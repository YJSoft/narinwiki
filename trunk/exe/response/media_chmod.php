<?
/**
 * 
 * 미디어 폴더 권한 변경
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc || !$is_wiki_admin) wiki_ajax_error();

$loc = wiki_ajax_data($loc);

$media =& wiki_class_load("Media");
$media->updateLevel(stripcslashes($loc), $access_level, $upload_level, $mkdir_level);
$ret = array('code'=>'1', 'access_level'=>$access_level, 'upload_level'=>$upload_level, 'mkdir_level'=>$mkdir_level);
echo wiki_json_encode($ret);
	
?>
