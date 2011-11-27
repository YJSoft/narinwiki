<?
/**
 * 위키 관리 : media 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");


$narin_config = wiki_class_load("Config");
$_POST['media_setting']['allow_extensions'] = str_replace(" ", "", $_POST['media_setting']['allow_extensions']);
$narin_config->update("/media_setting", $_POST['media_setting']);

header("location:{$wiki[path]}/adm/media.php?bo_table={$bo_table}");
?>


