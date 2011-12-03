<?
/**
 * 
 * 위키 관리 head 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once "../head.php";
$selected[$pageid] = " class='selected'";
?>
<h1>나린위키 관리</h1>

<div class="wiki_tab"> 
	<ul class="clear"> 
		<li<?=$selected['front']?>><a href="<?=$wiki['path']?>/adm/index.php?bo_table=<?=$wiki['bo_table']?>">위키관리</a></li>	
		<li<?=$selected['basic']?>><a href="<?=$wiki['path']?>/adm/basic.php?bo_table=<?=$wiki['bo_table']?>">기본설정</a></li> 
		<li<?=$selected['media']?>><a href="<?=$wiki['path']?>/adm/media.php?bo_table=<?=$wiki['bo_table']?>">미디어관리자</a></li> 
		<li<?=$selected['plugin']?>><a href="<?=$wiki['path']?>/adm/plugin.php?bo_table=<?=$wiki['bo_table']?>">플러그인</a></li> 
		<li<?=$selected['history']?>><a href="<?=$wiki['path']?>/adm/history.php?bo_table=<?=$wiki['bo_table']?>">문서이력/변경내역</a></li> 
		<li<?=$selected['cache']?>><a href="<?=$wiki['path']?>/adm/cache.php?bo_table=<?=$wiki['bo_table']?>">캐시/썸네일</a></li> 
		<li<?=$selected['nowiki']?>><a href="<?=$wiki['path']?>/adm/nowiki.php?bo_table=<?=$wiki['bo_table']?>">미등록문서</a></li> 		
	</ul> 
</div> 

<div id="wiki_admin">
