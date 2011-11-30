<?
/**
 * 
 * 액션 스크립트 : 댓글 삭제 (삭제된 후)
 *
 * @package	narinwiki
 * @subpackage event
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined('_GNUBOARD_')) exit;

if($article_of_delete_comment) {
	$doc = wiki_doc($article_of_delete_comment['ns'], $article_of_delete_comment['doc']);
	// 최근 변경 내역 업데이트
	$wikiChanges = wiki_class_load("Changes");
	$wikiChanges->update("DOC", $doc, "댓글삭제", ($member['mb_id'] ? $member['mb_id'] : $write['wr_name']));		
}

?>