<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 댓글 삭제 시  
 */
if($article_of_delete_comment) {
	$doc = wiki_doc($article_of_delete_comment[ns], $article_of_delete_comment[doc]);
	// 최근 변경 내역 업데이트
	$wikiChanges = wiki_class_load("Changes");
	$wikiChanges->update($doc, "댓글삭제", ($member[mb_id] ? $member[mb_id] : $write[wr_name]));		
}

?>