<?
/**
 * 
 * 에디터 미리보기 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once "_common.php";

$doc = wiki_ajax_data($doc);
$content = wiki_ajax_data($content);

list($ns, $doc, $path) = wiki_page_name(stripslashes($doc));

$wr = array('mb_id'=>$member['mb_id'],
					  'mb_level'=>$member['mb_level'],
					  'mb_name'=>$member['mb_name'],
					  'mb_nick'=>$member['mb_nick'],
					  'wr_subject'=>$doc,
					  'wr_content'=>stripslashes($content)
					 );

$parser =& wiki_class_load("Parser");
$html = $parser->parse($wr);

$no_layout = true;
ob_start();
include_once WIKI_PATH."/head.php";
echo $html;
include_once WIKI_PATH."/tail.php";
$content = ob_get_contents();
ob_clean();
echo $content;

?>
