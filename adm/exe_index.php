<?
/**
 * 위키 관리 : manage 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("_common.php");

if($w == 'jscssrefresh') {
	$wikiJsCss = wiki_class_load('JsCss');
	$wikiJsCss->updateJs();
	$wikiJsCss->updateCss();
}

header("location:".$wiki['url']."/adm/");
?>


