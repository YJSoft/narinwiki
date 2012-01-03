<?
/**
 * 
 * 위키 관리 tail 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

</div> <!--// wiki_admin -->

<div id="wiki_admin_tail">
	<span class="button"><a href="<?=wiki_url()?>">시작페이지</a></span>
	<? if($is_admin == 'super') { ?>
	<span class="button"><a href="<?=$wiki['g4_url']?>/adm/board_form.php?w=u&bo_table=<?=$bo_table?>">게시판관리</a></span>
	<?}?>
</div>

<?
include_once "../tail.php";
?>
