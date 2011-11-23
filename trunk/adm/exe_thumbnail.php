<?
/**
 * 위키 관리 : thumbnail 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");

$dir = $wiki['path'].'/data/'.$bo_table.'/thumb/';
if(file_exists($dir)) {
	foreach(glob($dir.'*.*') as $v){
		@unlink($v);
	}
}

$wikiCache = wiki_class_load("Cache");
$wikiCache->clear();
	
header("location:{$wiki[path]}/adm/thumbnail.php?bo_table={$bo_table}");
?>


