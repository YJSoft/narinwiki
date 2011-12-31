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

extract($wikiEvent->trigger("DELETE_ALL", 
							array("wr_id"=>$wr_id, 
									"folder"=>$folder, 
									"chk_wr_id"=>$_POST[chk_wr_id])));
?>
