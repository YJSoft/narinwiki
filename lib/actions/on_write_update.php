<?
if (!defined('_GNUBOARD_')) exit;
				
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
		sql_query("UPDATE {$wiki[write_table]} SET wr_content = '$content' WHERE wr_id = $wr_id");
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
	
	if($w == 'u') $status = "편집";	
	$wikiChanges->update("DOC", $fullname, $status, ($member[mb_id] ? $member[mb_id] : $wr_name));				
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
?>

