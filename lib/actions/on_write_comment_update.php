<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 댓글 작성/업데이트 시 
 * 위키문서 링크 수정 (/ 로 시작하지 않는 문서에 대해서)
 */
$util = wiki_class_load("Util");
$content = $util->wiki_fix_internal_link($wr_content);

if($content != $wr_content) {
	$content = mysql_real_escape_string($content);
	sql_query("UPDATE {$this->wiki[write_table]} SET wr_content = '$content' WHERE wr_id = $comment_id");
}		

	
// 최근 변경 내역 업데이트
if($w == 'c' || $w == 'cu') {
	list($ns, $docname, $doc) = wiki_page_name($wr_doc, $strip=false);
	$wikiChanges = wiki_class_load("Changes");
	if($w == 'c') $status = "댓글 작성";
	else $status = "댓글 편집";
	$wikiChanges->update($doc, $status, ($member[mb_id] ? $member[mb_id] : $wr_name));		
}
?>