<?
/**
 * 
 * 미디어 파일 목록
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

$media =& wiki_class_load("Media");
$ns = $media->getNS($loc);

if(!$ns) {
	echo wiki_json_encode(array('code'=>-101, 'msg'=>'존재하지 않는 폴더입니다.'));
	exit;
}


// 권한 검사
if($ns['ns_access_level'] > $member['mb_level']) {
	$ret = array('code'=>'-1', 'msg'=>'권한 없음');
	echo wiki_json_encode($ret);
	exit;
}

$thumb =& wiki_class_load("Thumb");
$thumb_width = 30;
$thumb_height = 30;	
$files = $media->getList($loc);
foreach($files as $k=>$file) {
	if($file['img_width'] > 0) {
		$thumb_path = $thumb->getMediaThumb($loc, $filename=$file['source'], $thumb_width, $thumb_height, $quality=90, $crop=true);
		$files[$k]['thumb'] = $thumb_path;
	} else $files[$k]['thumb'] = "";
	preg_match("/\.([a-zA-Z0-9]{2,4})$/", $file['source'], $m);
	if($m[1] && file_exists($wiki['path'].'/imgs/media_manager/ext/'.strtolower($m[1]).'.png')) {		
		$files[$k]['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/'.strtolower($m[1]).'.png';			
	} else $files[$k]['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/_blank.png';
	$files[$k]['filesize'] = wiki_file_size($file['filesize']);
	$files[$k]['bytes'] = $file['filesize'];
}

$ploc = wiki_get_parent_path($loc);
$pNS = $media->getNS($ploc);

$ret = array('code'=>1, 'files'=>$files, 'parent_mkdir_level'=>$pNS['ns_mkdir_level'], 'mkdir_level'=>$ns['ns_mkdir_level'], 'upload_level'=>$ns['ns_upload_level'], 'access_level'=>$ns['ns_access_level']);
echo wiki_json_encode($ret);	

?>
