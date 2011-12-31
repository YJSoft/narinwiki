<?
/**
 *
 * login 페이지로 포워딩
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
include_once("./_common.php");
header("location:".$wiki['g4_url']."/bbs/login.php?url=$url");
?>
