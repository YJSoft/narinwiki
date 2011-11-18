<?
/**
 * include skin 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($doc) list($ns, $docname, $doc) = wiki_page_name($doc);
if(!$doc && $wr_id) {
	$wikiArticle = wiki_class_load("Article");
	$tmp = $wikiArticle->getArticleById($wr_id);
	$doc = wiki_doc($tmp[ns], $tmp[doc]);
	list($ns, $docname, $doc) = wiki_page_name($doc);
}
extract($wikiEvent->trigger("WRITE_TAIL", array("folder"=>$ns, 
																	"docname"=>$docname, 
																	"doc"=>$doc,
																	"w"=>$w,
																	"wr_id"=>$wr_id,
																	"content"=>&$content, 
																	"write"=>&$write)));
?>