<?
/**
 * 
 * 위키 관리 : 미디어 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "media";

include_once("_common.php");
include_once "admin.head.php";

$wikiConfig = wiki_class_load("Config");
$setting = $wikiConfig->media_setting;
$allow_extensions = $setting['allow_extensions'];
$max_file_size = $setting['max_file_size'];
$small_width = $setting['small_size'];
$medium_width = $setting['medium_size'];
$large_width = $setting['large_size'];
?>
<style type="text/css">
	#admbasic th { text-align:right; width:150px; padding-right:10px; }
	#admbasic td { padding-left:5px; }
	.desc { color:#888; font-size:90%;text-align:left;font-weight:normal; margin-top:8px;}	
</style>
<form name="frmadm" onsubmit="return check_form(this);" action="<?=$wiki['path']?>/adm/exe_media.php" method="post">
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>

<div class="list_table">
	
<table id="admbasic" cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<th scope="row">업로드 가능 확장자</th>
		<td>       
			<textarea name="media_setting[allow_extensions]"  style="width:100%;height:100px"><?=$allow_extensions?></textarea>  
			<br/>* 콤마(,) 구분으로 확장자를 입력하세요. 입력하지 않을 시 모든 파일 업로드 가능합니다.
		</td>
	</tr>
	
	<tr>
		<th>최대 업로드 파일 크기</th>
		<td>
			<input type="text" name="media_setting[max_file_size]" id="mfs" required itemname="최대 업로드 파일 크기" value="<?=$max_file_size?>"/>
			* 파일크기와 크기 단위(mb, gb)를 같이 입력하세요.
		</td>
	</tr>
	
	<tr>
		<th>
			이미지 크기 (너비)
		</th>
		<td>
			<span> 작은 이미지 : <input type="text" name="media_setting[small_size]" size="5" id="ss" required itemname="작은 이미지" value="<?=$small_width?>"/>
			중간 이미지 : <input type="text" name="media_setting[medium_size]" size="5" id="ms" required itemname="중간 이미지" value="<?=$medium_width?>"/>
			큰 이미지 : <input type="text" name="media_setting[large_size]" size="5" id="ls" required itemname="큰 이미지" value="<?=$large_width?>"/> </span>
			<div class="desc">
			미디어관리자에서 이미지를 에디터에 삽입할 때 선택하는 이미지 크기입니다. width 값만 입력하며 height 는 자동 계산되어 추가됩니다. (단위:px)
			</div>			
		</td>
	</tr>	
	
	<tr>
		<td>&nbsp;</td>
		<td>
		<span class="button red"><input type="submit" value="확인"/></span>		
		</td>
	</tr>
</tbody>
</table>
</div>
</form>

<script type="text/javascript">
	function check_form(f) {
		var filter = /(gb|mb)$/i;
		var mfs = $("#mfs").val();
		if(!mfs) { alert('최대 업로드 파일 크기를 입력하세요'); return false; }
		if(!filter.test(mfs)) { alert('크기와 단위를 같이 입력하세요. 예> 100mb, 1gb'); return false; }
		return true;
	}
</script>
<?
include_once "admin.tail.php";
?>
