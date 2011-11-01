<?
/**
 * include 폴더 관리 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if(!$is_wiki_admin ) {	alert("권한이 없습니다."); }

$wikiNS = wiki_class_load("Namespace");
$ns = $wikiNS->get($loc);

?>

<div id="wiki_folder_admin" style="display:none">
	
	<h1>폴더관리</h1> 
	
	<form name="frmwikiman" onsubmit="return wiki_submit(this);" method="post">
	<input type="hidden" name="w" value="u">	
	<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>">	
	<input type="hidden" name="loc" value="<?=wiki_input_value($loc)?>">	
	<div class="list_table">
	<table id="wiki_man" cellpadding="0" cellspacing="0" border="0">	
	<tr>		
		<th scope="row">폴더경로</th>
		<td>
			<?=$loc?>
		</td>
	</tr>
		
	<tr>
		<th scope="row">접근권한</th>
		<td>
			<select name="wiki_access_level" style="width:50px" class="tx" >
				<? for($i=1; $i<=10; $i++) {
					$selected = ($ns[ns_access_level] == $i ? "selected" : "");
					echo "<option value='$i' $selected>$i</option>";
				} ?>
			</select>
		</td>
	</tr>
	
	<tr>
		<th scope="row">템플릿</th>
		<td>
		<textarea name="wiki_template" style="width:100%;height:120px" class="tx"><?=$ns[tpl]?></textarea>
		<table id="wiki_template_desc">
			<tr>
				<td>@DOCNAME@ : 문서명</td>
				<td>@FOLDER@ : 폴더경로</td>
			</tr>
			<tr>
				<td>@USER@ : 사용자 아이디</td>
				<td>@NAME@ : 사용자 이름</td>
			</tr>
			<tr>
				<td>@NICK@ : 사용자 별명</td>
				<td>@MAIL@ : 사용자 이메일</td>
			</tr>		
			<tr>
				<td>@DATE@ : 문서생성 시간</td>
				<td></td>
			</tr>
		</table>				
		
		</td>
	</tr>	
	
	<tr>		
		<th scope="row">폴더명 변경</th>
		<td>
			<? if($ns[ns] == "/") { ?>
			<input type="hidden" name="wiki_loc" value="<?=wiki_input_value($ns[ns])?>"/>
			<?=$ns[ns]?> &nbsp; (최상위 폴더는 변경할 수 없습니다)
			<? } else { ?>
			<input type="text" name="wiki_loc" value="<?=wiki_input_value($ns[ns])?>" size="50" class="tx"/>
			<? } ?>
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td>
			<span class="button red"><input type="submit" value="  완 료  "/>
		</td>
	</tr>
	
	</table>
	</div>
	</form>
</div> <!--// wiki_folder_admin -->

<style type="text/css">
	#wiki_folder_admin { display:none; }
	#wiki_man { margin-top:10px; }
	#wiki_folder_admin .list_table table { width:500px; }
	#wiki_folder_admin .list_table table th { width:80px; text-align:right; padding-right:8px;}
	#wiki_folder_admin .list_table table #wiki_template_desc { border-top:1px; margin-top:5px; width:100%;}
	#folder_input_layer { display:none; }
</style>

<script type="text/javascript">
function wiki_submit(f) 
{
	if(!check_folder_name(f.wiki_loc.value)) return false;

  var subject = "";
  $.ajaxSetup({async:false});
  $.getJSON(
  	"<?=$wiki[path]?>/exe/ajax.filter.php", 
  	{
  		"bo_table": "<?=$wiki[bo_table]?>",
      "subject": f.wiki_loc.value
    }, 
    function(data) {
    	subject = data.subject;
    }
  );
  
  if (subject) {
      alert("폴더명에 금지단어('"+subject+"')가 포함되어있습니다");
      f.wiki_loc.focus();
      return false;
  }
  <?
  if ($g4[https_url])
      echo "f.action = '$g4[https_url]/$wiki[path]/exe/folder_update.php';";
  else
      echo "f.action = '$wiki[path]/exe/folder_update.php';";
  ?>       
  
  return true;
}	
</script>
