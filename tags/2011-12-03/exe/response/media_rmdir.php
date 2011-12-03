<?
/**
 * 
 * 미디어 폴더 삭제
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc) wiki_ajax_error();

$loc = wiki_ajax_data($loc);

if($loc == '/') {
	echo wiki_json_encode(array('code'=>'-1', 'msg'=>'루트 폴더는 삭제할 수 없습니다.'));
	exit;					
}

$media = wiki_class_load("Media");

$parent_ns = wiki_get_parent_path($loc);
$parent = $media->getNS($parent_ns);
if($parent['ns_mkdir_level'] > $member['mb_level']) {
	echo wiki_json_encode(array('code'=>'-1', 'msg'=>'권한이 없습니다.'));
	exit;			
}

$folder = $media->getNS($loc);
if($folder['ns_mkdir_level'] > $member['mb_level'] || $folder['ns_access_level'] > $member['mb_level']) {
	echo wiki_json_encode(array('code'=>'-1', 'msg'=>'권한이 없습니다.'));
	exit;			
}


$success = $media->deleteFolder($loc);
if($success) {
	echo wiki_json_encode(array('code'=>1, 'updir'=>wiki_get_parent_path($loc)));
}
else {
	$ret = array('code'=>'-1', 'msg'=>'빈폴더가 아닙니다.');
	echo wiki_json_encode($ret);		
}
	
?>