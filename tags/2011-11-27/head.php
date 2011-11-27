<?
/**
 * 머리 문서 include 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($wiki[head_file] && !$no_layout) include_once $wiki[path] . "/" . $wiki[head_file];
else include_once $g4[path]."/head.sub.php";
?>
