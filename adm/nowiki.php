<?
/**
 * 위키 관리 : nowiki 페이지 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
$pageid = "nowiki";

include_once("_common.php");
include_once "admin.head.php";

$sql = "SELECT wr_id, wr_subject FROM {$wiki[write_table]} WHERE wr_id NOT IN ( SELECT wr_id FROM {$wiki[nsboard_table]} ) AND wr_is_comment <> 1";
$list = sql_list($sql);

$sql = "SELECT * FROM {$wiki[ns_table]} WHERE bo_table = '{$wiki[bo_table]}'";
$ns_list = sql_list($sql);
$ns_options = "";
foreach($ns_list as $idx => $ns)
{
	$v = wiki_input_value($ns[ns]);
	$ns_options .= "<option value=\"{$v}\">{$ns[ns]}</option>";
}
if(!$ns_options) $ns_options = "<option value=\"/\">/</option>";
?>


<? if(count($list) > 0) { ?>

<style type="text/css">
	.name_error { color:#ff0000; }
</style>

<div style="color:#ff0000;margin-bottom:8px">* 제목에 /, |, \ 문자를 사용할 수 없습니다.</div>

<form name="frmnowiki" onsubmit="return submit_check(this);" method="post">
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<colgroup width="20px"/>
<colgroup width=""/>
<colgroup width="40%"/>
<tr>
	<th><input type="checkbox" name="checkall"/></th>
	<th>문서명</th>
	<th>저장할 폴더</th>
</tr>
<? foreach($list as $idx => $wr) { 
		$name_error = "";
		if(!wiki_check_doc_name($wr[wr_subject])) {
			$name_error = " class='name_error'";
		}
?>
<tr>
	<td>
		<input type="checkbox" name="chk[]" class="chk" value="<?=$idx?>"/>
	</td>
	<td<?=$style?>>
		<input type="text" name="wr_subject[<?=$idx?>]" value="<?=wiki_input_value($wr[wr_subject])?>" style="width:100%;" <?=$name_error?>/>
		<input type="hidden" name="wr_id[<?=$idx?>]" value="<?=$wr[wr_id]?>"/>
	</td>	
	<td style="padding-left:5px">
		<select name="wr_folder[<?=$idx?>]" style="width:100%">
			<?=$ns_options?>
		</select>
	</td>
</tr>
<? if($should_change) { ?>
<tr>
	<td colspan="3" style="color:#800000;font-size:0.88em;padding-bottom:8px">
		
	</td>
</tr>
<? }?>
<? } ?>
<tr>
	<td colspan="2" align="center">
		<span class="button red">
			<input type="submit" value="적용"/>
		</span>
	</td>
</table>
</form>
<script type="text/javascript">
$(document).ready(function() {
	$('input[name="checkall"]').click(function() {
	  $(".chk").attr('checked', $(this).attr('checked'));
	});
});
function submit_check(f)
{
  var chk = document.getElementsByName("chk[]");
  var bchk = false;
	var hasError = false;
	var firstError = null;
  for (i=0; i<chk.length; i++)
  {     
      if (chk[i].checked) {
				ele = $("input[name=wr_subject["+i+"]]");
				ele.removeClass("name_error");
      	bchk = true;      	
	      if(!check_doc_name(ele.val(), true)) {
	      	if(firstError == null) firstError = ele;
	      	ele.addClass("name_error");
	      	hasError = true;      
	      }
	    }
  }
  
  if(hasError) {
  	check_doc_name(firstError.val());
  	return false;
  }
  
  if (!bchk) 
  {
      alert("문서를 하나 이상 선택하세요.");
      return false;
  }
  
  <?
  if ($g4[https_url])
      echo "f.action='$g4[https_url]/$wiki[path]/adm/exe_nowiki.php'";
  else
  		echo "f.action='$wiki[path]/adm/exe_nowiki.php'";
  ?>   
    
  return true;
}	
</script>
<? } else { ?>
	
	<div style="">
		없습니다.
	</div>

<?}?>

<?
include_once "admin.tail.php";
?>
