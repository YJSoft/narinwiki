<?

class NarinAction extends NarinActionPlugin {

	var $id;
	var $delete_all_docs;
	var $delete_article; // 지워질 문서
	
	public function __construct() {
		$this->id = "wiki_default_action";		
		parent::__construct();
	}	  	

	/**
	 * 액션 등록
	 */	
	public function register($ctrl)
	{
		$ctrl->addHandler("WRITE_COMMENT_UPDATE", $this, "on_comment_update");
		$ctrl->addHandler("WRITE", $this, "on_write");
		$ctrl->addHandler("VIEW_COMMENT", $this, "on_view_comment");
		$ctrl->addHandler("VIEW", $this, "on_view");
		$ctrl->addHandler("WRITE_UPDATE", $this, "on_write_update");
		$ctrl->addHandler("DELETE_HEAD", $this, "on_delete_head");
		$ctrl->addHandler("DELETE_TAIL", $this, "on_delete_tail");
		$ctrl->addHandler("DELETE_ALL_HEAD", $this, "on_delete_all_head");
		$ctrl->addHandler("DELETE_ALL_TAIL", $this, "on_delete_all_tail");
		$ctrl->addHandler("MOVE_DOC", $this, "on_doc_move");		
	}

	/**
	 * 문서명 변경 (이동)
	 */
	public function on_doc_move($params) {		
		// 최근 변경 내역 업데이트
		$wikiChanges = wiki_class_load("Changes");
		$toDoc = $params[to];
		$doc = $params[from];
		$wikiChanges->update($doc, "이름 변경 (이전)", $this->member[mb_id]);		
		$wikiChanges->update($toDoc, "이름 변경 (이후)", $this->member[mb_id]);		
	}
		
	/**
	 * 댓글 작성/업데이트 시 
	 * 위키문서 링크 수정 (/ 로 시작하지 않는 문서에 대해서)
	 */
	public function on_comment_update($params) {
		$s_content = $params[wr_content];
		$comment_id = $params[comment_id];
		
		$util = wiki_class_load("Util");
		$content = $util->wiki_fix_internal_link($s_content);
		
		if($content != $s_content) {
			$content = mysql_real_escape_string($content);
			sql_query("UPDATE {$this->wiki[write_table]} SET wr_content = '$content' WHERE wr_id = $comment_id");
		}		
	}
	
	/**
	 * 글쓰기 폼 보여주기 전 처리
	 */
	public function on_write($params) {
		$member = $this->member;
		$w = $params[w];
		$ns = $params[folder];
		$doc = $params[doc];
		$docname = $params[docname];
		$wr_id = $params[wr_id];
		$write = &$params[write];
		$title_msg = $params[title_msg];
		$is_wiki_admin = $this->is_wiki_admin;
		
		$ret = array();
		list($subject, $wr_doc) = wiki_doc_from_write($doc, $wr_id);

		if(!$write[is_owner] && !$is_wiki_admin) $ret['is_file'] = false;
		
		$title_msg = "문서 편집";
		if(!$w) {
			$title_msg = "새 문서";
			$wikiNS = wiki_class_load("Namespace");
			$folder = $wikiNS->get($ns);
			$tpl = $folder[tpl];
			$source = array("/@DOCNAME@/", "/@FOLDER@/", "/@USER@/", "/@NAME@/", "/@NICK@/", "/@MAIL@/", "/@DATE@/");
			$target = array($docname, $ns, $member[mb_id], $member[mb_name], $member[mb_nick], $member[mb_email], date("Y-m-d h:i:s"));	
			$content = preg_replace($source, $target, $tpl);
			$ret[content] = $content;
		}
		
		$ret[title_msg] = $title_msg;
		$ret[subject] = wiki_input_value($subject);
		$ret[wr_doc] = wiki_input_value($wr_doc);
		
		return $ret;		
	}	
	
	/**
	 * 댓글에 대한 위키 문법 분석
	 */
	public function on_view_comment($params) {
		$wikiParser = wiki_class_load("Parser");
		$list = &$params['list'];
		if($params[use_comment]) {
		
			for ($i=0; $i<count($list); $i++) {
				$list[$i][del_link] = wiki_adjust_path($list[$i][del_link]);
				$list[$i][content] = $wikiParser->parse($list[$i]);
			}
		} else $list = "";
	}
	
	/**
	 * 문서 등록 후 처리 
	 */
	public function on_write_update($params) {
		$wr_doc = $params[wr_doc];
		$wr_id = $params[wr_id];
		$w = $params[w];
		$wr_name = $params[wr_name];
		$member = $this->member;
		$wr_content = $params[wr_content];
		$wr_history = $params[wr_history];
		$write = $params[write];
		$wiki = $this->wiki;
		$bo_table = $wiki[bo_table];
		
		if($wr_doc) {

			$wikiControl = wiki_class_load("Control");
			$wikiHistory = wiki_class_load("History");	
				
			// 위키 트리 구조에 글 등록	
			$wikiControl->write_update($w, $wr_id, $wr_doc);
			
			// 문서이력 업데이트
			$editor = ($member[mb_id] ? $member[mb_id] : $wr_name);
			$wikiHistory->update($wr_id, $wr_content, $editor, $wr_history);
			
			// 절대경로로 시작하지않는 내부문서 경로에 현재 폴더 경로 추가
			list($ns, $docname, $fullname) = wiki_page_name($wr_doc, $strip=false);
			$util = wiki_class_load("Util");
			$content = $util->wiki_fix_internal_link($wr_content);
			$write[wr_content] = $content;
		
			if($content != $wr_content) {
				$content = mysql_real_escape_string($content);
				sql_query("UPDATE {$this->wiki[write_table]} SET wr_content = '$content' WHERE wr_id = $wr_id");
			}
		
			// 캐쉬 업데이트 필드 셋팅
			$wikiArticle = wiki_class_load("Article");
			$wikiArticle->shouldUpdateCache($wr_id, 1);
			$backlinks = $wikiArticle->getBackLinks($fullname, $includeSelf = true);
			for($i=0; $i<count($backlinks); $i++) {
				$wikiArticle->shouldUpdateCache($backlinks[$i][wr_id], 1);
			}
			
			// 최근 변경 내역 업데이트
			$wikiChanges = wiki_class_load("Changes");
			$status = "새문서";
			if($params[w] == 'u') $status = "편집";
			$wikiChanges->update($fullname, $status, $this->member[mb_id]);	
					
		}

		@mkdir($wiki[path]."/data/$bo_table");
		@chmod($wiki[path]."/data/$bo_table", 0707);
		@mkdir($wiki[path]."/data/$bo_table/css");
		@chmod($wiki[path]."/data/$bo_table/css", 0707);
		@mkdir($wiki[path]."/data/$bo_table/files");
		@chmod($wiki[path]."/data/$bo_table/files", 0707);
		@mkdir($wiki[path]."/data/$bo_table/js");
		@chmod($wiki[path]."/data/$bo_table/js", 0707);
		@mkdir($wiki[path]."/data/$bo_table/thumb");
		@chmod($wiki[path]."/data/$bo_table/thumb", 0707);
	}	
	
	/**
	 * 문서 삭제 후 처리 (HEAD)
	 * 문서가 삭제되기 전에 호출됨 (권한 검사 등으로 삭제 안될 수 도 있음)
	 */
	public function on_delete_head($params) {
		$wr_id = $params[wr_id];
		$wikiArticle = wiki_class_load("Article");
		$article = $wikiArticle->getArticleById($wr_id);
		$this->delete_article = $article;		
	}	
	
	/**
	 * 문서 삭제 후 처리 (TAIL)
	 * 문서가 삭제된 후 호출됨
	 */
	public function on_delete_tail($params) {
		
		$wr_id = $params[wr_id];

		$wikiArticle = wiki_class_load("Article");
		$article = $this->delete_article;

		$doc = wiki_doc($article[ns], $article[doc]);

		$backlinks = $wikiArticle->getBackLinks($doc, $includeSelf = false);
		for($i=0; $i<count($backlinks); $i++) {
			$wikiArticle->shouldUpdateCache($backlinks[$i][wr_id], 1);
		}
		
		$wikiArticle->deleteArticleById($wr_id);
		
		// 문서 이력 삭제
		$wikiHistory = wiki_class_load("History");
		$wikiHistory->clear($wr_id, $delete_all = true);

		// 캐시 삭제				
		$wikiCache = wiki_class_load("Cache");
		$wikiCache->delete($wr_id);
		
		// 최근 변경 내역 업데이트
		$wikiChanges = wiki_class_load("Changes");
		$wikiChanges->update($doc, "삭제", $this->member[mb_id]);
	}

	/**
	 * 여러 문서 삭제 후 처리 (HEAD)
	 */	
	public function on_delete_all_head($params) {
		
		$this->delete_all_docs = array();
		
		$wr_id = $params[wr_id];
		$chk_wr_id = $params[chk_wr_id];
		
		$tmp_array = array();
		if ($wr_id) // 건별삭제
		    $tmp_array[0] = $wr_id;
		else // 일괄삭제
		    $tmp_array = $chk_wr_id;		
		
		$wikiArticle = wiki_class_load("Article");
		for($i=0; $i<count($tmp_array); $i++) {
			$wr = $wikiArticle->getArticleById($tmp_array[$i]);
			if($wr) {
				$this->delete_all_docs[$wr[wr_id]] = wiki_doc($wr[ns], $wr[doc]);
			}
		}
	}
	
	/**
	 * 여러 문서 삭제 후 처리
	 */	
	public function on_delete_all_tail($params) {
		
		$tmp_array = $params[wr_id_array];
		$folder = $params[folder];
				
		$wikiArticle = wiki_class_load("Article");
		$wikiArticle->removeAllNotExistsDoc();

		
		$wikiNS = wiki_class_load("Namespace");
		$wikiNS->removeAllEmptyNS();
		
		// $tmp_array 에 지워야할 wr_id 있음
		// 하지만 권한이나 기타 문제로 지워지지 않는것도 있기 때문에
		// 일일이 체크해가며 cache 삭제
		$wikiCache = wiki_class_load("Cache");
		$wikiHistory = wiki_class_load("History");	

		// 최근 변경 내역 업데이트
		$wikiChanges = wiki_class_load("Changes");
			
		for ($i=count($tmp_array)-1; $i>=0; $i--) 
		{
			$write = sql_fetch(" select wr_id from {$this->wiki[write_table]} where wr_id = '{$tmp_array[$i]}' ");
			if(!$write) {
				$wikiCache->delete($tmp_array[$i]);
				$wikiHistory->clear($tmp_array[$i], $delete_all = true);
				$d_doc = $this->delete_all_docs[$tmp_array[$i]];
				$backlinks = $wikiArticle->getBackLinks($d_doc, $includeSelf = false);
				$wikiChanges->update($d_doc, "삭제", $this->member[mb_id]);				
				for($k=0; $k<count($backlinks); $k++) {
					$wikiArticle->shouldUpdateCache($backlinks[$k][wr_id], 1);
				}
			}			
		}		
	
		if($folder) {
			$bo_table = $this->wiki[bo_table];
			$ns = $wikiNS->get($folder);
			if(!$ns) goto_url("{$this->wiki[path]}/narin.php?bo_table=$bo_table");
			else goto_url("{$this->wiki[path]}/folder.php?bo_table=$bo_table&loc=".urlencode($folder));
			exit;
		}		
		
	}

	/**
	 * 문서 보기 전처리
	 */
	public function on_view($params) {
		
		// 변수 읽어오기
		$g4 = $this->g4;
		$wiki = $this->wiki;
		$view = &$params[view];
		$wikiConfig = $this->wiki_config;
		$ns = $params[folder];
		$wr_id = $params[wr_id];		
		$hid = $params[hid];
		$docname = $params[docname];
		$doc = $params[doc];
		$member = $this->member;
		$is_wiki_admin = $this->is_wiki_admin;
		$update_href = $params[update_href];
		$delete_href = $params[delete_href];
		$comment_delete_href = $params[comment_delete_href];
		$write_href = $params[write_href];
		$scrap_href = $params[scrap_href];
		$nogood_href = $params[nogood_href];
		$good_href = $params[good_href];		
		$write = &$this->write;

		// 반환 배열
		$ret = array();
		
		// 위키 오브젝트 로드
		$wikiParser = wiki_class_load("Parser");	
		$wikiArticle = wiki_class_load("Article");
		$wikiUtil = wiki_class_load("Util");
		
		// <nowiki>, <pre> 를 제외한 컨텐츠
		$no_nowiki_content = $wikiUtil->no_nowiki_content($view[wr_content]);
		
		// 편집 권한
		$default_edit_level = $wikiConfig->setting[edit_level];
		$article = $wikiArticle->getArticle($ns, $docname, __FILE__, __LINE__);
		
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
				$ret[recov_link] = $recov_link;
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
		
		$ret[use_comment] = $use_comment;
		$ret[is_comment_write] = $is_comment_write;
		$ret[is_category] = false;
		$ret[category_name] = false;

		
		// 링크 경로 수정
		$ret[update_href] = wiki_adjust_path($update_href);
		$ret[delete_href] = wiki_adjust_path($delete_href);
		$ret[comment_delete_href] = wiki_adjust_path($comment_delete_href);
		$ret[write_href] = wiki_adjust_path($write_href);
		$ret[scrap_href] = wiki_adjust_path($scrap_href);
		$ret[nogood_href] = wiki_adjust_path($nogood_href);
		$ret[good_href] = wiki_adjust_path($good_href);

		
		// DOC for URL
		$encoded_doc = urlencode($doc);
		$ret[encoded_doc] = $encoded_doc;
		
		// 문서 에디터?
		$is_doc_editor = ( $member[mb_id] && $member[mb_level] >= $edit_level );
		
		// 문서 에디터면 수정 버튼 보이도록
		if($is_doc_editor) {
			$update_href = $g4[bbs_path]."/write.php?w=u&bo_table=".$wiki[bo_table]."&wr_id=".$wr_id;
			$ret[update_href] = $update_href;
		}
		
		// 관리 패스
		$admin_href = $g4[admin_path]."/board_form.php?w=u&bo_table=".$wiki[bo_table];
		$ret[admin_href] = $admin_href;
		
		// 문서 관리 패스
		if($is_doc_owner || $is_wiki_admin) {
			$doc_admin_href = "javascript:wiki_admin(this);";
			$ret[doc_admin_href] = $doc_admin_href;
		}
		
		// 위키 관리 패스
		if($is_wiki_admin) {
			$wiki_admin_href = $wiki[path]."/adm/index.php?bo_table={$wiki[bo_table]}";
			$ret[wiki_admin_href] = $wiki_admin_href;
		}
		
		// 최근 변경내역 보기 링크
		$ret[recent_href] = $wiki[path]."/recent.php?bo_table=".$wiki[bo_table];
		
		// 백링크
		$wikiArticle = wiki_class_load("Article");
		$back_links = $wikiArticle->getBackLinks($doc);
		$ret[back_links] = $back_links;
		
		// 문서이력 URL
		if( $is_doc_owner || $is_wiki_admin || $member[mb_level] >= $history_access_level)
		{
			$history_href = $wiki[path]."/history.php?bo_table={$wiki[bo_table]}&doc=$encoded_doc";
			$ret[history_href] = $history_href;
		} else $ret[history_href] = "";
		
		// 네비게이션
		$navigation = wiki_navigation($doc);
		$ret[navigation] = $navigation;
		
		// 문서이력 보기라면 버튼 감춤
		if($is_history) {
			$ret[update_href] = "";
			$ret[delete_href] = "";
			$ret[scrap_href] = "";
			$ret[trackback_url] = "";
			$ret[nogood_href] = "";
			$ret[good_href] = "";
			$ret[comment_delete_href] = "";
			$ret[doc_admin_href] = "";
			$ret[use_comment] = "";
		}

		return $ret;		
	}
	
}

?>