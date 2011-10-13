<?
include_once("_common.php");

if(!$is_wiki_admin) alert("접근 금지");

// validation
foreach($chk as $idx => $i) {
	
	$wr_folder[$i] = stripcslashes($wr_folder[$i]);
	$wr_subject[$i] = stripcslashes($wr_subject[$i]);
		
	if(!wiki_check_doc_name($wr_subject[$i])) {
		alert("문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /");
		exit;
	}
	if(!wiki_check_folder_name($wr_folder[$i])) {
		alert("폴더명에 다음 문자는 사용할 수 없습니다 : \\, |");
		exit;
	}	
}

// update	
$wikiArticle = wiki_class_load("Article");
foreach($chk as $idx => $i) {	
	$wr_folder[$i] = preg_replace("/\/$/", "", $wr_folder[$i]);
	$doc = $wr_folder[$i] . "/" . $wr_subject[$i];
	$wikiArticle->addArticle($doc, $_POST[wr_id][$i]);	
}

header("location:{$wiki[path]}/adm/nowiki.php?bo_table={$bo_table}");
?>


