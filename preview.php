<?
include_once "_common.php";

list($ns, $doc, $path) = wiki_page_name($doc, $strip=true);

$wr = array('mb_id'=>$member['mb_id'],
					  'mb_level'=>$member['mb_level'],
					  'mb_name'=>$member['mb_name'],
					  'mb_nick'=>$member['mb_nick'],
					  'wr_subject'=>$doc,
					  'wr_content'=>stripslashes($content)
					 );

$parser = wiki_class_load("Parser");
$html = $parser->parse($wr);

$no_layout = true;
include_once "head.php";
echo $html;
include_once "tail.php";
?>