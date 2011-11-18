<?
/**
 * 위키 관리 : manage 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");

if($md == 'cache_clear') {
	
	$wikiCache = wiki_class_load("Cache");
	$wikiCache->clear();
	echo "1";
	exit;
}

header("location:{$wiki[path]}/adm/index.php?bo_table={$bo_table}");
?>


