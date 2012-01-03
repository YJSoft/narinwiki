<?
/**
 * 
 * 위키 관리 : 메인 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "basic";

include_once("_common.php");
include_once "admin.head.php";

$wikiConfig =& wiki_class_load("Config");
$setting = $wikiConfig->setting;
$skin = $setting['skin'];
$editLevel = $setting['edit_level'];
$historyLevel = $setting['history_access_level'];
$folderViewLevel = $setting['folder_view_level'];

?>
<style type="text/css">
	#admbasic th { text-align:right; width:150px; padding-right:10px; }
	#admbasic td { padding-left:5px; }
</style>
<form name="frmadm" onsubmit="return submit_check(this);" action="<?=$wiki['url']?>/adm/exe_basic.php" method="post">
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>

<div class="list_table">
	
<table id="admbasic" cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<th scope="row">스킨</th>
		<td>       
			<select name="setting[skin]" required itemname="스킨 디렉토리">  
			<?
			$arr = wiki_get_skins("board");
			for ($i=0; $i<count($arr); $i++) {
				$selected = ($arr[$i] == $skin ? "selected" : "");
				echo "<option value='$arr[$i]' $selected>$arr[$i]</option>\n";
			}
			?>
			</select>
		</td>
	</tr>
	
	<tr>
		<th>시작페이지</th>
		<td>
			<input type="text" name="wiki_front" size="40" required itemname="시작페이지명" value="<?=$board[bo_subject]?>"/>
			<input type="checkbox" name="wiki_front_apply_exist_doc" id="wfaed" value="1">
			<label for="wfaed">기존 시작페이지 이름도 같이 변경</label>
		</td>
	</tr>

	<tr>
		<th>상단파일경로</th>
		<td>
			<input type="text" name="setting[head_file]" size="40" itemname="상단파일" value="<?=$setting[head_file]?>"/>
		</td>
	</tr>

	<tr>
		<th>하단파일경로</th>
		<td>
			<input type="text" name="setting[tail_file]" size="40" itemname="하단파일" value="<?=$setting[tail_file]?>"/>
		</td>
	</tr>
	
	<tr>
		<th>문서 편집 권한</th>
		<td>
			<select name="setting[edit_level]" style="width:50px" class="tx" >
				<? for($i=1; $i<=10; $i++) {
					$selected = ($editLevel == $i ? "selected" : "");
					echo "<option value='$i' $selected>$i</option>";
				} ?>
			</select>
		</td>
	</tr>	
	
	<tr>
		<th>문서이력 보기 권한</th>
		<td>
			<select name="setting[history_access_level]" style="width:50px" class="tx" >
				<? for($i=1; $i<=10; $i++) {
					$selected = ($historyLevel == $i ? "selected" : "");
					echo "<option value='$i' $selected>$i</option>";
				} ?>
			</select>
		</td>
	</tr>
	
	<tr>
		<th>폴더 보기 권한</th>
		<td>
			<select name="setting[folder_view_level]" style="width:50px" class="tx" >
				<? for($i=1; $i<=10; $i++) {
					$selected = ($folderViewLevel == $i ? "selected" : "");
					echo "<option value='$i' $selected>$i</option>";
				} ?>
			</select>
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
	function submit_check(f)
	{
		
	  var subject = "";  
	  $.ajaxSetup({async:false});
	  $.getJSON(
	  	"<?=$wiki['url']?>/exe/ajax.filter.php", 
	  	{
	  		"bo_table": "<?=$wiki[bo_table]?>",
	      "subject": f.wiki_front.value    
	    }, 
	    function(data) {
	    	subject = data.subject;
	    	content = data.content;
	    });
	  
	  if (subject) {
	      alert("시작페이지명에 금지단어('"+subject+"')가 포함되어있습니다");
	      f.wiki_front.focus();
	      return false;
	  }  

  	return true;
	}
</script>
<?
include_once "admin.tail.php";
?>
