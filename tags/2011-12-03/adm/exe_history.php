<?
/**
 * 
 * 위키 관리 : manage 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
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

// 변경내역 정리 by day
if($md == 'ecbd' && $expire) {
	$wikiChanges = wiki_class_load("Changes");
	$wikiChanges->clearChangesByDate($expire);
}


header("location:".$wiki['path']."/adm/history.php?bo_table=$bo_table");
?>


