<?
/**
 * login 리다이렉트 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("./_common.php");
header("location:{$g4[bbs_path]}/login.php?url=$url");
?>