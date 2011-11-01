<?
/**
 * 나린위키 스킨 : 에러 출력 스킨
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined('_GNUBOARD_')) exit;

// 사용가능한 변수
// $title, $msg
?>
<h1><?=$title?></h1>
<?=$msg?>
<div class="wikiToolbar">
	<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
	<span class="button green"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
</div>