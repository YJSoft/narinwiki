<?
/**
 * 
 * include skin 스크립트
 *
 * @package	narinwiki
 * @subpackage inc
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
$board_skin_path = $wiki['inc_skin_path'];

ob_start();
include_once $g4['bbs_path']."/view_comment.php";
$content = ob_get_contents();
ob_clean();

if($wiki['fancy_url']) {
	
	// md5 스크립트 경로 수정
	$content = preg_replace("/(<script type=\'text\/javascript\' src=\')(.*?)(md5.js\'><\/script>)/i", "$1".$wiki['g4_url']."/js/$3", $content);
	
	// 폼 post 경로 수정 
	$content = preg_replace("/(action=\")(.*?)write_comment_update.php/i", "$1".$wiki['g4_url']."/bbs/write_comment_update.php", $content);
	
}

echo $content;

$board_skin_path = $wiki['skin_path'];
?>
