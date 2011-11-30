<?
/**
 *
 * login 페이지로 포워딩
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("./_common.php");
header("location:".$g4['bbs_path']."/login.php?url=$url");
?>