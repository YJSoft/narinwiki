<?
/**
 *
 * 나린위키 메인 페이지
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
include_once("./_common.php");

if(!trim($docname)) header("location:".$wiki['path']);

ob_start();	
$wikiArticle = wiki_class_load("Article");
$wikiControl = wiki_class_load("Control");

$view = $wikiArticle->getArticle($ns, $docname);

$wikiControl->acl($doc);	

if(!$view) {
	$wikiControl->noDocument($ns, $docname, $doc);
} else {		
	$wikiControl->viewDocument($doc, $view['wr_id']);	
}

$content = ob_get_contents();
ob_end_clean();

include_once $wiki['path']."/lib/Minifier/htmlmin.php";
include_once $wiki['path']."/lib/Minifier/jsmin.php";
include_once $wiki['path']."/lib/Minifier/cssmin.php";
echo Minify_HTML::minify($content, $options=array("jsMinifier"=>"JSMin::minify", "cssMinifier"=>"CssMin::minify"));

?>
