<?
/**
 *
 * 위키 관리 : plugin 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("_common.php");

$wikiConfig =& wiki_class_load("Config");

$current_using_plugins = $wikiConfig->using_plugins;

$use_plugins = array();
for($i=0; $i<count($wiki_plugin); $i++)
{
	if($wiki_plugin_use[$i]) array_push($use_plugins, $wiki_plugin[$i]);
}

$wikiConfig->update("/using_plugins", $use_plugins);

$unused_plugins = array_diff($current_using_plugins, $use_plugins);
foreach($unused_plugins as $k=>$p) {
	$pi = wiki_plugin_info($p);
	$pi->onUnused();	
}

$wikiJsCss = wiki_class_load('JsCss');
$wikiJsCss->updateJs();
$wikiJsCss->updateCss();

header("location:".$wiki['url']."/adm/plugin.php");


?>


