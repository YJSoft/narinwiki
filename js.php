<?
/**
 * 
 * js 병합 & minify 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once "_common.php";

$offset = 60 * 60 * 24 * 7; // Cache for 1 weeks

$wikiJs = wiki_class_load('JsCss');

$wikiJs->updateJs();

if(!file_exists($wikiJs->js)) {
	$wikiJs->updateJs();
}

if(!file_exists($wikiJs->js)) {
	header("HTTP/1.0 404 Not Found");
	exit;	
}

$modified = filemtime($wikiJs->js);

header ('Expires: ' . gmdate ("D, d M Y H:i:s", time() + $offset) . ' GMT');

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified) {	
	header("HTTP/1.0 304 Not Modified");
	header ('Cache-Control:');
	exit;	
}

header ('Cache-Control: max-age=' . $offset);
header ('Content-type: text/javascript; charset='.$g4['charset']);
header ('Pragma:');
header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $modified )." GMT");

echo file_get_contents($wikiJs->js);    

?>
