<?
include_once "./_common.php";

//wiki_only_ajax();
$wikiConfig = wiki_class_load("Config");

// 문서 검색 (by toolbar)
if($w == "find_doc" && $find_doc) {
	
	if(wiki_is_euckr()) $find_doc = iconv("UTF-8", "CP949", rawurldecode($find_doc)); 
	
	$sql = "SELECT * FROM $wiki[write_table] AS wt LEFT JOIN $wiki[nsboard_table] AS nt ON nt.bo_table = '$wiki[bo_table]' AND wt.wr_id = nt.wr_id WHERE nt.ns <> '' AND wt.wr_subject LIKE '%$find_doc%'";
	$result = sql_list($sql);
	$list = array();
	foreach($result as $idx => $v) {
		array_push($list, array("folder"=>$v[ns], "docname"=>$v[wr_subject]));
	}

	if(wiki_is_euckr()) wiki_ajax_data($list);
	
	echo json_encode($list);
	exit;
}

// 플러그인 명령 실행
if($w == "plugin" && $p && $m) {
	$wikiEvent->trigger("AJAX_CALL", array("plugin"=>$p, "method"=>$m, "get"=>$_GET, "post"=>$_POST));
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


function wiki_ajax_data(&$arr) {
	if(!is_array($arr)) {
		$arr = iconv("CP949", "UTF-8", $arr);
		return;
	}
	foreach($arr as $k => $v) {
		wiki_ajax_data($arr[$k]);
	}
}

?>