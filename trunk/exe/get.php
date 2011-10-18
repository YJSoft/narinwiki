<?
include_once "./_common.php";

wiki_only_ajax();
$wikiConfig = wiki_class_load("Config");

// 문서이력 내용 보기
if($w == "history" && $doc && $hid) {	
	list($ns, $docname, $doc) = wiki_validate_doc($doc);					

	$article = wiki_class_load("Article");
	$view = $article->getArticle($ns, $docname);

		// 권한 체크
	if($view[mb_id] && $view[mb_id] == $member[mb_id]) $is_doc_owner = true;
	else $is_doc_owner = false;
	
	$history_access_level = $wikiConfig->setting[history_access_level];
	
	if( !$is_doc_owner && $is_wiki_admin && $member[mb_level] < $history_access_level)
	{
		echo "권한 없음";
		exit;
	}
	
	$history = wiki_class_load("History");
	$row = $history->get($hid);
	echo nl2br(wiki_text($row[content]));
	exit;	
}

// 문서 검색 (by toolbar)
if($w == "find_doc" && $find_doc) {
	$sql = "SELECT * FROM $wiki[write_table] AS wt LEFT JOIN $wiki[nsboard_table] AS nt ON nt.bo_table = '$wiki[bo_table]' AND wt.wr_id = nt.wr_id WHERE nt.ns <> '' AND wt.wr_subject LIKE '%$find_doc%'";
	$result = sql_list($sql);
	$list = array();
	foreach($result as $idx => $v) {
		array_push($list, array("folder"=>$v[ns], "docname"=>$v[wr_subject]));
	}
	echo json_encode($list);
	exit;
}

// 폴더 목록 
if($w == "folderlist") {
	$wikiNS = wiki_class_load("Namespace");
	$folders = $wikiNS->namespaces("/", $withArticle=false);
	
	$json = array();
	foreach($folders as $vp => $rp)
	{
		array_push($json, array("display"=>$vp, "path"=>$rp));
	}
	echo json_encode($json);
	exit;
}
?>