<?
/**
 * 
 * include skin 스크립트
 *
 * @package	narinwiki
 * @subpackage event
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

extract($wikiEvent->trigger("DELETE_ALL", 
							array("wr_id"=>$wr_id, 
									"folder"=>$folder, 
									"chk_wr_id"=>$_POST[chk_wr_id])));
?>