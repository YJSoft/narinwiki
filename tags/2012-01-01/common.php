<?
/**
 * 
 * 공용 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

define("__NARINWIKI__", true);
define("WIKI_PATH", dirname(__FILE__));

if(!file_exists(WIKI_PATH."/narin.config.php")) {
	header("location:install.php");
	exit;
}

if (isset($_GET['wiki_path']) || isset($_POST['wiki_path']) || isset($_COOKIE['wiki_path'])) {
    unset($_GET['wiki_path']);
    unset($_POST['wiki_path']);
    unset($_COOKIE['wiki_path']);
    unset($wiki_path);
}

include_once WIKI_PATH."/narin.config.php";
$g4_path = $wiki_path . '/'. $wiki['g4_path'];

include_once $g4_path."/common.php";
include_once WIKI_PATH."/lib/narin.wiki.lib.php";
include_once WIKI_PATH."/lib/narin.Plugin.class.php";

if($board['bo_1_subj'] != "narinwiki") {
	echo "<script type='text/javascript'>alert('존재하지 않는 위키입니다.'); location.href='{$g4['path']}';</script>";
	exit;
}

?>
