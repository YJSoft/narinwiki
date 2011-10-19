<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("VIEW_HEAD", array("folder"=>$ns, 
																				"docname"=>$docname, 
																				"doc"=>$doc,
																				"wr_id"=>$wr_id, 
																				"view"=>&$view)));

?>