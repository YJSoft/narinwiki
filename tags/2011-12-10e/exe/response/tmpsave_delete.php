<?
/**
 * 
 * 임시 저장 삭제
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$member['mb_id'] || !$wr_doc) wiki_ajax_error();

$id = md5($member['mb_id']."_".$wr_doc);
$reg = "tmpsave/$id";	
wiki_set_option($reg, null, null);
echo 1;		

?>