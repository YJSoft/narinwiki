<?
/**
 * 
 * include head.sub 스크립트
 *
 * @package	narinwiki
 * @subpackage event
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if($wiki[path] && file_exists($wiki[path]."/narin.config.php") ) {
	if(!$doc && $wr_id) {
		$wikiArticle = wiki_class_load("Article");
		$tmp = $wikiArticle->getArticleById($wr_id);
		$doc = wiki_doc($tmp[ns], $tmp[doc]);
		list($ns, $docname, $doc) = wiki_page_name($doc);
	}

	$scriptFile = basename($_SERVER["SCRIPT_NAME"]);
	extract($wikiEvent->trigger("LOAD_HEAD", array("script"=>$scriptFile, 
													"folder"=>$ns, 
													"docname"=>$docname, 
													"doc"=>$doc)));	
?>

<link rel="stylesheet" href="<?=$wiki[path]?>/css.php?bo_table=<?=$bo_table?>" type="text/css" media="all">
<script type="text/javascript">
	var wiki_path = "<?=$wiki[path]?>";
	var wiki_script = "<?=(basename($_SERVER["SCRIPT_NAME"]))?>";
	var wiki_doc = "<?=addslashes($doc)?>";
</script>
<script type="text/javascript" src="<?=$wiki[path]?>/js.php?bo_table=<?=$bo_table?>"></script> 

<? } ?>