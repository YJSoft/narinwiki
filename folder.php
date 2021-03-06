<?
/**
 * 
 * folder 보기 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("./_common.php");

if(!$loc) $loc = "/";
$loc = stripcslashes($loc);
if($loc{0} != '/') $loc = '/'.$loc;

$wikiConfig =& wiki_class_load("Config");
$wikiNS =& wiki_class_load("Namespace");
$n = $wikiNS->get($loc);

if(!$n) {
	$wikiControl =& wiki_class_load("Control");
	$wikiControl->error("폴더 에러", "존재하지 않는 폴더입니다");
}

$folderViewLevel = $wikiConfig->setting['folder_view_level'];
if($member['mb_level'] < $folderViewLevel || $member['mb_level'] < $ns['ns_access_level']) {
	$wikiControl =& wiki_class_load("Control");
	$wikiControl->error("권한 없음", "폴더 보기 권한이 없습니다.");	
}

$folder['loc'] = $n['ns'];
$folder['navi'] = wiki_navigation($n['ns'], $isNS=true);
$folder['up'] = wiki_get_parent_path($n['ns']);
$recent_href = wiki_url('recent');

if($member['mb_level'] >= $board[bo_write_level]) {
	$create_doc_href = "javascript:createDoc('".wiki_input_value(preg_replace("/\'/", "\\\'", $folder['loc']))."');";
} else $create_doc_href = "";

if($n['ns_access_level'] > $member['mb_level']) {
	$wikiControl =& wiki_class_load("Control");
	$wikiControl->notAllowedFolder($n['ns']);
}
$folder_list = $wikiNS->getList($n['ns'], $withArticle=true);

if($is_wiki_admin) {	
	$wiki_admin_href = $wiki['url']."/adm/index.php?bo_table=".$wiki['bo_table'];
	$f = $wikiNS->namespaces("/", $withArticle=false);
	
	$all_folders = array();
	foreach($f as $vp => $rp)
	{
		array_push($all_folders, array("display"=>$vp, "path"=>$rp));
	}	
}

$tree = $wikiNS->get_tree("/", $n['ns']);

include_once WIKI_PATH."/head.php";
include_once $wiki['inc_skin_path']."/folder.skin.php";
include_once WIKI_PATH."/tail.php";
?>
