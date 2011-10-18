<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("DELETE_ALL_HEAD", array("wr_id"=>$wr_id, "folder"=>$folder, "chk_wr_id"=>$_POST[chk_wr_id])));

?>