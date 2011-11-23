<?
/**
 * 플러그인 명령 실행
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$p || !$m) wiki_ajax_error();

$wikiEvent->trigger("AJAX_CALL", array("plugin"=>$p, "method"=>$m, "get"=>$_GET, "post"=>$_POST));

?>