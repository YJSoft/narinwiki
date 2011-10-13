<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("WRITE_COMMENT_UPDATE_TAIL", array("w"=>$w, 
																			"wr_id"=>$wr_id, 
																			"wr_name"=>stripcslashes($wr_name), 
																			"wr_email"=>stripcslashes($wr_email), 
																			"wr_content"=>stripcslashes($wr_content),
																			"comment_id"=>$comment_id)));	

?>