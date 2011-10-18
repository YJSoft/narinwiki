<?
if (!defined('_GNUBOARD_')) exit;

// 사용가능한 변수
// $g4, $wiki, $folder, $docname, $doc, $write_href
?>
<h1><?=$docname?></h1>
이 문서는 아직 만들어지지 않았습니다.
<div class="wikiToolbar">
	<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
	<span class="button green"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>">시작페이지</a></span>
	<span class="button red"><a href="<?=$write_href?>">페이지 만들기</a></span>
</div>