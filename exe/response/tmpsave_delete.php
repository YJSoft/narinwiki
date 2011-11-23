<?
/**
 * 임시 저장 삭제
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$member[mb_id] || !$wr_doc) wiki_ajax_error();

$id = md5($member[mb_id]."_".$wr_doc);
$reg = "tmpsave/$id";	
wiki_set_option($reg, null, null);
echo 1;		

?>