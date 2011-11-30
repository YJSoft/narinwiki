<?
/**
 * 
 * 액션 스크립트 : 글 작성폼 보이기 전 처리
 *
 * @package	narinwiki
 * @subpackage event
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined('_GNUBOARD_')) exit;

list($subject, $wr_doc) = wiki_doc_from_write($doc, $wr_id);

if(!$write['is_owner'] && !$is_wiki_admin) $return_array['is_file'] = false;

$title_msg = "문서 편집";
if(!$w) {
	$title_msg = "새 문서";
	$wikiNS = wiki_class_load("Namespace");
	$folder = $wikiNS->get($folder);
	$tpl = $folder['tpl'];
	$source = array("/@DOCNAME@/", "/@FOLDER@/", "/@USER@/", "/@NAME@/", "/@NICK@/", "/@MAIL@/", "/@DATE@/");
	$target = array($docname, $folder, $member['mb_id'], $member['mb_name'], $member['mb_nick'], $member['mb_email'], date("Y-m-d h:i:s"));	
	$content = preg_replace($source, $target, $tpl);
	$return_array['content'] = $content;
}

$return_array['title_msg'] = $title_msg;
$return_array['subject'] = wiki_input_value($subject);
$return_array['wr_doc'] = wiki_input_value($wr_doc);

?>