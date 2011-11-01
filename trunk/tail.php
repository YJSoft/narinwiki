<?
/**
 * 꼬리 문서 include 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if($wiki[tail_file]) include_once $wiki[path] . "/" . $wiki[tail_file];
else include_once $g4[path]."/tail.sub.php";
?>