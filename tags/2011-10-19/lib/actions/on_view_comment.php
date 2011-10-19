<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 댓글에 대한 위키 문법 분석
 */
$wikiParser = wiki_class_load("Parser");
$list = &$params['list'];
if($use_comment) {

	for ($i=0; $i<count($list); $i++) {
		$list[$i][del_link] = wiki_adjust_path($list[$i][del_link]);
		$list[$i][content] = $wikiParser->parse($list[$i]);
	}
} else $list = "";
?>