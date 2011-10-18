<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("DOWNLOAD_TAIL", array("wr_id"=>$wr_id, 
																	"no"=>$no,
																	"filepath"=>&$filepath)));
?>