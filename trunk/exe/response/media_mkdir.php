<?
/**
 * 미디어 폴더 생성
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$ploc || !$loc) wiki_ajax_error();

$loc = wiki_ajax_data($loc);
$ploc = wiki_ajax_data($ploc);

$media = wiki_class_load("Media");
$parent = $media->getNS($ploc);
if(!$parent && $ploc == '/') {
	$media->addNamespace('/');
} else if(!$parent || $parent['ns_mkdir_level'] > $member['mb_level']) {
	$ret = array('code'=>'-1', 'msg'=>'권한이 없습니다.');
	echo wiki_json_encode($ret);
	exit;		
}

if(!wiki_check_folder_name($loc)) {
	$ret = array('code'=>'-1', 'msg'=>'폴더명 형식이 잘못되었습니다');
	echo wiki_json_encode($ret);
	exit;
}

$media->addNamespace($loc, $parent);
echo wiki_json_encode(array('code'=>1));
	
?>