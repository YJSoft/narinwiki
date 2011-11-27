<?
/**
 * 위키 관리 : manage 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");

$wikiHistory = wiki_class_load("History");
if($md == 'del_unlinked' && $doc) {
	$wikiHistory->deleteUnlinked($doc);
}

if($md == 'clear_unlinked') {
	$wikiHistory->clearUnlinked();
}

// 문서이력 정리 by count
if($md == 'ehbc' && $expire) {
	
}

// 문서이력 정리 by day
if($md == 'ehbd' && $expire) {
	$wikiHistory->clearHistoryByDate($expire);
}

header("location:{$wiki[path]}/adm/history.php?bo_table={$bo_table}");
?>


