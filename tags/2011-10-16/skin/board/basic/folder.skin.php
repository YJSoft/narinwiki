<?
if (!defined("_GNUBOARD_")) exit; //개별 페이지 접근 불가 
/**********************************************
사용 가능한 변수

$wiki[path] : 위키 루트 폴더 경로
$wiki[skin_path] : 스킨 경로
$wiki 변수는 narin.config.php 파일 참고

$folder[navi] : 상단 네비게이션 문자열 e.g. Home > byfun > com > gnuboard
$folder[loc] : 파라미터로 넘어온 위치 문자열 e.g. /byfun/com/gnuboard
$folder[up] : 상위 폴더 e.g. /byfun/com

$create_doc_href : 문서 생성 링크
$folder_manage_href : 문서 관리 링크
$wiki_admin_href : 위키 관리 링크

$folder_list : 폴더 내의 파일 목록 
   - 형식 
        array( 
              array("path"=>"/byfun/com/gnuboard/plugin", "name"=>"plugin", "href"=>"folder.php?loc=/byfun/com/gnuboard/plugin", "type"=>"folder"),
              array("path"=>"/byfun/com/gnuboard/플러그인제작방법", "name"=>"플러그인제작방법",, "href"=>"index.php?doc=/byfun/com/gnuboard/플러그인제작방법" "type"=>"doc"),
              array("path"=>"/byfun/com/gnuboard/도움말", "name"=>"도움말",  "href"=>"index.php?doc=/byfun/com/gnuboard/도움말", "type"=>"doc")
  			)
********************************************/
$colspan = 1;
if($is_wiki_admin) $colspan++;
?>

<div id="wiki_title_bar">
	
	<span id="wiki_title">[[<?=$folder[loc]?>]]</span>
	
	<div class="wiki_tools clear">
		
		<div class="wiki_tools_left">
	  	
	  	<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
			  	
		</div> <!--// wiki_tools_left -->
		
		<div class="wiki_tools_right">
		
			<form action="<?=$wiki[path]?>/search.php" onsubmit="return wiki_search(this);" method="get" class="wiki_form">
			<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>"/>
			<input type="text" class="search_text txt" name="stx" size="20"/>
			<span class="button purple"><input type="submit" value="검색"></span>
			</form>		
				
		</div> <!--// wiki_tools_right -->
		
		<div style="float:right;margin-right:5px;">
			<span class='button'><a href='<?=$recent_href?>'>최근 변경내역</a></span>		
		</div>		
		
	</div> <!--// wiki_tools -->
	
	<div class="wiki_navigation">
		<?=$folder[navi]?>
	</div> <!--// wiki_navigation -->
	

</div> <!--// wiki_title_bar -->

<div id="wiki_contents">
	<? if($is_wiki_admin) { ?>
	<input type="checkbox" name="checkall">
	<? } ?>
	<form name="frmflist" method="post">
	<input type="hidden" name="bo_table" value="<?=$wiki[bo_table]?>">
	<input type="hidden" name="folder" value="<?=wiki_input_value($folder[loc])?>">
	<input type="hidden" name="move_to_folder">
	<table id="folder_list" cellspacing="0" cellpadding="0" border="0">
	<? 
	// 최상위 폴더가 아니면... 상위 폴더 이동 링크 보여줌
	if($loc != "/") {?>
		<tr>
			<? if($is_wiki_admin) echo "<td></td>"; ?>
			<td class="flist folder_up">
				<a href="<?=$wiki[path]?>/folder.php?bo_table=<?=$wiki[bo_table]?>&loc=<?=$folder[up]?>">..</a>
			</td>
		</tr>	
		<tr><td colspan="<?=$colspan?>" height="1px" bgcolor="#ececec"></td></tr>	
	<?}
	
	for($i=0; $i<count($folder_list); $i++) {	?>
		<tr>
			<? if($is_wiki_admin) {				
				if($folder_list[$i][type] == 'doc') {?>
					<td width="20px">
						<input type="checkbox" name="chk_wr_id[]" class="chk" value="<?=$folder_list[$i][wr_id]?>" style="margin-top:3px"/>
					</td>
				<?}
				else echo '<td></td>';
			}
			?>
			<td class="flist <?=$folder_list[$i][type]?>">
				<a href="<?=$folder_list[$i][href]?>"><?=$folder_list[$i][name]?></a>
			</td>		
		</tr>
		<tr><td colspan="<?=$colspan?>" height="1px" bgcolor="#ececec"></td></tr>
		<? } ?>
	</table>
	
	<div style="display:none;">
		<a href="#move_folder_layer" class="wiki_modal" id="a_move_folder">문서이동</a>
		<div id="move_folder_layer" style="width:400px;height:100px;">
			<div style="background-color:#3B3B3B; color:#fff; padding:5px;">
			문서 이동
			</div>
			<div style="padding:10px;">
				폴더 :
				<select name="move_folder" id="move_folder">
					<? for($i=0; $i<count($all_folders); $i++) {
						echo "<option value=\"".wiki_input_value($all_folders[$i][path])."\">{$all_folders[$i][display]}</option>";
					}?>
				</select>
			</div>
			<div style="margin-top:10px;padding:5px;border-top:1px dotted #ccc;">
				<span class="button"><a href="javascript:closeDialog();">닫기</a></span>
				<span class="button black"><a href="javascript:select_move_do();">이동</a></span>
			</div>
		</div>
	</div>
	
	</form>
</div> <!--// wiki_contents -->

<? 
if($is_wiki_admin) {
	include_once $wiki[path]."/inc/inc.folder.manager.php";
} 
?>


<div class="wiki_tools clear" style="margin-top:10px;">
	
	<div class="wiki_tools_left">
		<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
		<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
	</div> <!--// wiki_tools_left -->

	<div class="wiki_tools_right">
		
		<? if($create_doc_href) { ?>
		<span class="button"><a href="<?=$create_doc_href?>">문서생성</a></span>
		<? }?>
		
		<? if($folder_manage_href) { ?>
		<span class='button'><a href="#folderadmin" id="show_folderadmin">폴더 관리</a></span>
		<? } ?>
		<? if($is_wiki_admin) { ?>
		<span class="button"><a href="javascript:select_delete();">문서삭제</a></span>
		<span class="button"><a href="javascript:select_move();">문서이동</a></span>
		<? }?>
		<? if($wiki_admin_href) {?>
			<span class='button'><a href='<?=$wiki_admin_href?>'>위키 관리</a></span>
		<? } ?>				
	</div> <!--// wiki_tools_right -->
		
</div>



<script type="text/javascript">
	$(document).ready(function() {
		$('input[name="checkall"]').click(function() {
		  $(".chk").attr('checked', $(this).attr('checked'));
		});		
	});

	$("#show_folderadmin").click(function(evt) {
		evt.preventDefault();
		if($("#wiki_folder_admin").is(":visible")) {
			$(this).text('폴더 관리');
			$("#wiki_folder_admin").hide();
			$("#wiki_contents").show();
		} else {
			$(this).text('폴더 보기');
			$("#wiki_folder_admin").show();
			$("#wiki_contents").hide();
		}		
	});


function check_select()
{
	var f = document.frmflist;
  var chk = document.getElementsByName("chk_wr_id[]");
  var bchk = false;
  for (i=0; i<chk.length; i++)
  {     
      if (chk[i].checked) {
      	bchk = true;      	
      	break;
	    }
  }
    
  if (!bchk) 
  {
      alert("문서를 하나 이상 선택하세요.");
      return false;
  }	
  
  return true;
  
}

// 선택한 게시물 삭제
function select_delete() {
	
	if(!check_select()) return;
	var f = document.frmflist;
	if (!confirm("선택한 문서를 정말 삭제 하시겠습니까?"))
  	return;
	
	f.action = "<?=$g4[bbs_path]?>/delete_all.php";
  f.submit();
}	

// 선택한 게시물 삭제
function select_move() {
	
	if(!check_select()) return;
	$('#a_move_folder').trigger('click');
}

function select_move_do() {
	var f = document.frmflist;
	if (!confirm("선택한 문서를 정말 이동 하시겠습니까?"))
  	return;	
  
  f.move_to_folder.value = $("#move_folder").val();

	f.action = "<?=$wiki[path]?>/exe/move_doc_all.php";
  f.submit();
}

function closeDialog() {
	$.nmTop().close();
}
</script>