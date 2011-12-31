<?
/**
 * 
 * 위키 관리 : plugin 설정 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("_common.php");


$info = wiki_plugin_info($plugin);
if(!$info) {
	alert("플러그인 로드 오류");
}

@mkdir(WIKI_PATH."/data/$bo_table");
@chmod(WIKI_PATH."/data/$bo_table", 0707);
@mkdir(WIKI_PATH."/data/$bo_table/css");
@chmod(WIKI_PATH."/data/$bo_table/css", 0707);
@mkdir(WIKI_PATH."/data/$bo_table/files");
@chmod(WIKI_PATH."/data/$bo_table/files", 0707);
@mkdir(WIKI_PATH."/data/$bo_table/js");
@chmod(WIKI_PATH."/data/$bo_table/js", 0707);

$setting = $info->getSetting();
if(!$info->checkSetting($setting)) alert("플러그인 설정 오류");

$wikiConfig =& wiki_class_load("Config");
$wikiConfig->update("/plugin_setting/".$info->getId(), $_POST['setting']);
$info->afterSetSetting($_POST['setting']);

$wikiJsCss = wiki_class_load('JsCss');
$wikiJsCss->updateJs();
$wikiJsCss->updateCss();
	
header("location:".$wiki['url']."/adm/plugin.php");

?>


