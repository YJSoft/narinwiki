<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once $wiki[path] . "/inc/inc.write.tail.php";

$board_skin_path = $wiki[skin_path];
@include_once $wiki[skin_path]."/write.tail.skin.php";
$board_skin_path = $wiki[inc_skin_path];
?>
