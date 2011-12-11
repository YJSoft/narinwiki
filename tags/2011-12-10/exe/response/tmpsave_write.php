<?
/**
 * 
 * 임시 저장
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();


if(!$member['mb_id']) wiki_ajax_error('로그인한 사용자만 사용할 수 있습니다.');
if(!$wr_doc) wiki_ajax_error('문서 제목이 누락되었습니다.');
if(!$wr_content) wiki_ajax_error('문서 내용이 없습니다.'); 

$id = md5($member['mb_id']."_".$wr_doc);
$reg = "tmpsave/$id";	
wiki_set_option($reg, array("wr_content", "wr_date"), array($wr_content, date("Y-m-d h:i:s")));

echo json_encode(array('code'=>1));

?>