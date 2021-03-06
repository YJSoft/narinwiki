<?
/**
 * 
 * 나린위키 스킨 : 문서이력 스킨
 * 
 * 문서이력보기 스킨 페이지
 * 
 * <b>사용 변수</b>
 * - $folder : 폴더 경로
 * - $docname : 문서명
 * - $doc : 폴더 경로 + 문서명
 * - $history : 문서이력 목록
 * - $member : 로그인 정보 ($member['mb_id'], $member['mb_level'], $member['mb_nick'] ...)
 * - $is_admin : 그누보드 관리자 인가
 * - $is_wiki_admin : 위키 관리자인가
 * 
 * <code> 
 * // $history 변수 형식
 * array(
 *   array("id"=>문서이력id,
 *         "wr_id"=>문서id,
 *         "content"=>내용,
 *         "recover_href"=>이 문서이력으로 복원하는 URL,
 *         "date"=>등록시간)
 * )
 * </code>
 * 
 * @package	narinwiki
 * @subpackage skin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; //개별 페이지 접근 불가 

/**********************************************
$folder : 폴더 경로
$docname : 문서명
$doc : 폴더 경로 + 문서명
$history = array("id"=>history_id,
                 "wr_id"=>wr_id,
                 "content"=>content,
                 "recover_href"=>recover href,
                 "date"=>datetime);
********************************************/
$colspan = 4;
if($clear_href) $colspan++;
$btn_width = 140;
if($clear_href) $btn_width = 220;
?>
<h1>문서이력 : <?=$docname?></h1>

<div class="list_table">
	
	<table id="wiki_history" width="100%" border="1" cellspacing="0" summary="문서이력 목록">
	<tbody>
	<tr>
		<?if($clear_href) { ?><th scope="col" width="30px"><input type="checkbox" name="checkall" /></th><?}?>
		<th scope="col" width="120px">날짜</th>
		<th scope="col" width="50px">편집자</th>
		<th scope="col">문서요약</th>		
		<th scope="col" width="<?=$btn_width?>px">명령</th>		
	</tr>		
	
	<? for($i=0; $i<count($history); $i++) {?>
	<tr>
		<? if($history[$i]['delete_href']) { ?>
		<td>
			<input type="checkbox" name="hid[]" value="<?=$history[$i]['id']?>"/>
		</td>	
		<?} else if($clear_href) {?>
		<td></td>
		<? } ?>
		<td class="history_date">
			<?=$history[$i]['reg_date']?>
		</td>
		<td class="history_editor">
			<?=$history[$i]['editor_mb_id']?>&nbsp;
		</td>		
		<td class="history_summary">
			<?=$history[$i]['summary']?>&nbsp;
		</td>
		<td class="history_cmd">
			<? if($i || $page > 1) { ?>
			<span class="button"><a href="<?=wiki_url('read', array('doc'=>$doc, 'hid'=>$history[$i]['id']))?>">보기</a></span>
			<span class="button"><a href="<?=wiki_url('diff', array('hid'=>$history[$i]['id']))?>">차이</a></span>			
			<a href="#history_content_layer<?=$i?>" id="show_history_<?=$i?>" style="display:none" class="wiki_modal"></a>
			<? } else { ?>
			현재 문서
			<? } ?>
			<? if($history[$i]['recover_href']) { ?>
				<span class="button"><a href="<?=$history[$i]['recover_href']?>">복원</a></span>
			<? } ?>
			<? if($history[$i]['delete_href']) { ?>
				<span class="button"><a href="<?=$history[$i]['delete_href']?>">삭제</a></span>
			<? } ?>
			<div class="history_content" id="history_content_layer<?=$i?>">
				<div >
					<div class="history_content_date"><?=$history[$i]['date']?>: <?=$history[$i]['summary']?></div>
					<div id="history_content_<?=$i?>"></div>
					<div class="wiki_tools">
						<? if($history[$i]['recover_href']) { ?> 	
							<span class="button"><a href="<?=$history[$i]['recover_href']?>">복원</a></span>
						<? } ?>
						<? if($history[$i]['delete_href']) { ?>
							<span class="button"><a href="<?=$history[$i]['delete_href']?>">삭제</a></span>
						<? } ?>					
					</div>
				</div>
			</div>		
		</td>
	</tr>
	
	<? } ?>
	
	<? if($paging) { ?>
	<tr><td colspan="<?=$colspan?>" style="padding-top:10px"><?=$paging?></td></tr>
	<? } ?>
	</tbody>
	</table>
</div>
	
<? if(!count($history)) {?>

<div style="height:100px">
	문서 이력이 존재 하지 않습니다.
</div>	
		
<?
}
?>

<div class="wiki_tools clear">

	<div class="wiki_tools_left">
		<span class="button"><a href="<?=wiki_url('read', array('doc'=>$doc))?>">문서보기</a></span>
		<span class="button"><a href="<?=wiki_url()?>">시작페이지</a></span>
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
		  $("input[name='hid[]']").attr('checked', $(this).attr('checked'));
		});		
	});
</script>
<?}?>
<script type="text/javascript">
	function show_history(idx, hid) {
		$("#history_content_"+idx).load('<?=$wiki['url']?>/exe/get.php?bo_table=<?=$bo_table?>&w=history&doc=<?=urlencode($doc)?>&hid='+hid, function() {
			$("#show_history_"+idx).trigger('click');
		});
	}	
</script>
