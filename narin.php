<?
/**
 *
 * 나린위키 메인 페이지
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("./_common.php");

$use_minify = true;
if($use_minify) ob_start();

// 위키 오브젝트 로드
$wikiArticle =& wiki_class_load("Article");
$wikiControl =& wiki_class_load("Control");
$wikiUtil =& wiki_class_load("Util");

// 문서 로드
$write = &$wikiArticle->getArticle($ns, $docname);

// 권한 검사
$wikiControl->acl($doc);

// 문서가 없을 경우
if(!$write) {	
	$wikiControl->noDocument($ns, $docname, $doc);
	exit;
}

// 문서 id
$wr_id = $write['wr_id'];

// 한번 읽은글은 브라우저를 닫기전까지는 카운트를 증가시키지 않음
$ss_name = "ss_view_{$bo_table}_{$wr_id}";
if (!get_session($ss_name))
{
	sql_query(" update $write_table set wr_hit = wr_hit + 1 where wr_id = '$wr_id' ");
	set_session($ss_name, TRUE);
}

// 브라우저 타이틀 설정
$g4['title'] = $docname . ' - ' . $ns;

// IP보이기 사용 여부
$ip = "";
$is_ip_view = $board['bo_use_ip_view'];
if ($is_admin) {
	$is_ip_view = true;
	$ip = $write['wr_ip'];
} else // 관리자가 아니라면 IP 주소를 감춘후 보여줍니다.
$ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $write['wr_ip']);

// 수정, 삭제 링크
$update_href = $delete_href = "";

// 로그인중이고 자신의 글이라면 또는 관리자라면 패스워드를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] == $write['mb_id'])) || $is_admin) {
	$wiki['g4_url']."/bbs/write.php?w=u&bo_table=$bo_table&wr_id=$wr_id&page=$page" . $qstr;
	$delete_href = "javascript:del('".$wiki['g4_url']."/bbs/delete.php?bo_table=$bo_table&wr_id=$wr_id&page=$page".urldecode($qstr)."');";
	if ($is_admin)
	{
		set_session("ss_delete_token", $token = uniqid(time()));
		$delete_href = "javascript:del('".$wiki['g4_url']."/bbs/delete.php?bo_table=$bo_table&wr_id=$wr_id&token=$token&page=$page".urldecode($qstr)."');";
	}
}
else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
	$update_href = $wiki['g4_url']."/bbs/password.php?w=u&bo_table=$bo_table&wr_id=$wr_id&page=$page" . $qstr;
	$delete_href = $wiki['g4_url']."/bbs/password.php?w=d&bo_table=$bo_table&wr_id=$wr_id&page=$page" . $qstr;
}

// 뷰 가져오기
$view = get_view($write, $board, $board_skin_path, 255);

// <nowiki>, <pre> 를 제외한 컨텐츠
$no_nowiki_content = $wikiUtil->no_nowiki_content($view['wr_content']);

// 편집 권한
$default_edit_level = $wikiConfig->setting['edit_level'];

// 최근 업데이트된 시간
$view['update_date'] = $write['update_date'];

// 문서 작성자?
$is_doc_owner = ( $view['mb_id'] && $view['mb_id'] == $member['mb_id'] );


// 편집 / 문서이력 접근 레벨
$edit_level = ( $write['edit_level'] ? $write['edit_level'] : $default_edit_level);
$history_access_level = $wikiConfig->setting['history_access_level'];

// 문서이력의 '보기'
$is_history = false;
if($hid) {
	$wikiHistory =& wiki_class_load("History");
	$history = $wikiHistory->get($hid, $view['wr_id']);
	if($history) {
		if($member['mb_level'] >= $history_access_level) {
			$view['wr_content'] = $history['content'];
			$is_history = true;
		}
	}
}

// 문서이력보기 또는 ~~NOCACHE~~ 사용시 캐시 사용 안함
if(!$hid && !preg_match("/~~NOCACHE~~/", $no_nowiki_content)) {
	$wikiCache =& wiki_class_load("Cache");

	// 캐시 업데이트 하지 않아도 되는 경우
	if(!$write['should_update_cache']) {

		// 캐시 읽어옴
		$cached_content = $wikiCache->get($write['wr_id']);

		// 저장된 캐시가 없다면 parsing 후 cacheing
		if(!$cached_content) {
			$wikiParser =& wiki_class_load("Parser");
			$cached_content = $wikiParser->parse($view);
			$wikiCache->update($wr_id, $view['content']);
		} else {

			// partial nocache (부분 캐시 사용 안함)
			preg_match_all('/<nocache plugin="(.*?)" method="(.*?)" params="(.*?)">(.*?)<\/nocache>/is', $cached_content, $m);
			
			// 부분 캐시 업데이트가 필요하면
			if($m) {
				
				// 플러그인 상위 클래스 include
				include_once WIKI_PATH."/lib/narin.SyntaxPlugin.class.php";
				
				for($i=0; $i<count($m[0]); $i++) {
					
					// 파라미터 분석					
					$plugin = $m[1][$i];	// plugin
					$method = $m[2][$i];	// method
					$params = json_decode(stripcslashes($m[3][$i]), $assoc=true);	// params			
					
					// 문법 클래스 경로		
					$syntaxFile = WIKI_PATH. '/plugins/'.$plugin.'/syntax.php';
					
					if(file_exists($syntaxFile)) {
						
						// 클래스명
						$realClassName = "NarinSyntax".ucfirst($plugin);
						
						// 문법 클래스 include
						include_once $syntaxFile;
						
						if(class_exists($realClassName)) {
							
							// 문법 플러그인 객체 생성
							$p = new $realClassName();								
						
							// 부분 캐시 업데이트 메소드 확인
							if(is_callable(array($p, $method))) {
								
								// 부분 캐시 업데이트 메소드 호출
								$html = $p->$method($params);
								
								// 캐시 업데이트
								$cached_content = str_replace($m[0][$i], 
										'<nocache plugin="'.$plugin.'" method="'.$method.'" params="'.addslashes(json_encode($params)).'">'.$html.'</nocache>', 
										$cached_content);
										
							}	// if callable
						}	// if class exists
					} // if file_exists
				} // for
				
				// DB의 캐시 업데이트 --> 캐시에 최신 내용 유지
				$wikiCache->update($wr_id, $cached_content);

			} // if m
		} // else

		$view['content'] = $cached_content;


	} else {	// 캐시를 업데이트 해야 하는 경우
		$wikiParser =& wiki_class_load("Parser");
		$view['content'] = $wikiParser->parse($view);
		$wikiCache->update($wr_id, $view['content']);
	}

} else {	// 캐시 사용 안하는 경우

	$wikiParser =& wiki_class_load("Parser");
	$view['content'] = $wikiParser->parse($view);

	if($write['should_update_cache']) {
		// 캐시를 사용하지 않더라도 일단 캐시
		// 외부에서 parsing 사용하지 않더라도 쓸 수 있게...
		$wikiCache =& wiki_class_load("Cache");
		$wikiCache->update($wr_id, $view['content']);
	}

}

// 이력 보기라면.. 문서 위에 정보 출력
if($is_history) {
	$encoded_doc = urlencode($doc);
	if($is_wiki_admin || $is_doc_owner) {
		$recov_link = "<span class=\"button green\"><a href=\"javascript:recover_history({$view['wr_id']}, {$hid});\">이 문서로 복원</a></span>";
		$recov_link = $recov_link;
	}
	
	$show_cur_doc = wiki_url('read', array('doc'=>$doc));
	$history_info =<<<END
		
		<div class="list_table">
		<table cellpadding="0" cellspacing="0">
		<colgroup width="90px"/>
		<colgroup width=""/>
		<tbody>
			<tr>
				<th>편집</th>
				<td>{$history['editor_mb_id']} ({$history['reg_date']})</td>
			</tr>
			<tr>
				<th>문서요약</th>
				<td>{$history['summary']}</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
				$recov_link
					<span class="button red"><a href="{$show_cur_doc}">현재 문서 보기</a></span>
				</td>
			</tr>
		</tbody>
		</table>
		</div>
		
END;
	$view['content'] = $history_info . $view['content'];
}

// 코맨트 사용 처리
$use_comment = false;
if(!preg_match("/~~COMMENT~~/", $no_nowiki_content)) {
	$is_comment_write = false;
} else $use_comment = true;


// norobot 일 경우, 매타태그 추가
if(preg_match("/~~NOROBOT~~/", $no_nowiki_content)) {
	$wiki_head .= '<meta name="robots" content="noindex, nofollow">'."\n";
}



// DOC for URL
$encoded_doc = urlencode($doc);

// 문서 에디터?
$is_doc_editor = ( $member['mb_id'] && ( $member['mb_level'] >= $edit_level || $is_doc_owner));

// 문서 에디터면 수정 버튼 보이도록
if($is_doc_editor) {
	$update_href = $wiki['g4_url']."/bbs/write.php?w=u&bo_table=".$bo_table."&wr_id=".$wr_id;
}

// 문서 관리 패스
if($is_doc_owner || $is_wiki_admin) {	
	$doc_admin_href = "javascript:wiki_admin(this);";		
}

// 위키 관리 패스
$admin_href = "";
$wiki_admin_href = "";
if($is_wiki_admin) {
	$wiki_admin_href = $wiki['url']."/adm/";
	// 관리 패스
	$admin_href = $wiki['g4_url']."/adm/board_form.php?w=u&bo_table=".$bo_table;	
}

// 최근 변경내역 보기 링크
$recent_href = wiki_url('recent');

// 백링크
$back_links = $wikiArticle->getBackLinks($doc);


// 문서이력 URL
if( $is_doc_owner || $is_wiki_admin || $member['mb_level'] >= $history_access_level)
{
	$history_href = wiki_url('history', array('doc'=>$doc));
} else $history_href = "";

// 네비게이션
$navigation = wiki_navigation($doc);

// 공헌자
$contributors = $write['contributors'];


// 문서이력 보기라면 버튼 감춤
if($is_history) {
	$update_href = "";
	$delete_href = "";
	$comment_delete_href = "";
	$doc_admin_href = "";
	$use_comment = "";
}

// 머리문서 include
include_once WIKI_PATH . "/head.php";

// 스킨 include
include_once("$board_skin_path/view.skin.php");

// 꼬리 include
include_once WIKI_PATH . "/tail.php";


if($use_minify) {
	$content = ob_get_contents();
	ob_end_clean();

	include_once WIKI_PATH."/lib/Minifier/htmlmin.php";
	include_once WIKI_PATH."/lib/Minifier/jsmin.php";
	include_once WIKI_PATH."/lib/Minifier/cssmin.php";
	echo Minify_HTML::minify($content, $options=array("jsMinifier"=>"JSMin::minify", "cssMinifier"=>"CssMin::minify"));
}
?>
