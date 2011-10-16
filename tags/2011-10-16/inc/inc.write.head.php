<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($doc) list($ns, $docname, $doc) = wiki_page_name($doc);

extract($wikiEvent->trigger("WRITE_HEAD", array("folder"=>$ns, 
																	"docname"=>$docname, 
																	"doc"=>$doc,
																	"w"=>$w,
																	"wr_id"=>$wr_id,
																	"content"=>&$content, 
																	"write"=>&$write)));
?>