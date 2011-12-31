<?
/**
 * 
 * 위키 관리 : media 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("_common.php");


$narin_config =& wiki_class_load("Config");
$_POST['media_setting']['allow_extensions'] = str_replace(" ", "", $_POST['media_setting']['allow_extensions']);
$narin_config->update("/media_setting", $_POST['media_setting']);

header("location:".$wiki['url']."/adm/media.php");
?>


