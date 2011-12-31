<?
/**
 * 
 * 문서 변경 내역 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once "./_common.php";

wiki_only_ajax();

if(!$is_wiki_admin) {
	echo "권한이 없습니다.";
	exit;
}

$wikiChanges =& wiki_class_load("Changes");

if($w == 'da') {
	$wikiChanges->clear();
	echo 1;
	exit;	
}


if($w == 'ds') {
	for($i=0; $i<count($cids); $i++) {
		$wikiChanges->delete($cids[$i]);		
	}
	echo 1;
	exit;
}
?>
