<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if(!$is_wiki_admin && !$is_doc_owner) 
{
	alert("권한이 없습니다.");
}
		
$wikiNS = wiki_class_load("Namespace");
$wikiConfig = wiki_class_load("Config");
$wikiArticle = wiki_class_load("Article");
$write = $wikiArticle->getArticleById($view[wr_id]);
$default_edit_level = $wikiConfig->setting[edit_level];
$folders = $wikiNS->namespaces("/", $withArticle=false);
$input_doc = wiki_input_value($docname);
$input_full = wiki_input_value($doc);
?>
<div id="wiki_doc_admin" style="display:none">
	
<h1>문서관리</h1> 

<form name="frmwikiman" onsubmit="return wiki_admin_submit(this);" method="post">
<input type="hidden" name="w" value="u">	
<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>">	
<input type="hidden" name="doc" value="<?=$input_full?>">	
<input type="hidden" name="wiki_folder_switch" id="wiki_folder_switch" value="wiki_folder_select">	
<div class="list_table">
<table id="wiki_man" cellpadding="0" cellspacing="0" border="0">
<tbody>
<tr>		
	<th scope="row">문서경로</th>
	<td>
		<?=$doc?>
	</td>
</tr>

<tr>
	<th scope="row">편집권한</th>
	<td>
		<select name="wiki_edit_level" class="tx" >
			<option value='0'>위키기본설정 따름 (<?=$default_edit_level?>)</option>
			<? for($i=1; $i<=10; $i++) {
				$selected = ($write[edit_level] == $i ? "selected" : "");
				echo "<option value='$i' $selected>$i</option>";
			} ?>
		</select>
	</td>
</tr>

<tr>
	<th scope="row">접근권한</th>
	<td>
		<select name="wiki_access_level" style="width:50px" class="tx" >
			<? for($i=1; $i<=10; $i++) {
				$selected = ($write[access_level] == $i ? "selected" : "");
				echo "<option value='$i' $selected>$i</option>";
			} ?>
		</select>
	</td>
</tr>

<tr>
	<th scope="row">폴더</th>
	<td>
		<span id="folder_select_layer">
			<select name="wiki_folder_select" id="wiki_folder_select" class="tx" >
				<? foreach($folders as $k=>$v) {
					$selected = ($ns == $v ? "selected" : "");
					echo "<option value='$v' $selected>$k</option>";
				} ?>
			</select>		
			<span class="button"><a href="#enterFolder" id="enter_folder">직접입력</a></span>
		</span>
		
		<span id="folder_input_layer">
			/Home/<input type="text" class="tx" name="wiki_folder_input_fake" style="width:200px" id="wiki_folder_input_fake"/>
			<input type="hidden" name="wiki_folder_input" id="wiki_folder_input"/>
			<span class="button"><a href="#selectFolder" id="select_folder">폴더선택</a></span>
		</span>
		
	</td>
</tr>

<tr>		
	<th scope="row">문서명</th>
	<td>
		<input type="text" name="wiki_doc" value="<?=$input_doc?>" class="tx" size="50"/>
	</td>
</tr>

<tr>
	<td></td>
	<td>
		<span class="button red"><input type="submit" value="  완 료  "/>
	</td>
</tr>
</tbody>
</table>
</div>
</form>

</div> <!--// wiki_doc_admin -->

<style type="text/css">
  #wiki_doc_admin { display:none; padding-bottom:20px;}
	#wiki_doc_admin .list_table table { width:500px; }
  #wiki_doc_admin .list_table table th { width:100px; text-align:right; padding-right:5px; }
	#wiki_man { margin-top:10px; }
	#folder_input_layer { display:none; }
</style>

<script type="text/javascript">

	$(document).ready(function() {

		$("#enter_folder").click(function() {
			$("#wiki_folder_switch").val("wiki_folder_input");
			$("#folder_input_layer").show(function() {
				$("#wiki_folder_input").focus();
			});
			$("#folder_select_layer").hide();						
		});

		$("#select_folder").click(function() {
			$("#wiki_folder_switch").val("wiki_folder_select");
			$("#folder_input_layer").hide();
			$("#folder_select_layer").show('fast');
		});
	});

	function wiki_admin_submit(f) 
	{
		
		if(!check_doc_name(f.wiki_doc.value)) return false;
		if(f.wiki_folder_switch.value == 'wiki_folder_input' && !check_folder_name("/"+f.wiki_folder_input_fake.value)) return false;
		
	  var subject = "";
	  var content = "";
	  var fname = f.wiki_folder_select.value;
	  
		if(f.wiki_folder_switch.value == 'wiki_folder_input') {
			fname = f.wiki_folder_input_fake.value;
	  }
	  
	  
	  $.ajaxSetup({async:false});
	  $.getJSON(
	  	"<?=$wiki[path]?>/exe/ajax.filter.php", 
	  	{
	  		"bo_table": "<?=$wiki[bo_table]?>",
	      "subject": fname,
	      "content": f.wiki_doc.value      
	    }, 
	    function(data) {
	    	subject = data.subject;
	    	content = data.content;
	    });
	  
	  if (subject) {
	      alert("폴더명에 금지단어('"+subject+"')가 포함되어있습니다");
	      f.wiki_folder_input_fake.focus();
	      return false;
	  }  
	  
	  if (content) {
	      alert("문서명에 금지단어('"+subject+"')가 포함되어있습니다");
	      f.wiki_doc.focus();
	      return false;
	  }    
	  
	  f.wiki_folder_input.value = "/"+f.wiki_folder_input_fake.value;
	  <?
	  if ($g4[https_url])
	      echo "f.action = '$g4[https_url]/$wiki[path]/exe/doc_update.php';";
	  else
	      echo "f.action = '$wiki[path]/exe/doc_update.php';";
	  ?>       
	  
	  return true;
	}	
</script>