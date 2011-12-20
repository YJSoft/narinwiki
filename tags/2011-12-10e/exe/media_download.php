<?
/**
 *
 * 미디어 파일 다운로드 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once "_common.php";

$media =& wiki_class_load("Media");
$fileinfo = $media->getFile($file);

if(!$fileinfo) {
	header("HTTP/1.0 404 Not Found");
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


if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {	
    header("content-type: doesn/matter");
    header("content-length: ".filesize("$filepath"));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-transfer-encoding: binary");
} else {
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
