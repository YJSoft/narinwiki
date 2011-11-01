<?
/**
 * 최근 업데이트 문서 목록 보기 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("./_common.php");

$wikiControl = wiki_class_load("Control");

$history_access_level = $wikiConfig->setting[history_access_level];
if($member[mb_level] < $history_access_level) {
	$wikiControl->error("권한 없음", "문서 이력 조회 권한이 없습니다.");
}

$page_rows = 15;
if(!$page) $page = 1;

$sql_all = "SELECT id FROM {$wiki[changes_table]} WHERE bo_table = '{$wiki[bo_table]}'";				
$result = sql_query($sql_all);
$total_count = mysql_num_rows($result);

$total_page  = ceil($total_count / $page_rows);
$from_record = ($page - 1) * $page_rows;

$sql = "SELECT * FROM {$wiki[changes_table]} WHERE bo_table = '{$wiki[bo_table]}' ORDER BY id DESC LIMIT $from_record, $page_rows";
$paging = get_paging(10, $page, $total_page, $wiki[path]."/recent.php?bo_table=$bo_table&page=");


if($is_wiki_admin) {
	$clear_href = "javascript:clear_changes();";
	$delete_selected_href = "javascript:delete_selected_changes();";
} else {
	$clear_href = "";
	$delete_selected_href = "";
}

$list = array();
$res = sql_query($sql);
while($row = sql_fetch_array($res)) {
	$target = urlencode($row[target]);
	if($row[target_type] == "DOC") {
		$row[view_href] = $wiki[path]."/narin.php?bo_table=".$wiki[bo_table]."&doc=".$target;
	} else if($row[target_type] == "FOLDER") {
		$row[view_href] = $wiki[path]."/folder.php?bo_table=".$wiki[bo_table]."&loc=".$target;		
	}
	array_push($list, $row);
}


$wikiControl->includePage($wiki[inc_skin_path] . "/recent.skin.php", $layout=true);	

?>