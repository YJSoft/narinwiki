<?
/**
 *
 * 미디어 파일 다운로드 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once "_common.php";

$media =& wiki_class_load("Media");
if($file{0} != '/') $file = '/'.$file;

$fileinfo = $media->getFile($file);

if(!$fileinfo) {
	print_r2($_GET);
	//header("HTTP/1.0 404 Not Found");
	exit;
}

$ns = $media->getNS($fileinfo['ns']);
if($ns['ns_access_level'] > $member['mb_level']) {
	alert('권한 없음');
	exit;
}


if(!$fileinfo['img_width'] && !$is_wiki_admin) {
	$media->updateDownloadCount($fileinfo['id']);
}

$original = $fileinfo['source'];
$filepath = $fileinfo['path'];

$ftime = filemtime($filepath);

header("Cache-Control: private, max-age=10800, pre-check=10800");
header("Pragma: private");
header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));

// 캐쉬된 이미지를 써라..
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $ftime)) {
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $ftime).' GMT', true, 304);
  exit;
}

// 그누보드 다운로드 참고
if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {	
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filepath)).' GMT', true, 200);
    header("content-type: doesn/matter");
    header("content-length: ".filesize("$filepath"));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-transfer-encoding: binary");
} else {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filepath)).' GMT', true, 200);
    header("content-type: file/unknown");
    header("content-length: ".filesize("$filepath"));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-description: php generated data");
}
header("pragma: no-cache");
header("expires: 0");
flush();

$fp = fopen($filepath, "rb");

$download_rate = 10;

while(!feof($fp)) {
    print fread($fp, round($download_rate * 1024));
    flush();
    usleep(1000);
}
fclose ($fp);
flush();		

?>
