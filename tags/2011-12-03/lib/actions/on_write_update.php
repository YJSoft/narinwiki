<?
/**
 * 
 * 액션 스크립트 : 문서 작성/업데이트 후 처리
 *
 * @package	narinwiki
 * @subpackage event
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined('_GNUBOARD_')) exit;
				
if($wr_doc) {

	// 문서이력 업데이트
	$editor = ($member['mb_id'] ? $member['mb_id'] : $wr_name);
		
	// 위키 트리 구조에 글 등록	
	$wikiArticle = wiki_class_load("Article");
	
	if($w == '') { // 새글 작성 시
		$wikiArticle->addArticle($wr_doc, $wr_id);
	} else if($w == 'u') {	// 업데이트 시
		$wikiArticle->updateArticle($wr_doc, $wr_id);
	}			
	
	// 공헌자 추가
	$wikiArticle->addContributor($wr_id, $editor);
	
	$wikiHistory = wiki_class_load("History");		
	$wikiHistory->update($wr_id, $wr_content, $editor, $wr_history);
	
	// 새문서일 경우 이전 문서 이력과 연결 시켜 줌
	if(!$w) $wikiHistory->setLinked($wr_id, $wr_doc);
	
	// 절대경로로 시작하지않는 내부문서 경로에 현재 폴더 경로 추가
	list($ns, $docname, $fullname) = wiki_page_name($wr_doc);
	$util = wiki_class_load("Util");
	$content = $util->wiki_fix_internal_link($wr_content);
	$write['wr_content'] = $content;

	if($content != $wr_content) {
		$content = mysql_real_escape_string($content);
		sql_query("UPDATE ".$wiki['write_table']." SET wr_content = '$content' WHERE wr_id = $wr_id");
	}

	// 캐쉬 업데이트 필드 셋팅
	$wikiArticle->shouldUpdateCache($wr_id, 1);
	$backlinks = $wikiArticle->getBackLinks($fullname, $includeSelf = true);
	for($i=0; $i<count($backlinks); $i++) {
		$wikiArticle->shouldUpdateCache($backlinks[$i]['wr_id'], 1);
	}
	
	// 최근 변경 내역 업데이트
	$wikiChanges = wiki_class_load("Changes");
	$status = "새문서";
	
	if($w == "u") {
		$thumb = wiki_class_load("Thumb");				
		$thumb->deleteThumb($wiki['bo_table']."-".$wr_id . "-");
	}	
	
	if($w == 'u') $status = "편집";	
	$wikiChanges->update("DOC", $fullname, $status, ($member['mb_id'] ? $member['mb_id'] : $wr_name));				
}

// 임시저장 삭제
$id = md5($member['mb_id']."_".stripcslashes($wr_doc));
$reg = "tmpsave/$id";	
wiki_set_option($reg, null, null);

@mkdir($wiki['path']."/data/$bo_table");
@chmod($wiki['path']."/data/$bo_table", 0707);
@mkdir($wiki['path']."/data/$bo_table/css");
@chmod($wiki['path']."/data/$bo_table/css", 0707);
@mkdir($wiki['path']."/data/$bo_table/files");
@chmod($wiki['path']."/data/$bo_table/files", 0707);
@mkdir($wiki['path']."/data/$bo_table/js");
@chmod($wiki['path']."/data/$bo_table/js", 0707);
@mkdir($wiki['path']."/data/$bo_table/thumb");
@chmod($wiki['path']."/data/$bo_table/thumb", 0707);
?>

