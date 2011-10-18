<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
if($wiki[path] && file_exists($wiki[path]."/narin.config.php") ) {?>

<link rel="stylesheet" href="<?=$wiki[path]?>/css.php?bo_table=<?=$bo_table?>" type="text/css">
<script type="text/javascript">
	var wiki_path = "<?=$wiki[path]?>";
	var wiki_script = "<?=(basename($_SERVER["SCRIPT_NAME"]))?>";
</script>
<script type="text/javascript" src="<?=$wiki[path]?>/js.php?bo_table=<?=$bo_table?>"></script> 

<? } ?>