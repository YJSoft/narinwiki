<?
/**
 * 
 * 플러그인 명령 실행
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
include_once "_common.php";

if(!$p || !$m) wiki_not_found_page();

$wikiConfig =& wiki_class_load("Config");

$is_active = false;
foreach($wikiConfig->using_plugins as $plugin) {
	if($plugin == $p) {
		$is_active = true;
		break;
	}
}

// $p 는 플러그인 폴더명
$wikiEvent->trigger_one($p, strtoupper("PX_" . $p . "_" . $m), array("get"=>$_GET, "post"=>$_POST));

?>
