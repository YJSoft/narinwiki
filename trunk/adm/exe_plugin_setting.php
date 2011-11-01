<?
/**
 * 위키 관리 : plugin 설정 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("_common.php");


$info = wiki_plugin_info($plugin);
if(!$info) {
	alert("플러그인 로드 오류");
}

@mkdir($wiki[path]."/data/$bo_table");
@chmod($wiki[path]."/data/$bo_table", 0707);
@mkdir($wiki[path]."/data/$bo_table/css");
@chmod($wiki[path]."/data/$bo_table/css", 0707);
@mkdir($wiki[path]."/data/$bo_table/files");
@chmod($wiki[path]."/data/$bo_table/files", 0707);
@mkdir($wiki[path]."/data/$bo_table/js");
@chmod($wiki[path]."/data/$bo_table/js", 0707);

$setting = $info->getSetting();
if(!$info->checkSetting($setting)) alert("플러그인 설정 오류");

$wikiConfig = wiki_class_load("Config");
$wikiConfig->update("/plugin_setting/".$info->getId(), $_POST[setting]);
$info->afterSetSetting($_POST[setting]);

wiki_set_option("js_modified", "timestamp", time());
wiki_set_option("css_modified", "timestamp", time());

header("location:{$wiki[path]}/adm/plugin.php?bo_table={$bo_table}");

?>


