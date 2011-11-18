<?
/**
 * 위키 관리 : command 페이지 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
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

위키 관리 페이지입니다. <br/>
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
	<td><a href="<?=$wiki['path']?>/adm/plugin.php?bo_table=<?=$bo_table?>">플러그인 설정</a></td>
	<td>플러그인 사용여부, 설정, 설치, 제거 등의 플러그인 설정을 합니다.</td>
</tr>
<tr>
	<td><a href="#cache_clear" id="cache_clear">캐시(Cache) 초기화 </a></td>
	<td>저장된 모든 캐시(Cache)를 삭제하고, 위키 문서가 열릴 때 다시 생성되도록 설정합니다.</td>
</tr>
<tr>
	<td><a href="<?=$wiki['path']?>/adm/nowiki.php?bo_table=<?=$bo_table?>">미등록문서</a></td>
	<td>그누보드 게시판에는 등록되어있으나 위키 문서로 등록되지 않은 게시물들을 위키에 등록합니다.</td>
</tr>
</table>

<script type="text/javascript">
$("#cache_clear").click(function(evt) {
	evt.preventDefault();
	if(confirm('캐시를 초기화 하겠습니까?')) {
		$.post('<?=$wiki['path']?>/adm/exe_index.php?bo_table=<?=$bo_table?>&md=cache_clear', function(data) {
			if(data == '1') alert('초기화 완료');
			else alert('초기화 실패\n'+data);
		});
	}
});

</script>
<?
include_once "admin.tail.php";
?>
