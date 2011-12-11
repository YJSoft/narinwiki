<?
/**
 * 
 * 위키 관리 : media 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("_common.php");


$narin_config =& wiki_class_load("Config");
$_POST['media_setting']['allow_extensions'] = str_replace(" ", "", $_POST['media_setting']['allow_extensions']);
$narin_config->update("/media_setting", $_POST['media_setting']);

header("location:".$wiki['path']."/adm/media.php?bo_table=$bo_table");
?>


