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



if(!$loc || !$is_wiki_admin || !$file) wiki_not_found_page();

$filepath = WIKI_PATH.'/data/'.$bo_table.'/'.$file.'.zip';

if(!file_exists($filepath)) {
	wiki_not_found_page();
}

$original = basename($loc).'.zip';
if($original == '.zip') $original = 'narin_media.zip';

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


@unlink($filepath);

?>
