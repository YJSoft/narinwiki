<?
/**
 * include skin 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("VIEW_COMMENT", array("folder"=>$ns, 
																				"docname"=>$docname, 
																				"doc"=>$doc,
																				"use_comment"=>$use_comment, 
																				"list"=>&$list)));

?>