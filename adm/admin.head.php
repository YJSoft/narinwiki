<?
/**
 * 
 * 위키 관리 head 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
$g4['title'] = "나린위키 관리";
include_once "../head.php";
$selected[$pageid] = " class='selected'";
?>
<h1>나린위키 관리</h1>

<div class="wiki_tab"> 
	<ul class="clear"> 
		<li<?=$selected['front']?>><a href="<?=$wiki['url']?>/adm/">위키관리</a></li>	
		<li<?=$selected['basic']?>><a href="<?=$wiki['url']?>/adm/basic.php">기본설정</a></li> 
		<li<?=$selected['media']?>><a href="<?=$wiki['url']?>/adm/media.php">미디어관리자</a></li> 
		<li<?=$selected['plugin']?>><a href="<?=$wiki['url']?>/adm/plugin.php">플러그인</a></li> 
		<li<?=$selected['history']?>><a href="<?=$wiki['url']?>/adm/history.php">문서이력/변경내역</a></li> 
		<li<?=$selected['cache']?>><a href="<?=$wiki['url']?>/adm/cache.php">캐시/썸네일</a></li> 
		<li<?=$selected['nowiki']?>><a href="<?=$wiki['url']?>/adm/nowiki.php">미등록문서</a></li> 		
	</ul> 
</div> 

<div id="wiki_admin">
