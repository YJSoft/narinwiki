<?
include_once "./_common.php";


$wikiArticle = wiki_class_load("Article");
$wikiNS = wiki_class_load("Namespace");

$loc = stripcslashes($loc);
$wiki_loc = stripcslashes($wiki_loc);
$wiki_template = stripcslashes($wiki_template);

wiki_validate_folder($wiki_loc);

$ns = $wikiNS->get($loc);

if($w != 'u' || !$loc || !$wiki_loc || !$ns || !$wiki_access_level || !$is_wiki_admin)
{
	alert("권한이 없습니다");
	exit;
}

// 문자열 끝에 '/' 가 있다면 '/' 삭제
$loc = preg_replace("/\/$/", "", $loc);
$wiki_loc = preg_replace("/\/$/", "", $wiki_loc);
if(!$wiki_loc) $wiki_loc = "/";

if($ns[ns] != "/" && $wiki_loc != "/" && $ns[ns] != $wiki_loc) {
	$wikiNS->updateNamespace($loc, $wiki_loc);
}

$wikiNS->setTemplate($wiki_loc, $wiki_template);


if($write[access_level] != $wiki_access_level) {
	$wikiNS->updateAccessLevel($wiki_loc, $wiki_access_level);
}

header("location:{$wiki[path]}/folder.php?bo_table={$wiki[bo_table]}&loc=".urlencode(stripcslashes($wiki_loc)));

?>