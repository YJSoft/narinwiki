<?
/**
 * 
 * 주어진 미디어 폴더의 모든 파일 삭제
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc || !$is_wiki_admin) wiki_ajax_error();

$loc = wiki_ajax_data($loc);

$media = wiki_class_load("Media");	
$media->clear($loc);

echo wiki_json_encode(array('code'=>1));


?>