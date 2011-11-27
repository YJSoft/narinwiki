<?
/**
 * 위키 관리 : 썸네일 페이지 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
$pageid = "thumb";

include_once("_common.php");
include_once "admin.head.php";

$filecount = 0;
?>
<style type="text/css">
	#admbasic th { text-align:right; width:150px; padding-right:10px; }
	#admbasic td { padding-left:5px; }
</style>
<form name="frmadm" onsubmit="return check_form(this);" action="<?=$wiki['path']?>/adm/exe_thumbnail.php" method="post">
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>

<div class="list_table">
	
<table id="admbasic" cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<th scope="row">생성된 썸네일 총 크기</th>
		<td>       
			<?=wiki_file_size(filesize_r($wiki['path'].'/data/'.$bo_table.'/thumb'))?>
		</td>
	</tr>
	<tr>
		<th scope="row">생성된 썸네일 총 개수</th>
		<td>       
			<?=$filecount?> 파일
		</td>
	</tr>
		
	<tr>
		<td>&nbsp;</td>
		<td>
		<span class="button red"><input type="submit" value="썸네일 초기화"/></span>		
		</td>
	</tr>
</tbody>
</table>
</div>
</form>

<script type="text/javascript">
	function check_form(f) {
		if(!confirm('썸네일을 초기화 하시겠습니까?\n생성된 문서 캐시도 모두 삭제됩니다.')) return false;
		return true;
	}
</script>
<?
include_once "admin.tail.php";

function filesize_r($path){
	global $filecount;
  if(!file_exists($path)) return 0;
  if(is_file($path)) {
  	$filecount++;
  	return filesize($path);
  }
  $ret = 0;
  foreach(glob($path."/*") as $fn)
    $ret += filesize_r($fn);
  return $ret;
}
?>
