<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 나린위키 외부에서 사용하기 위한 함수 모음
 *
 * 함수 명명규칙 : nw_ 로 시작하도록 함
 */


/**
 * 위키 폴더 내의 문서/폴더 목록 반환
 * @params (string) $wiki_path 위키 경로 (g4로 부터 상대경로)
 * @params (string) $bo_table 위키로 사용되는 bo_table
 * @params (string) $folder 조회하고자 하는 폴더
 * @params (boolean) $witharticle true 면 폴더 목록과 함께 문서목록 반환, false 면 폴더 목록만 반환
 * @return (mixed) 목록 배열
 */
function nw_get_list($wiki_path, $bo_table, $folder, $withArticle=true) {		
	global $g4;
	include_once $wiki_path . "/narin.config.php";
	$wiki_path = $wiki[path];
	$ns_table = $wiki[ns_table];
	$nsboard_table = $wiki[nsboard_table];
	$write_table = $g4['write_prefix'] . $bo_table;
	$escapedParent = mysql_real_escape_string($folder);
	$regp = ($folder == "/" ? "/" : $escapedParent."/");	
	if($parent != "/") {
		$add =	"nt.ns = '$escapedParent' OR ";
		$addSlash = "/";
	}
	
	$sql = "SELECT *  FROM $ns_table WHERE $add ns LIKE '$escapedParent%' AND ns NOT REGEXP '^$regp(.*)/' AND bo_table ='$bo_table'";
	if($withArticle) {
		$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc, wb.wr_id FROM $ns_table AS nt LEFT JOIN $nsboard_table AS nb ON nt.ns = nb.ns AND nt.bo_table = nb.bo_table LEFT JOIN $write_table AS wb ON nb.wr_id = wb.wr_id WHERE ( $add nt.ns LIKE '$escapedParent$addSlash%' AND nt.ns NOT REGEXP '^$regp(.*)/' ) AND nt.bo_table = '$bo_table'ORDER BY wb.wr_subject";
	}		
	
	$folders = array();
	$files = array();
	$already = array();
	$result = sql_query($sql);
	while ($row = sql_fetch_array($result))	
	{
		if($row[ns] == $parent) {
			if(!$row[doc]) continue;
			$path = ($row[ns] == "/" ? "/" : $row[ns]."/").$row[doc];
			$href = $wiki_path.'/narin.php?bo_table='.$bo_table.'&doc='.urlencode($path);
			$ilink = "[[".$path."]]";
			array_push($files, array("name"=>$row[doc], "path"=>$path, "href"=>$href, "internal_link"=>$ilink, "wr_id"=>$row[wr_id], "type"=>"doc"));
		} else {				
			$href = $wiki_path.'/folder.php?bo_table='.$bo_table.'&loc='.urlencode($row[ns]);
			$name = ereg_replace($parent."/", "", $row[ns]);
			$name = ereg_replace($parent, "", $name);			
			if($already[$name]) continue;
			$already[$name] = $name;
			$ilink = "[[folder=".$row[ns]."]]";
			array_push($folders, array("name"=>$name, "path"=>$row[ns], "href"=>$href, "internal_link"=>$ilink, "type"=>"folder"));
		}		
	}
	if(count($folders)) $folders = nw_subval_asort($folders, "name");
	$list = array_merge($folders, $files);
	return $list;
}

/**
 * 연관배열의 키 순으로 정렬 (asort)
 * @params (array) $a 정렬할 배열
 * @params (string) $subkey 배열의 키값
 */
function nw_subval_asort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}

/**
 * 연관배열의 키 순으로 정렬 (sort)
 * @params (array) $a 정렬할 배열
 * @params (string) $subkey 배열의 키값
 */
function nw_subval_sort($a,$subkey) {
	$c = subval_asort($a, $subkey);
	$c = array_reverse($c);
	return $c;
}	
?>
