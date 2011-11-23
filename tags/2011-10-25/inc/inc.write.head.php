<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($doc) list($ns, $docname, $doc) = wiki_page_name($doc);
if(!$doc && $wr_id) {
	$wikiArticle = wiki_class_load("Article");
	$tmp = $wikiArticle->getArticleById($wr_id);
	$doc = wiki_doc($tmp[ns], $tmp[doc]);
	list($ns, $docname, $doc) = wiki_page_name($doc);
}
extract($wikiEvent->trigger("WRITE_HEAD", array("folder"=>$ns, 
																	"docname"=>$docname, 
																	"doc"=>$doc,
																	"w"=>$w,
																	"wr_id"=>$wr_id,
																	"content"=>&$content, 
																	"write"=>&$write)));
?>