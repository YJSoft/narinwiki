<?
/**
 * 
 * 위키 관리 : index 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "front";

include_once("_common.php");
include_once "admin.head.php";
?>
<style type="text/css">
	.list_table a { color:blue; }
	.list_table th, 
	.list_table td { text-align:left; }
</style>

<div class="list_table">
<table id="" cellspacing="0" width="100%" cellpadding="0" border="1" >
<colgroup>
	<col width="160px"/>
	<col/>
</colgroup>
<tr>
	<th>관리</th>
	<th>설명</th>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/basic.php?bo_table=<?=$bo_table?>">기본설정</a></td>
	<td>스킨, 시작페이지, 상-하단 파일, 권한 등의 위키 기본 설정을 합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/media.php?bo_table=<?=$bo_table?>">미디어관리자</a></td>
	<td>미디어관리자의 기능 설정을 합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/plugin.php?bo_table=<?=$bo_table?>">플러그인</a></td>
	<td>플러그인 사용여부, 설정, 설치, 제거 등의 플러그인 설정을 합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/history.php?bo_table=<?=$bo_table?>">문서이력/변경내역</a></td>
	<td>삭제된 문서의 문서이력, 문서이력을 관리합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/cache.php?bo_table=<?=$bo_table?>">캐시/썸네일</a></td>
	<td>캐시(Cache)와 썸네일(Thumbnail)을 관리합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/nowiki.php?bo_table=<?=$bo_table?>">미등록문서</a></td>
	<td>그누보드 게시판에는 등록되어있으나 위키 문서로 등록되지 않은 게시물들을 위키에 등록합니다.</td>
</tr>
</table>

<?
include_once "admin.tail.php";
?>
