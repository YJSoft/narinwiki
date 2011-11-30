<?
/**
 * 
 * 인터셉트 : skin 스크립트
 *
 * @package	narinwiki
 * @subpackage wrapper
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined('_GNUBOARD_')) exit;

$board_skin_path = $wiki['skin_path']; // 개별 페이지 접근 불가
@include_once $wiki['skin_path']."/error.skin.php";
$board_skin_path = $wiki['inc_skin_path'];
?>