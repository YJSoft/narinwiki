<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 문서 보기 전처리
 */

$view = &$params[view];

// 위키 오브젝트 로드
$wikiParser = wiki_class_load("Parser");	
$wikiArticle = wiki_class_load("Article");
$wikiUtil = wiki_class_load("Util");

// <nowiki>, <pre> 를 제외한 컨텐츠
$no_nowiki_content = $wikiUtil->no_nowiki_content($view[wr_content]);

// 편집 권한
$default_edit_level = $wikiConfig->setting[edit_level];
$article = $wikiArticle->getArticle($folder, $docname, __FILE__, __LINE__);

// 문서 작성자?
$is_doc_owner = ( $view[mb_id] && $view[mb_id] == $member[mb_id] );


// 편집 / 문서이력 접근 레벨
$edit_level = ( $article[edit_level] ? $article[edit_level] : $default_edit_level);
$history_access_level = $wikiConfig->setting[history_access_level];

// 문서이력의 '보기'
$is_history = false;
if($hid) {
	$wikiHistory = wiki_class_load("History");	
	$history = $wikiHistory->get($hid, $view[wr_id]);	
	if($history) {				
		if($member[mb_level] >= $history_access_level) {
			$view[wr_content] = $history[content];
			$is_history = true;
		}
	}	
}

// 문서이력보기 또는 ~~NOCACHE~~ 사용시 캐시 사용 안함
if(!$hid && !preg_match("/~~NOCACHE~~/", $no_nowiki_content)) {
	$wikiCache = wiki_class_load("Cache");		
	
	// 캐시 업데이트 하지 않아도 되는 경우
	if(!$article[should_update_cache]) {
		
		// 캐시 읽어옴		
		$cached_content = $wikiCache->get($article[wr_id]);
		
		// 저장된 캐시가 없다면 parsing 후 cacheing
		if(!$cached_content) {			
			$view[content] = $wikiParser->parse($view);
			$wikiCache->update($wr_id, $view[content]);		
		} else {	// 저장된 캐시가 있으면 사용

			$view[content] = $cached_content;
		}
	
	} else {	// 캐시를 업데이트 해야 하는 경우
		$view[content] = $wikiParser->parse($view);
		$wikiCache->update($wr_id, $view[content]);			
	}
	
} else {	// 캐시 사용 안하는 경우
	$view[content] = $wikiParser->parse($view);
}

if($is_history) {
	$encoded_doc = urlencode($doc);
	if($is_wiki_admin || $is_doc_owner) {
		$recov_link = "<span class=\"button green\"><a href=\"javascript:recover_history({$view[wr_id]}, {$hid});\">이 문서로 복원</a></span>";
		$return_array[recov_link] = $recov_link;
	}

	$history_info =<<<END
		
		<div class="list_table">
		<table cellpadding="0" cellspacing="0">
		<colgroup width="90px"/>
		<colgroup width=""/>
		<tbody>
			<tr>
				<th>편집</th>
				<td>$history[editor_mb_id] ($history[reg_date])</td>
			</tr>
			<tr>
				<th>문서요약</th>
				<td>$history[summary]</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					$recov_link
					<span class="button red"><a href="{$wiki[path]}/narin.php?bo_table={$wiki[bo_table]}&doc={$encoded_doc}">현재 문서 보기</a></span>
				</td>
			</tr>
		</tbody>
		</table>
		</div>
		
END;
	$view[content] = $history_info . $view[content];
}

$use_comment = false;

if(!preg_match("/~~COMMENT~~/", $no_nowiki_content)) {
	$is_comment_write = false;
} else $use_comment = true;

$return_array[use_comment] = $use_comment;
$return_array[is_comment_write] = $is_comment_write;
$return_array[is_category] = false;
$return_array[category_name] = false;


// 링크 경로 수정
$return_array[update_href] = wiki_adjust_path($update_href);
$return_array[delete_href] = wiki_adjust_path($delete_href);
$return_array[comment_delete_href] = wiki_adjust_path($comment_delete_href);
$return_array[write_href] = wiki_adjust_path($write_href);
$return_array[scrap_href] = wiki_adjust_path($scrap_href);
$return_array[nogood_href] = wiki_adjust_path($nogood_href);
$return_array[good_href] = wiki_adjust_path($good_href);


// DOC for URL
$encoded_doc = urlencode($doc);
$return_array[encoded_doc] = $encoded_doc;

// 문서 에디터?
$is_doc_editor = ( $member[mb_id] && $member[mb_level] >= $edit_level );

// 문서 에디터면 수정 버튼 보이도록
if($is_doc_editor) {
	$update_href = $g4[bbs_path]."/write.php?w=u&bo_table=".$wiki[bo_table]."&wr_id=".$wr_id;
	$return_array[update_href] = $update_href;
}

// 관리 패스
$admin_href = $g4[admin_path]."/board_form.php?w=u&bo_table=".$wiki[bo_table];
$return_array[admin_href] = $admin_href;

// 문서 관리 패스
if($is_doc_owner || $is_wiki_admin) {
	$doc_admin_href = "javascript:wiki_admin(this);";
	$return_array[doc_admin_href] = $doc_admin_href;
}

// 위키 관리 패스
if($is_wiki_admin) {
	$wiki_admin_href = $wiki[path]."/adm/index.php?bo_table={$wiki[bo_table]}";
	$return_array[wiki_admin_href] = $wiki_admin_href;
}

// 최근 변경내역 보기 링크
$return_array[recent_href] = $wiki[path]."/recent.php?bo_table=".$wiki[bo_table];

// 백링크
$wikiArticle = wiki_class_load("Article");
$back_links = $wikiArticle->getBackLinks($doc);
$return_array[back_links] = $back_links;

// 문서이력 URL
if( $is_doc_owner || $is_wiki_admin || $member[mb_level] >= $history_access_level)
{
	$history_href = $wiki[path]."/history.php?bo_table={$wiki[bo_table]}&doc=$encoded_doc";
	$return_array[history_href] = $history_href;
} else $return_array[history_href] = "";

// 네비게이션
$navigation = wiki_navigation($doc);
$return_array[navigation] = $navigation;

// 문서이력 보기라면 버튼 감춤
if($is_history) {
	$return_array[update_href] = "";
	$return_array[delete_href] = "";
	$return_array[scrap_href] = "";
	$return_array[trackback_url] = "";
	$return_array[nogood_href] = "";
	$return_array[good_href] = "";
	$return_array[comment_delete_href] = "";
	$return_array[doc_admin_href] = "";
	$return_array[use_comment] = "";
}

?>