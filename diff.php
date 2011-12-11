<?
/**
 * 
 * 문서 비교 스크립트 (diff)
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("./_common.php");

$wikiConfig =& wiki_class_load("Config");
$history_access_level = $wikiConfig->setting['history_access_level'];

$wikiControl =& wiki_class_load("Control");
if($member['mb_level'] < $history_access_level) {
	$wikiControl->error("문서 이력 보기 권한 없음", "문서 이력보기 권한이 없습니다.");	
}

$wikiControl =& wiki_class_load("Control");
$wikiArticle =& wiki_class_load("Article");
$wikiHistory =& wiki_class_load("History");
	
$history = $wikiHistory->get($hid);	
if(!$history) {
	$wikiControl->error("문서 이력 에러", "요청하신 문서 이력에 대한 자료가 없습니다.");
}

$article = &$wikiArticle->getArticleById($history['wr_id']);	

$folder = $article['ns'];
$docname = $article['doc'];
$doc = wiki_doc($article['ns'], $article['doc']);	

$wikiControl->acl($doc);

$current = $wikiHistory->getCurrent($history['wr_id']);
// 문서 작성자?
$is_doc_owner = ( $article['mb_id'] && $article['mb_id'] == $member['mb_id'] );

include_once "head.php";
?>
<style type="text/css">
.Differences {
	width: 100%;
	border:1px solid #ccc;
	border-collapse: collapse;
	border-spacing: 0;
	empty-cells: show;
}

.Differences thead th {
	text-align: left;
	border-bottom: 1px solid #000;
	background: #ccc;
	color: #000;
	padding: 4px;
}
.Differences tbody th {
	text-align: right;
	background: #eee;
	width: 4em;
	padding: 1px 2px;
	border-right: 1px solid #000;
	vertical-align: top;
	font-size: 13px;
}

.Differences td {
	padding: 1px 2px;
	font-family: Consolas, monospace;
	font-size: 13px;
}

.DifferencesSideBySide .ChangeInsert td.Left {
	background: #dfd;
}

.DifferencesSideBySide .ChangeInsert td.Right {
	background: #cfc;
}

.DifferencesSideBySide .ChangeDelete td.Left {
	background: #f88;
}

.DifferencesSideBySide .ChangeDelete td.Right {
	background: #faa;
}

.DifferencesSideBySide .ChangeReplace .Left {
	background: #fe9;
}

.DifferencesSideBySide .ChangeReplace .Right {
	background: #fd8;
}

.Differences ins, .Differences del {
	text-decoration: none;
}

.DifferencesSideBySide .ChangeReplace ins, .DifferencesSideBySide .ChangeReplace del {
	background: #fc0;
}

.Differences .Skipped {
	background: #f7f7f7;
}

.DifferencesInline .ChangeReplace .Left,
.DifferencesInline .ChangeDelete .Left {
	background: #fdd;
}

.DifferencesInline .ChangeReplace .Right,
.DifferencesInline .ChangeInsert .Right {
	background: #dfd;
}

.DifferencesInline .ChangeReplace ins {
	background: #9e9;
}

.DifferencesInline .ChangeReplace del {
	background: #e99;
}

pre {
	width: 100%;
	overflow: auto;
}	

.list_table table th { width:80px; text-align:right;padding-right:5px; }
h1 { padding:0; margin:0; }
</style>

<h1>문서 비교 </h1>

<div class="list_table">
	<table cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<th>문서명</th><td><?=$docname?></td>
		</tr>
		<tr>
			<th>현재문서</th><td><?=$current['editor_mb_id']?> (<?=$current['reg_date']?>)</td>
		</tr>
		<tr>
			<th>이전문서</th><td><?=$history['editor_mb_id']?> (<?=$history['reg_date']?>)</td>
		</tr>
		<tr>
			<th>변경내역</th><td><?=$history['summary']?></td>
		</tr>
	</tbody>
	</table>
</div>

<?
require_once $wiki['path']."/lib/Diff/Diff.php";
require_once $wiki['path']."/lib/Diff/Renderer/Html/SideBySide.php";
$options = array(
	//'ignoreWhitespace' => true,
	//'ignoreCase' => true
);
if(wiki_is_euckr()) {
	$history['content'] = iconv("CP949", "UTF-8", $history['content']);
	$article['wr_content'] = iconv("CP949", "UTF-8", $article['wr_content']);
}
$history_content = explode("\n", $history['content']);
$current_content = explode("\n", $article['wr_content']);
$diff = new Diff($history_content, $current_content, $options);
$renderer = new Diff_Renderer_Html_SideBySide();
$diffData = $diff->Render($renderer);

if(wiki_is_euckr()) {
	$diffData = preg_replace_callback("/(<td(.*?)>)(.*?)(<\/td>)/s", create_function('$matches', 'return $matches[1].iconv("UTF-8", "CP949", $matches[3]).$matches[4];'), $diffData);
}

echo $diffData;
?>

<div class="clear" style="margin-top:10px">
	<div style="float:left">
		<span class="button"><a href="<?=$wiki['path']?>/narin.php?bo_table=<?=$bo_table?>">시작페이지</a></span>
		<span class="button"><a href="<?=$wiki['path']?>/history.php?bo_table=<?=$bo_table?>&doc=<?=urlencode($doc)?>">문서이력  목록</a></span>
		<span class="button red"><a href="<?=$wiki['path']?>/narin.php?bo_table=<?=$bo_table?>&doc=<?=urlencode($doc)?>">문서보기</a></span>
		<? if($is_wiki_admin || $is_doc_owner) { ?>
		<span class="button green"><a href="javascript:recover_history(<?=$article['wr_id']?>, <?=$hid?>);">이 문서로 복원</a></span>
		<? } ?>
	</div>
	<div style="float:right">
		<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
	</div>
</div>

<?
include_once "tail.php";
?>
