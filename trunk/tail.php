<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if($wiki[tail_file]) include_once $wiki[path] . "/" . $wiki[tail_file];
else include_once $g4[path]."/tail.sub.php";
?>