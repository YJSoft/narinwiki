<?
/**
 * 
 * 플러그인 명령 실행
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$p || !$m) wiki_ajax_error();

$wikiEvent->trigger("AJAX_CALL", array("plugin"=>$p, "method"=>$m, "get"=>$_GET, "post"=>$_POST));

?>