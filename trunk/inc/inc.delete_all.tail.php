<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 


extract($wikiEvent->trigger("DELETE_ALL_TAIL", array("folder"=>$folder, "wr_id_array"=>$tmp_array)));


?>