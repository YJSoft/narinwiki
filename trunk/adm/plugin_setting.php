<?
$pageid = "plugin";

include_once("_common.php");
include_once "admin.head.php";

$info = wiki_plugin_info($plugin);
if(!$info) {
	alert("플러그인 로드 오류");
}

$setting = $info->getPluginSetting();
if(!$info->checkSetting($setting)) alert("플러그인 설정 오류");

?>
<style type="text/css">
.txt { width:50%; border:1px solid #ccc; }
.desc { margin-left:10px; font-size:8pt; color:#888; }
</style>
<h1>플러그인 셋팅</h1>

<form name="frmplugin" onsubmit="return check_submit(this);" action="<?=$wiki[path]?>/adm/exe_plugin_setting.php" method="post">
<input type="hidden" name="plugin" value="<?=$plugin?>"/>
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>
<div class="list_table">
<table id="" cellspacing="0" width="100%" cellpadding="0" border="1" >
<colgroup width="150px"/>
<colgroup width=""/>
<tbody>	
<? foreach($setting as $name => $attr) { ?>
	<tr>
		<th scope="row" style="text-align:right;padding-right:5px"><?=$attr[label]?></th>
		<td>
			<? if($attr[type] == "text") { ?>
			<input type="text" class="txt" name="setting[<?=$name?>]" value="<?=$attr[value]?>">
			<? } if($attr[type] == "textarea") { ?>
			<textarea name="setting[<?=$name?>]" class="txt" style="width:100%; height:200px"><?=$attr[value]?></textarea>
			<? } if($attr[type] == "select") {?>
				<select name="setting[<?=$name?>]">
					<? foreach($attr[options] as $k => $v) { ?>
					<option value="<?=$v?>" <?=($v==$attr[value] ? "selected" : "")?>><?=$v?></option>
					<?}?>
				</select>
			<? } if($attr[type] == "checkbox") {?>
				<input type="hidden" name="setting[<?=$name?>]">
				<input type="checkbox" name="<?=$name?>" <?=($attr[value] ? "checked" : "")?> class="chk" value="1">
			<? } ?>
			<span class="desc"><?=$attr[desc]?></span>
		</td>
	</tr>
<? } ?>
<tr>
	<td></td>
	<td>
		<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
		<span class="button red"><input type="submit" value="완료"></span>
	</td>
</tr>
</tbody>
</table>
</div>
</form>

<script type="text/javascript">
	function check_submit(f) {
		$(".chk").each(function() {
			if($(this).is(":checked")) {
				$("input[name='setting["+$(this).attr('name')+"]']").val(1);
			} else $("input[name='setting["+$(this).attr('name')+"]']").val(0);
		});
		return true;
	}
</script>
<?
include_once "admin.tail.php";
?>
