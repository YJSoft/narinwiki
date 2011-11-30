<?
/**
 * 
 * 위키 관리 : 문서이력 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "history";

include_once("_common.php");
include_once "admin.head.php";

$wikiHistory = wiki_class_load("History");
$unlinkedList = $wikiHistory->unlinkedHistory();
?>
<style type="text/css">
	#admbasic th { text-align:right; width:150px; padding-right:10px; }
	#admbasic td { padding-left:5px; }
	.list_table a { color:blue; }
	.list_table input { border:1px solid #ccc; }
	.desc { color:#888; font-size:90%;text-align:left;font-weight:normal; margin-top:8px;}
</style>
<form name="frmadm" onsubmit="return check_form(this);" action="<?=$wiki['path']?>/adm/exe_media.php" method="post">
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>

<div class="list_table">
	
<table id="admbasic" cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<th scope="row">
			삭제된 문서의 문서이력
			<div class="desc">
			(문서가 삭제되어도 문서이력은 남습니다. 문서가 삭제되어도 추후 같은 이름의 새 문서를 만들고 저장된 문서이력으로 복원할 수 있습니다.)
			</div>
		</th>
		<td>       
			
			<table style="border:0" cellspacing="0" cellpadding="0" border="0">
				<colgroup>
				<col><col width="50px">
				</colgroup>
				<? foreach($unlinkedList as $k=>$v) {
				echo '<tr><td>'.$v['doc'] .'</td><td><span class="button"><a href="javascript:del_unlinked(\''.addslashes($v['doc']).'\');">삭제</a></span></td></tr>';
				} ?>
			</table>
			<? if(count($unlinkedList)) { ?>
			<div style="text-align:right;padding:10px">
				<span class="button red"><a href="javascript:clear_unlinked();">모든 삭제된 문서의 문서이력 삭제</a></span>
			</div>
			<? } else { ?>
			데이터가 없습니다.
			<? } ?>
		</td>
	</tr>

	<tr>
		<th scope="row">
			문서이력 정리
		</th>
		<td>       
			<div>
				<select id="history_expire_day">
					<option value="0">선택</option>
					<option value="7">1주일</option>
					<option value="30">1달</option>
					<option value="90">3달</option>
					<option value="180">6개월</option>
					<option value="365">1년</option>
					<option value="730">2년</option>
					<option value="1095">3년</option>
				</select> 이전의 문서이력을 <a href="javascript:del_expired_history_by_day();">모두 삭제</a>합니다.
			</div>
		</td>
	</tr>
	
</tbody>
</table>
</div>
</form>

<script type="text/javascript">
	function del_unlinked(doc) {
		if(!confirm('삭제하시겠습니까?')) return;
		location.href = 'exe_history.php?bo_table='+g4_bo_table+'&md=del_unlinked&doc='+encodeURIComponent(doc);
	}
	function clear_unlinked() {
		if(!confirm('삭제하시겠습니까?')) return;
		location.href = 'exe_history.php?bo_table='+g4_bo_table+'&md=clear_unlinked';
	}	
	function del_expired_history_by_day() {		
		var ex = $("#history_expire_day").val();
		if(ex == 0) { alert('값을 선택하세요'); return; }
		if(!confirm('정리하시겠습니까?')) return;
		location.href = 'exe_history.php?bo_table='+g4_bo_table+'&md=ehbd&expire='+ex;
	}

</script>
<?
include_once "admin.tail.php";
?>
