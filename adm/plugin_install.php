<?
include_once("_common.php");

if($w != 'install' && $w != 'uninstall') {
	alert("잘못된 접근");
}

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


$info->$w();

header("location:{$wiki[path]}/adm/plugin.php?bo_table={$bo_table}");

?>


