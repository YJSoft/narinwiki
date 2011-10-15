<?
include_once("_common.php");

$use_plugins = array();
for($i=0; $i<count($wiki_plugin); $i++)
{
	if($wiki_plugin_use[$i]) array_push($use_plugins, $wiki_plugin[$i]);
}

$wikiConfig = wiki_class_load("Config");
$wikiConfig->update("/using_plugins", $use_plugins);

header("location:{$wiki[path]}/adm/plugin.php?bo_table={$bo_table}");
?>


