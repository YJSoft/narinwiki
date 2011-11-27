<?
define("__NARIN_API__", true);

/**
 * ajax 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
include_once "./_common.php";

//wiki_only_ajax();

@extract(wiki_unescape($_POST));
@extract(wiki_unescape($_GET));

$inc_file = "./response/".$w.".php";
if(!$w || !file_exists($inc_file)) {
	wiki_not_found_page();
}

include_once $inc_file;


function wiki_ajax_error($msg = "파라미터 오류") {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>$msg));
	exit;
}


?>