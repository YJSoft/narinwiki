<?
/**
 *
 * 위키 관리 : plugin 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "plugin";

include_once("_common.php");
include_once "admin.head.php";
$wikiPlugin =& wiki_class_load("Plugin");
$wikiConfig =& wiki_class_load("Config");
$use_plugins = array();
foreach($wikiConfig->using_plugins as $v) $use_plugins[$v] = $v;
$plugins = wiki_plugin_load();
?>

<style type="text/css">
#plist {
	
}

#plist th {
	padding: 5px;
	background-color: #ccc;
}
</style>

<form name="frmplugin" onsubmit="return submit_check(this);"
	method="post"><input type="hidden" name="bo_table"
	value="<?=$wiki['bo_table']?>" />

<div class="list_table">
<table id="" cellspacing="0" width="100%" cellpadding="0" border="1">
	<tbody>
		<colgroup width="200px" />
		<colgroup width="80px" />
		<colgroup width="" />
		<colgroup width="120px" />
		<tr>
			<th scope="col">플러그인</th>
			<th scope="col">사용여부</th>
			<th scope="col">설명</th>
			<th scope="col">설정</th>
		</tr>
		<? for($i=0; $i<count($plugins); $i++) {
			$p = $plugins[$i];
			$info = $p['info'];
			$use = $use_plugins[$p['name']];
			$selected_0 = $selected_1 = "";
			if($use) {
				$selected_1 = "selected";
			} else {
				$selected_0 = "selected";
			}
			?>
		<tr>
			<td><?=$p['name']?> <? if(!$is_default) { ?> <input type="hidden"
				name="wiki_plugin[]" value="<?=$p['name']?>"> <?}?></td>
			<td><? if($is_default) { ?> 사용 <?} else { ?> <select
				name="wiki_plugin_use[]">
				<option value="0" <?=$selected_0?>>사용안함</option>
				<option value="1" <?=$selected_1?>>사용</option>
			</select> <? } ?></td>
			<td><?=$info->description()?></td>
			<td><? if($info->shouldInstall()) { ?> <span class="button"><a
				href="<?=$wiki['path']?>/adm/plugin_install.php?bo_table=<?=$bo_table?>&w=install&plugin=<?=$p['name']?>">설치</a></span>
				<? } else if($info->getSetting()) { ?> <span class="button"><a
				href="<?=$wiki['path']?>/adm/plugin_setting.php?bo_table=<?=$bo_table?>&plugin=<?=$p['name']?>">설정</a></span>
				<? if($info->shouldUnInstall()) { ?> <span class="button"><a
				href="<?=$wiki['path']?>/adm/plugin_install.php?bo_table=<?=$bo_table?>&w=uninstall&plugin=<?=$p['name']?>">제거</a></span>
				<?}?> <? } ?> &nbsp;</td>
		</tr>

		<? } ?>
	</tbody>
</table>
<div style="text-align: center; margin-top: 10px;"><span
	class="button red"><input type="submit" value="적용" /></span></div>
</div>
</form>

<script type="text/javascript">

function submit_check(f)
{
  
  <?
  if ($g4['https_url'])
	echo "f.action='".$g4['https_url']."/".$wiki['path']."/adm/exe_plugin.php'";
  else
	echo "f.action='".$wiki['path']."/adm/exe_plugin.php'";
  ?>   
    
  return true;
}	
</script>


  <?
  include_once "admin.tail.php";
  ?>
