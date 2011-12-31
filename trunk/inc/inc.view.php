<?
/**
 *
 * include skin 스크립트
 *
 * @package	narinwiki
 * @subpackage event
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

extract($wikiEvent->trigger("VIEW", array("folder"=>$ns,
											"docname"=>$docname, 
											"is_history"=>$is_history,
											"doc"=>$doc,
											"write_href"=>$write_href,
											"delete_href"=>$delete_href,
											"scrap_href"=>$scrap_href,
											"update_href"=>$update_href,
											"comment_delete_href"=>$comment_delete_href,
											"good_href"=>$good_href,
											"nogood_href"=>$nogood_href,
											"wr_id"=>$wr_id, 
											"hid"=>$hid,
											"view"=>&$view)));
?>
