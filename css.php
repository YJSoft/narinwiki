<?
/**
 * 
 * CSS 병합 & minify 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
include_once "_common.php";

$offset = 60 * 60 * 24 * 7; // Cache for 1 weeks

$wikiCss = wiki_class_load('JsCss');

$css_file = isset($print) ? $wikiCss->css_print : $wikiCss->css;
$wikiCss->updateCss();

if(!file_exists($css_file)) {
	$wikiCss->updateCss();
}

if(!file_exists($css_file)) {
	header("HTTP/1.0 404 Not Found");
	exit;	
}

$modified = filemtime($css_file);

header ('Expires: ' . gmdate ("D, d M Y H:i:s", time() + $offset) . ' GMT');

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified) {	
	header("HTTP/1.0 304 Not Modified");
	header ('Cache-Control:');
	exit;	
}

header ('Cache-Control: max-age=' . $offset);
header ('Content-type: text/css; charset='.$g4['charset']);
header ('Pragma:');
header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $modified )." GMT");

echo file_get_contents($css_file);    

?>
