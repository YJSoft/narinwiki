<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("DELETE_COMMENT_HEAD", array("comment_id"=>$comment_id)));

?>