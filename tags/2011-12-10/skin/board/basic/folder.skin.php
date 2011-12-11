<?
/**
 * 
 * 나린위키 스킨 : 폴더보기 스킨
 * 
 * 문서보기 상단의 네비게이션을 클릭했을 때 보여지는
 * 폴더보기 스킨 페이지
 * 
 * <b>사용 변수</b>
 * - $wiki['path'] : 위키 루트 폴더 경로
 * - $wiki['skin_path'] : 스킨 경로 ($wiki 변수는 narin.config.php, narin.wiki.lib.php 파일 참고)
 * - $folder['navi'] : 상단 네비게이션 문자열    e.g. Home > byfun > com > gnuboard
 * - $folder['loc'] : 파라미터로 넘어온 위치 문자열 e.g. /byfun/com/gnuboard
 * - $folder['up'] : 상위 폴더 e.g. /byfun/com
 * - $create_doc_href : 문서 생성 링크
 * - $folder_manage_href : 문서 관리 링크
 * - $wiki_admin_href : 위키 관리 링크
 * - $tree : 폴더 트리
 * - $folder_list : 폴더 내의 파일 목록 
 * - $member : 로그인 정보 ($member['mb_id'], $member['mb_level'], $member['mb_nick'] ...)
 * - $is_admin : 그누보드 관리자 인가
 * - $is_wiki_admin : 위키 관리자인가
 * 
 * <code> 
 * // $folder_list 의 한 row 형식
 * Array
 * (
 *   [ns] => 폴더경로
 *   [bo_table] => 게시판 id
 *   [mb_id] => 작성자 id
 *   [wr_name] => 작성자 name (문서 작성 시점)
 *   [mb_nick] => 작성자 nick
 *   [doc] => 문서명
 *   [wr_id] => 문서 id
 *   [wr_datetime] => 문서 생성 시간
 *   [editor] => 마지막 편집자 id
 *   [update_date] => 마지막 편집 시간
 *   [wr_good] => 추천수
 *   [wr_nogood] => 비추천수
 *   [wr_comment] => 댓글수
 *   [wr_hit] => 조회수
 *   [href] => 문서/폴더 보기 URL
 *   [name] => 문서 또는 폴더명
 *   [path] => 전체 경로 (폴더경로 + 문서명)
 *   [internal_link] => 위키 문서 링크 텍스트 (e.g. [[/폴더경로/문서명]])
 *   [type] => 유형 (doc 또는 folder)
 * ) 
 * 
 * </code>
 *
 * @package	narinwiki
 * @subpackage skin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; //개별 페이지 접근 불가 
$colspan = 5;
?>

<div id="wiki_title_bar">
	
	<span id="wiki_title">[[<?=$folder['loc']?>]]</span>
	
	<div class="wiki_tools clear">
		
		<div class="wiki_tools_left">
	  	
	  	<span class="button"><a href="<?=$wiki['path']?>/narin.php?bo_table=<?=$wiki['bo_table']?>">시작페이지</a></span>
			  	
		</div> <!--// wiki_tools_left -->
		
		<div class="wiki_tools_right">
		
			<form action="<?=$wiki['path']?>/search.php" onsubmit="return wiki_search(this);" method="get" class="wiki_form">
			<input type="hidden" name="bo_table" value="<?=$wiki['bo_table']?>"/>
			<input type="text" class="search_text txt" name="stx" size="20"/>
			<span class="button purple"><input type="submit" value="검색"></span>
			</form>		
				
		</div> <!--// wiki_tools_right -->
		
		<div style="float:right;margin-right:5px;">
			<span class='button'><a href='<?=$recent_href?>'>최근 변경내역</a></span>		
		</div>		
		
	</div> <!--// wiki_tools -->
	
	<div class="wiki_navigation">
		<?=$folder['navi']?>
	</div> <!--// wiki_navigation -->
	

</div> <!--// wiki_title_bar -->

<div id="wiki_contents">

<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td id="wiki_folder_navi"><?=$tree?></td><td valign="top">
	
	<div id="wiki_folder_contents">
		<form name="frmflist" method="post">
		<input type="hidden" name="bo_table" value="<?=$wiki['bo_table']?>">
		<input type="hidden" name="folder" value="<?=wiki_input_value($folder['loc'])?>">
		<input type="hidden" name="move_to_folder">

		<table id="folder_list" cellspacing="0" cellpadding="0" border="0">
		<colgroup>
			<col width="20px"> <!-- checkbox -->
			<col>
			<col width="70px"> <!-- mb_id -->
			<col width="50px"> <!-- hit -->
			<col width="80px"> <!-- date -->
		</colgroup>
		<thead>
			<th>
				<? if($is_wiki_admin) { ?>
				<input type="checkbox" name="checkall">
				<? } ?>				
			</th>
			<th style="padding-left:20px">이름</th>
			<th>작성자</th>
			<th>조회수</th>
			<th>날짜</th>
		</thead>
		<tbody>
		<? 
		// 최상위 폴더가 아니면... 상위 폴더 이동 링크 보여줌
		if($loc != "/") {?>
			<tr>
				<td> </td>
				<td class="flist folder_up">
					<a href="<?=$wiki['path']?>/folder.php?bo_table=<?=$wiki['bo_table']?>&loc=<?=$folder[up]?>">..</a>
				</td>
				<td> </td>
				<td> </td>
				<td> </td>
			</tr>	
			<tr><td colspan="<?=$colspan?>" height="1px" bgcolor="#ececec"></td></tr>	
		<?}
		
		for($i=0; $i<count($folder_list); $i++) {	?>
			<tr>
				<td>
				<? if($is_wiki_admin) {				
					if($folder_list[$i]['type'] == 'doc') {?>
							<input type="checkbox" name="chk_wr_id[]" class="chk" value="<?=$folder_list[$i][wr_id]?>" style="margin-top:3px"/>
					<?}
					}?>
				</td>
				<td class="flist <?=$folder_list[$i]['type']?>">
					<a href="<?=$folder_list[$i]['href']?>"><?=$folder_list[$i]['name']?></a>
					<? 
						if($folder_list[$i]['wr_comment']) {
							?><span class="f_comment">(<?=$folder_list[$i]['wr_comment']?>)</span><?
						}
					?>
				</td>		
				<td><?=$folder_list[$i]['editor']?></td>
				<td><?=$folder_list[$i]['wr_hit']?></td>
				<td><?=substr($folder_list[$i]['update_date'], 0, 11)?></td>
			</tr>
			<tr><td colspan="<?=$colspan?>" height="1px" bgcolor="#ececec"></td></tr>
			<? } ?>
		</tbody>
		</table>
		
		<div style="display:none;">
			<a href="#move_folder_layer" class="wiki_modal" id="a_move_folder">문서이동</a>
			<div id="move_folder_layer" >
				<div style="background-color:#3B3B3B; color:#fff; padding:5px;">
				문서 이동
				</div>
				<div style="padding:10px;">
					폴더 :
					<select name="move_folder" id="move_folder">
						<? for($i=0; $i<count($all_folders); $i++) {
							echo "<option value=\"".wiki_input_value($all_folders[$i]['path'])."\">{$all_folders[$i][display]}</option>";
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
	</div> <!--// wiki_folder_contents -->

</td></tr></table>
</div> <!--// wiki_contents -->

<? 
if($is_wiki_admin) {
	include_once $wiki['path']."/inc/inc.folder.manager.php";
} 
?>


<div class="wiki_tools clear" style="margin-top:10px;">
	
	<div class="wiki_tools_left">
		<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
		<span class="button"><a href="<?=$wiki['path']?>/narin.php?bo_table=<?=$wiki['bo_table']?>">시작페이지</a></span>
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
		$(".narin_tree").treeview({
			persist: "location",
			collapsed: true
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

	f.action = "<?=$wiki['path']?>/exe/move_doc_all.php";
  f.submit();
}

function closeDialog() {
	$.wiki_lightbox_close();
}
</script>