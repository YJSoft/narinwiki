<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once "../head.php";
$selected[$pageid] = " class='selected'";
?>
<h1>위키 관리</h1>

<div class="wiki_tab"> 
	<ul class="clear"> 
		<li<?=$selected[home]?>><a href="<?=$wiki[path]?>/adm/index.php?bo_table=<?=$wiki[bo_table]?>">기본설정</a></li> 
		<li<?=$selected[plugin]?>><a href="<?=$wiki[path]?>/adm/plugin.php?bo_table=<?=$wiki[bo_table]?>">플러그인 설정</a></li> 
		<li<?=$selected[nowiki]?>><a href="<?=$wiki[path]?>/adm/nowiki.php?bo_table=<?=$wiki[bo_table]?>">미등록문서</a></li> 		
	</ul> 
</div> 

<div id="wiki_admin">
