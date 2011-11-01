<?
/**
 * 인터셉트 : skin 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
include_once $wiki[path]."/inc/inc.write_comment_update.head.php";

$board_skin_path = $wiki[skin_path];
@include_once $wiki[skin_path]."/write_comment_update.head.skin.php";
$board_skin_path = $wiki[inc_skin_path];
?>                                                                                            