<?
/**
 * 
 * 나린위키 스킨 : 최근 변경 내역 스킨
 *
 * 최근변경 내역 보여주는 스킨 페이지
 *
 * <b>사용 변수</b>
 * - $list : 최근 변경 내역 목록
 * - $paging : 페이징
 * - $clear_href : 관리자 일 경우
 * - $member : 로그인 정보 ($member['mb_id'], $member['mb_level'], $member['mb_nick'] ...)
 * - $is_admin : 그누보드 관리자 인가
 * - $is_wiki_admin : 위키 관리자인가
 *
 * @package	narinwiki
 * @subpackage skin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; //개별 페이지 접근 불가 

$colspan = 2;
if($clear_href) $colspan++;
?>
<h1>최근 변경 내역</h1>

<div class="list_table">
	
	<table id="wiki_changes" width="100%" border="1" cellspacing="0" summary="최근 변경 내역  목록">
	<tbody>
	<tr>
		<?if($clear_href) { ?><th scope="col" width="30px"><input type="checkbox" name="checkall" /></th><?}?>
		<th scope="col" width="120px">날짜</th>
		<th scope="col">변경내역</th>
	</tr>		
	
	<? for($i=0; $i<count($list); $i++) {?>
	<tr>
		<? if($clear_href) { ?>
		<td>
			<input type="checkbox" name="cid[]" value="<?=$list[$i][id]?>"/>
		</td>	
		<? } ?>
		<td class="changes_date">
			<?=$list[$i][reg_date]?>
		</td>
		<td class="changes_doc">
			<a href="<?=$list[$i][view_href]?>"><?=$list[$i][target]?></a>&nbsp;
			: <?=$list[$i][status]?>&nbsp;
			- <?=($list[$i][user] ? $list[$i][user] : $list[$i][ip_addr])?>
		</td>		

	</tr>
	
	<? } ?>
	
	<? if($paging) { ?>
	<tr><td colspan="<?=$colspan?>" style="padding-top:10px"><?=$paging?></td></tr>
	<? } ?>
	</tbody>
	</table>
</div>
	
<? if(!count($list)) {?>

<div style="height:100px">
	변경 내역이 존재 하지 않습니다.
</div>	
		
<?
}
?>

<div class="wiki_tools clear">

	<div class="wiki_tools_left">
		<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
		<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
	</div>
	<div class="wiki_tools_right">
		<? if($clear_href) { ?>
		<span class="button"><a href="<?=$delete_selected_href?>">선택 이력 삭제</a></span>
		<span class="button"><a href="<?=$clear_href?>">모든 이력 삭제</a></span>
		<?}?>
	</div>
</div>

<? if($clear_href) {?>
<script type="text/javascript">
	$(document).ready(function() {
		$('input[name="checkall"]').click(function() {
		  $("input[name='cid[]']").attr('checked', $(this).attr('checked'));
		});		
	});
</script>
<?}?>