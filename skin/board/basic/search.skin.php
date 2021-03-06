<?
/**
 * 
 * 나린위키 스킨 : 검색 스킨
 *
 * <b>사용 변수</b>
 * - $list : 검색 결과 목록
 * - $write_pages : 페이징
 * - $wiki_admin_href : 관리자 링크
 *
 * @package	narinwiki
 * @subpackage skin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$colspan = 4;
if($is_good) $colspan++;
if($is_nogood) $colspan++;
?>

<div id="search_head" class="clear">
	
	<div id="search_title">
		위키 검색	
	</div>
	
	<form action="<?=$wiki['url']?>/search.php" onsubmit="return wiki_search(this);" method="get" class="wiki_form">
	<input type="hidden" name="bo_table" value="<?=$bo_table?>"/>
	<input type="text" class="search_text txt" name="stx" size="20" value="<?=wiki_input_value(stripcslashes($stx))?>"/>
	<span class="button purple"><input type="submit" value="검색"></span>
	</form>		
		
</div>

<div id="search_list">
<? for($i=0; $i<count($list); $i++) {
		$bg = $i%2 ? 0 : 1;
?>
	<div class="bg<?=$bg?>">
			<?
			echo "<a href='".$list[$i]['doc_href']."' class='title' style='margin-right:10px'>".$list[$i]['subject']."</a>";
			if ($list[$i]['comment_cnt'])
				echo " <a href=\"".$list[$i]['comment_href']."\"><span class='comment'>".$list[$i]['comment_cnt']."</span></a>";			
                
      echo " " . $list[$i]['icon_new'];
      echo " " . $list[$i]['icon_file'];
      echo " " . $list[$i]['icon_link'];
      echo " " . $list[$i]['icon_hot'];
      echo " " . $list[$i]['icon_secret'];
      echo $nobr_end;
      ?>
  </div>
	<div class="doc_url bg<?=$bg?>">
			<a href="<?=$list[$i]['doc_href']?>"><?=$list[$i]['doc']?></a>
	    - <span class="name"><?=$list[$i]['name']?></span>
	    <span class="datetime"><?=$list[$i]['datetime']?></span>
	    <span class="hit"><?=$list[$i]['wr_hit']?> hits</span>				   	    
	</div>
	<div class="search_content bg<?=$bg?>">
			<?=$list[$i]['content']?>
	</div>
<?}?>	
</div>

<? if($write_pages) {?>
<div id="paging">
	<?=$write_pages?>
</div>
<?}?>

<? if(!count($list)) {?>
<div class="nodata"><b><?=stripcslashes($stx)?></b> 에 대한 검색 결과가 없습니다.</div>
<?}?>

<div class="wikiToolbar clear">
	
	<div class="wikiLeftTools">
  	<span class="button"><a href="<?=wiki_url()?>">시작페이지</a></span>
	</div> <!--// wikiLeftTools -->	
	
	<div class="wikiRightTools">		  	  	  	  		
	  <? if($wiki_admin_href) {?>
			<span class='button'><a href='<?=$wiki_admin_href?>'>위키 관리</a></span>
		<? } ?>				
	</div> <!--// wikiRightTools -->
	
</div> <!--// wikiToolbar -->

<script type="text/javascript">
$(document).ready(function(){
	$("#wiki_search_text").focus().select();
});	
</script>
