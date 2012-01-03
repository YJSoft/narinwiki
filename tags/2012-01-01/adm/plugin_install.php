<?
/**
 * 
 * 위키 관리 : 플러그인 설치 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("_common.php");

if($w != 'install' && $w != 'uninstall') {
	alert("잘못된 접근");
}

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


$info->$w();

$wikiJsCss = wiki_class_load('JsCss');
$wikiJsCss->updateJs();
$wikiJsCss->updateCss();

header("location:".$wiki['url']."/adm/plugin.php?bo_table=$bo_table");

?>


