<?
/**
 * 위키 관리 tail 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

</div> <!--// wiki_admin -->

<div id="wiki_admin_tail">
	<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
	<? if($is_admin == 'super') { ?>
	<span class="button"><a href="<?=$g4[path]?>/adm/board_form.php?w=u&bo_table=<?=$wiki[bo_table]?>">게시판관리</a></span>
	<?}?>
</div>

<?
include_once "../tail.php";
?>