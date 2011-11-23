<?
/**
 * 미디어 파일 삭제
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc || !$file) wiki_ajax_error();

$loc = wiki_ajax_data($loc);
$file = wiki_ajax_data($file);

$media = wiki_class_load("Media");	
$file_info = $media->getFile($loc, $file);

if(!$file_info) {
	$ret = array('code'=>'-1', 'msg'=>'파일 정보가 없습니다.');
	echo wiki_json_encode($ret);
	exit;		
}

// 권한 검사
if($file_info['mb_id'] != $member['mb_id'] && !$is_wiki_admin) {
	$ret = array('code'=>'-1', 'msg'=>'권한이 없습니다.');
	echo wiki_json_encode($ret);
	exit;
}

$media->deleteFile($loc, $file);

$thumb = wiki_class_load("Thumb");	
$thumb->deleteThumb("media-".$wiki['bo_table']."-".$file_info['id']);
echo wiki_json_encode(array('code'=>1));


?>