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

extract($wikiEvent->trigger("VIEW_HEAD", array("folder"=>$ns, 
												"docname"=>$docname, 
												"doc"=>$doc,
												"wr_id"=>$wr_id, 
												"view"=>&$view)));
?>
