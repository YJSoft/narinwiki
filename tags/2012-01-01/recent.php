<?
/**
 * 
 * 최근 업데이트 문서 목록 보기 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("./_common.php");

$wikiControl =& wiki_class_load("Control");

$history_access_level = $wikiConfig->setting['history_access_level'];
if($member['mb_level'] < $history_access_level) {
	$wikiControl->error("권한 없음", "문서 이력 조회 권한이 없습니다.");
}

$page_rows = 15;
if(!$page) $page = 1;

$sql_all = "SELECT id FROM ".$wiki['changes_table']." WHERE bo_table = '".$wiki['bo_table']."'";				
$result = sql_query($sql_all);
$total_count = mysql_num_rows($result);

$total_page  = ceil($total_count / $page_rows);
$from_record = ($page - 1) * $page_rows;

$sql = "SELECT * FROM ".$wiki['changes_table']." WHERE bo_table = '".$wiki['bo_table']."' ORDER BY id DESC LIMIT $from_record, $page_rows";

$page_base_url = wiki_url('recent', array('page'=>''));
$paging = get_paging(10, $page, $total_page, $page_base_url);


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
	if($row['target_type'] == "DOC") {
		$row['view_href'] = wiki_url('read', array('doc'=>$row[target]));
	} else if($row['target_type'] == "FOLDER") {
		$row['view_href'] = wiki_url('folder', array('loc'=>$row[target]));		
	}
	array_push($list, $row);
}


$wikiControl->includePage($wiki['inc_skin_path'] . "/recent.skin.php", $layout=true);	

?>
