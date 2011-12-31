<?
/**
 * 
 * 꼬리 문서 include 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<?
if($wiki['tail_file'] && !$no_layout) include_once WIKI_PATH . "/" . $wiki['tail_file'];
else include_once G4_PATH."/tail.sub.php";
?>
