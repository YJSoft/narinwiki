<?
/**
 * 
 * 임시 저장 삭제
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$member['mb_id'] || !$wr_doc) wiki_ajax_error();

$id = md5($member['mb_id']."_".$wr_doc);
$reg = "tmpsave/$id";	
$tmp_saved = wiki_get_option($reg);	
$ret = array();	
if($tmp_saved) {
	$ret['code'] = 1;
	$ret['wr_date'] = $tmp_saved['wr_date'];
	$ret['wr_content'] = $tmp_saved['wr_content'];
} else {
	$ret['code'] = -1;
}	
echo wiki_json_encode($ret);		

?>
