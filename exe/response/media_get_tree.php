<?
/**
 * 미디어 트리 출력
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if(!defined("__NARIN_API__")) wiki_not_found_page();

if(!$loc) wiki_ajax_error();

$media = wiki_class_load("Media");
$ns = $media->getNS($loc);
if(!$ns && $loc == '/') $media->addNamespace('/');
else if(!$ns) {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'존재하지 않는 폴더입니다.'));
	exit;
}

echo wiki_json_encode(array('code'=>1, 'tree'=>$media->get_tree("/", $loc)));	
	
?>