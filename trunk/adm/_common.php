<?
$wiki_path = ".."; // common.php 의 상대 경로
include_once("$wiki_path/common.php");
if(!$is_wiki_admin) {
	alert("접근 금지!!");
}
?>