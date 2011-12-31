<?
/**
 *
 * 액션 스크립트 : 댓글 보기 전 처리
 *
 * @package	narinwiki
 * @subpackage event
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * 댓글에 대한 위키 문법 분석
 */
$wikiParser =& wiki_class_load("Parser");
$list = &$params['list'];
if($use_comment) {

	for ($i=0; $i<count($list); $i++) {
		$list[$i]['del_link'] = $wiki['g4_url'].'/bbs/'.substr($list[$i]['del_link'], 2);
		if (!strstr($list[$i]['wr_option'], "secret") || $is_admin || $is_wiki_admin
		|| ($write['mb_id']==$member['mb_id'] && $member['mb_id'])
		|| ($list[$i]['mb_id']==$member['mb_id'] && $member['mb_id'])) {
			$list[$i]['content'] = $wikiParser->parse($list[$i]);
		}
	}
} else $list = "";
?>
