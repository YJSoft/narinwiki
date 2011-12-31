<?
/**
 * 
 * 미디어 파일 등록
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc || !$source || !$file) wiki_ajax_error();

$loc = wiki_ajax_data($loc);
$file = wiki_ajax_data($file);
$source = wiki_ajax_data($source);

$media =& wiki_class_load("Media");
$thumb =& wiki_class_load("Thumb");

$ns = $media->getNS($loc);		
if($ns['ns_access_level'] > $member['mb_level'] || $ns['ns_upload_level'] > $member['mb_level']) {
	echo "권한이 없습니다.";
	exit;
}

$media->addFile($loc, $source, $file);

$thumb_width = 30;
$thumb_height = 30;
$f = $media->getFile($loc, $source);	
if($f['img_width']) {
	$thumb_path = $thumb->getMediaThumb($ns=$loc, $filename=$f['source'], $thumb_width, $thumb_height, $quality=90, $crop=true);
	$f['thumb'] = $thumb_path;
} else $f['thumb'] = "";

preg_match("/\.([a-zA-Z0-9]{2,4})$/", $f['source'], $m);
if($m[1] && file_exists(WIKI_PATH.'/imgs/media_manager/ext/'.strtolower($m[1]).'.png')) {		
	$f['ext_icon'] = $wiki['url'].'/imgs/media_manager/ext/'.strtolower($m[1]).'.png';			
} else $f['ext_icon'] = $wiki['url'].'/imgs/media_manager/ext/_blank.png';

$f['code'] = 1;
$f['filesize'] = wiki_file_size($f['filesize']);

$json = wiki_json_encode($f);
	
wiki_set_option("uploading", $file, null);

// uploading 이 기록된 시간이 6시간 이전이면..
// (6시간 이전에 파일 올리다 중단된 것이면)
// 삭제
$ctime = time();
$expire = 6*60*60; // 6시간
$not_completed_files = wiki_get_option("uploading");
if(!empty($not_completed_files)) {
	foreach($not_completed_files as $file => $timestamp) {	
		if($ctime - $timestamp > $expire) {		
			$deleted = $media->deleteUnusedFile($file);
			unset($not_completed_files[$file]);
		}
	}		
	$uploading_files = array();
	$uploading_times = array();
	foreach($not_completed_files as $file => $timestamp) {
		array_push($uploading_files, $file);
		array_push($uploading_times, $timestamp);
	}	
	wiki_set_option("uploading", $uploading_files, $uploading_times);
} else {
	wiki_set_option("uploading", null, null);
}

echo $json;

?>
