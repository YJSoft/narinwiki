<?
/**
 * 
 * 문서 검색 응답 (by toolbar)
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(wiki_is_euckr()) $find_doc = iconv("UTF-8", "CP949", rawurldecode($find_doc)); 

$sql = "SELECT * FROM ".$wiki['write_table']." AS wt 
			  LEFT JOIN ".$wiki['nsboard_table']." AS nt ON nt.bo_table = '".$wiki['bo_table']."' AND wt.wr_id = nt.wr_id 
			  WHERE nt.ns <> '' AND wt.wr_subject LIKE '%$find_doc%'";
$result = sql_list($sql);
$list = array();
foreach($result as $idx => $v) {
	array_push($list, array("folder"=>$v['ns'], "docname"=>$v['wr_subject']));
}

echo wiki_json_encode($list);

?>