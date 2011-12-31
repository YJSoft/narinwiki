<?
/**
 * 
 * 위키 관리 : 관리 플러그인 실행
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "front";

include_once("_common.php");


$wikiAdmin =& wiki_class_load("Admin");
$plugin = $wikiAdmin->getPlugin($p);

if(!$wikiAdmin->isUsable($p) || !$plugin) { 
	alert('사용할 수 없는 플러그인입니다.');
	exit;
}

if(!$m) $m = 'view';
else if(!is_callable(array($plugin, $m))) {
	alert('지원되지 않는 기능입니다.');
	exit;
}

if(!$nolayout) include_once "admin.head.php";
$plugin->$m(array('get'=>$_GET, 'post'=>$_POST));

//print_r($_GET);
if(!$nolayout) include_once "admin.tail.php";
?>
