<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("WRITE", array("folder"=>$ns, 
																	"docname"=>$docname, 
																	"doc"=>$doc,
																	"w"=>$w,
																	"wr_id"=>$wr_id,
																	"subject"=>$subject,
																	"title_msg"=>$title_msg,
																	"content"=>$content, 
																	"write"=>&$write)));
?>
