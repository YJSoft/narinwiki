<?
/**
 * 위키 관리 : basic 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");

if($wiki_front_apply_exist_doc) {
	$wikiArticle = wiki_class_load("Article");
	$front = $wikiArticle->getFrontPage();
	sql_query("UPDATE {$write_table} SET wr_subject = '$wiki_front' WHERE wr_id = $front[wr_id]");
}

sql_query("UPDATE {$g4[board_table]} SET bo_subject = '$wiki_front' WHERE bo_table = '$bo_table'");
$narin_config = wiki_class_load("Config");
$narin_config->update("/setting", $_POST[setting]);

header("location:{$wiki[path]}/adm/basic.php?bo_table={$bo_table}");
?>


