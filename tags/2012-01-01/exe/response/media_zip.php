<?
/**
 * 
 * 미디어 폴더 zip
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
$files = $media->getList($loc);

if(empty($files)) {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'빈 폴더입니다.'));
	exit;	
}

if(!class_exists('ZipArchive')) {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'PHP 에서 ZipArchive 를 지원하지 않습니다.'));
	exit;	
}
$zip = new ZipArchive();
$name = md5(time());
$zipFile = WIKI_PATH.'/data/'.$bo_table.'/'.$name.'.zip';

if(!$zip->open($zipFile, ZIPARCHIVE::CREATE)) {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'압축파일 생성 실패 (1)'));
	exit;
}

foreach($files as $k=>$file) {
	$zip->addFile($file['path'], $file['source']);
}

$zip->close();

if(!file_exists($zipFile)) {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'압축파일 생성 실패 (2)'));
	exit;
}

echo wiki_json_encode(array('code'=>1, 'file'=>$name));
	
?>
