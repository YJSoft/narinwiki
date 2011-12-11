<?
/**
 * 
 * 위키 관리 : plugin 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("_common.php");

$use_plugins = array();
for($i=0; $i<count($wiki_plugin); $i++)
{
	if($wiki_plugin_use[$i]) array_push($use_plugins, $wiki_plugin[$i]);
}

$wikiConfig =& wiki_class_load("Config");
$wikiConfig->update("/using_plugins", $use_plugins);

wiki_set_option("js_modified", "timestamp", time());
wiki_set_option("css_modified", "timestamp", time());

header("location:".$wiki['path']."/adm/plugin.php?bo_table=$bo_table");
?>


