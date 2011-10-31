<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$options[new_day] = ($options[new_day] ? $options[new_day] : 2);
?>
<table width=100% border="0" cellpadding="0" cellspacing="0">
<? foreach($list as $k=>$v) { 
	$doc = ($v[ns] == "/" ? "" : $v[ns]) ."/". $v[docname]; 
	$days = abs(ceil((strtotime($v[reg_date])-strtotime("now"))/86400));
	if($days < $options[new_day]) $style = ";font-weight:bold";
	else $style = "";
?>				
	<tr>
		<td height=20>
			<a href="<?=$v[href]?>" title="<?=addslashes($doc)?>" style="<?=$style?>"><?=$v[docname]?></a>
			<span style="font-size:8pt;color:#888;"><?=$v[ns]?></span>
		</td>
		<td style="width:50px;padding-right:23px;color:#888">
			<div style="width:50px;overflow:hidden;white-space:nowrap;"><?=$v[mb_nick]?></div>
		</td>
		<td style="width:140px;color:#888"><?=$v[reg_date]?></td>
	</tr>
<? }?>
</table>