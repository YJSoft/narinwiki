<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

/*********************
 
 추가된 변수
	스킨 폴더의 *.css 파일은 자동으로 추가됨 
	(모든 스킨 파일에서 board_skin_path 의 css 파일을 링크할 필요 없음)
	$navigation  : 상단 네비게이션 문자열 e.g. Home > byfun > com > gnuboard
	$wiki_admin_href : 위키 관리 링크
	$doc_admin_href : 문서 관리 링크
	$history_href : 문서 이력 링크
	$back_links : 이 문서를 링크한 문서들 정보
	
**********************/
?>

<div id="wiki_title_bar">
	
	<span id="wiki_title">[[<a href="#backlinks" id="show_backlinks"><?=$view[subject]?></a>]]</span>
	
	<div class="wiki_tools clear">
		
		<div class="wiki_tools_left">
	  	
	  	<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
			<? if($history_href) {?>
		  	<span class='button'><a href='<?=$history_href?>'>문서 이력</a></span>
			<? }?>
			  	
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
		<?=$navigation?>
	</div> <!--// wiki_navigation -->
	

</div> <!--// wiki_title_bar -->



<!-- 내용 출력 -->
<div id="wiki_contents" class="wiki_contents">

	<div id="wiki_write_contents">		
	<?=$view[content]?>
	</div> <!--// wiki_write_contents -->
	
	<? if ($is_signature) { ?>
	<div id='wiki_signature'>
		<?=$signature?>
	</div><!--// wiki_signature -->
	<?}?>
			
	<!-- 테러 태그 방지용 --></xml></xmp><a href=""></a><a href=''></a>
	
	<? if($use_comment) { ?>		
	<div id="wiki_comment">
		<? include_once($wiki[path]."/inc/view_comment.php"); ?>
	</div> <!--// wiki_comment -->
	<? }?>

	
	<div id="wiki_after_contents">
	  작성일 : <?=date("y-m-d H:i", strtotime($view[wr_datetime]))?>
	 / 작성자 : <?=$view[name]?><? if ($is_ip_view) { echo "&nbsp;($ip)"; }?>
	 / 조회 : <?=number_format($view[wr_hit])?>
	  <? if ($is_good) { ?> / 추천 : <?=number_format($view[wr_good])?><? } ?>
  	<? if ($is_nogood) { ?> / 비추천 : <?=number_format($view[wr_nogood])?><? } ?>				
	</div> <!--// wiki_after_contents -->
	
</div> <!--// wiki_contents -->

<div id="wiki_back_links">
	<h2>이 문서를 링크하고 있는 다른 문서들</h2>
	<ul>
	<? for($i=0; $i<count($back_links); $i++) { ?>
		<li><a href="<?=$back_links[$i][href]?>"><?=$back_links[$i][wr_subject]?></a></li>		
	<? }?>
	</ul>
</div> <!--// wiki_back_links -->

<? 
// 문서 관리
if($is_wiki_admin || $is_doc_owner) {
	include_once($wiki[path]."/inc/inc.doc.manager.php");
} 
?>

<div class="wiki_tools clear">
	
	<div class="wiki_tools_left">	
		
  	<span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
  	<? if ($scrap_href) { echo "<span class='button'><a href=\"javascript:;\" onclick=\"win_scrap('$scrap_href');\">스크랩</a></span> "; } ?>
  	<? if ($trackback_url) { ?><span class='button'><a href="javascript:trackback_send_server('<?=$trackback_url?>');" style="letter-spacing:0;" title='주소 복사'>트랙백</a></span><?}?>
	</div> <!--// wiki_tools_left -->	
	
	<div class="wiki_tools_right">		
  	  	
	  <? if ($nogood_href) {?>
	  <span class="button"><a href="<?=$nogood_href?>" target="hiddenframe">비추천</a></span>
	  <? } ?>
	
	  <? if ($good_href) {?>
	  <span class="button"><a href="<?=$good_href?>" target="hiddenframe">추천</a></span>	 
	  <? } ?>		

  	<? if ($update_href) { ?>
  	<span class="button"><a href="<?=$update_href?>">수정</a></span>
  	<?}?>
		
		<? if ($delete_href) { ?>
		<span class="button"><a href="<?=$delete_href?>">삭제</a></span>
		<?}?>
	  
	  <? if($history_href) {?>
	  	<span class='button'><a href="<?=$history_href?>">문서 이력</a></span>
		<? }?>
	  
	  <? if($doc_admin_href) {?>
			<span class='button'><a href="#docadmin" id="show_docadmin">문서 관리</a></span>
		<? } ?>
		
	  <? if($wiki_admin_href) {?>
			<span class='button'><a href='<?=$wiki_admin_href?>'>위키 관리</a></span>
		<? } ?>		

		
	</div> <!--// wiki_tools_right -->
	
</div> <!--// wiki_tools -->




<script type="text/javascript">
function file_download(link, file) {
    <? if ($board[bo_download_point] < 0) { ?>if (confirm("'"+file+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board[bo_download_point])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}
</script>

<script type="text/javascript" src="<?="$g4[path]/js/board.js"?>"></script>
<script type="text/javascript">
$(document).ready(function() {
	resizeBoardImage(<?=(int)$board[bo_image_width]?>);
	drawFont();
	$("#show_backlinks").click(function(evt) {
		evt.preventDefault();
		if($("#wiki_back_links").is(":visible")) {
			$("#wiki_contents").show();
			$("#wiki_back_links").hide();
		} else {
			$("#wiki_contents").hide();
			$("#wiki_back_links").show();		
		}
		$("#wiki_doc_admin").hide();			
	});
	
	$("#show_docadmin").click(function(evt) {
		evt.preventDefault();
		if($("#wiki_doc_admin").is(":visible")) {
			$("#show_docadmin").text('문서 관리');
			$("#wiki_doc_admin").hide();
			$("#wiki_contents").show();
			$("#wiki_back_links").hide();
		} else {
			$("#show_docadmin").text('문서 보기');
			$("#wiki_doc_admin").show();
			$("#wiki_contents").hide();
			$("#wiki_back_links").hide();			
		}		
	});
});

</script>