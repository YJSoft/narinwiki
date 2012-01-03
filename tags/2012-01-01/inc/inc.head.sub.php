<?
/**
 * 
 * include head.sub 스크립트
 *
 * @package	narinwiki
 * @subpackage event
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if(file_exists(WIKI_PATH."/narin.config.php") ) {
	if(!$doc && $wr_id) {
		$wikiArticle =& wiki_class_load("Article");
		$tmp = &$wikiArticle->getArticleById($wr_id);
		$doc = wiki_doc($tmp['ns'], $tmp['doc']);
		list($ns, $docname, $doc) = wiki_page_name($doc);
	}

	$scriptFile = basename($_SERVER['SCRIPT_NAME']);
	$wikiEvent =& wiki_class_load("Event");
	extract($wikiEvent->trigger("LOAD_HEAD", array("script"=>$scriptFile, 
													"folder"=>$ns, 
													"docname"=>$docname, 
													"doc"=>$doc)));	
$css_path = $wiki['url'].'/css.php';
$js_path = $wiki['url'].'/js.php';
if($wiki['fancy_url']) {
	$css_path = $wiki['url'].'/_narin.css';
	$js_path = $wiki['url'].'/_narin.js';
}
if(isset($wiki_head)) {
echo $wiki_head;
}
?>
<link rel="stylesheet" href="<?=$css_path?>" type="text/css" media="all">
<script type="text/javascript">
	var wiki_url = "<?=$wiki['url']?>";
	var wiki_script = "<?=(basename($_SERVER['SCRIPT_NAME']))?>";
	var wiki_doc = "<?=addslashes($doc)?>";
	<? if($wiki['fancy_url']) { ?>
var wiki_fancy = true;
	g4_path = "<?=$wiki['g4_url']?>";
	g4_url = g4_path;
	<? } ?>	
</script>
<script type="text/javascript" src="<?=$js_path?>"></script>
<? } ?>
