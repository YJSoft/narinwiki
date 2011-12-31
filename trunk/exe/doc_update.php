<?
/**
 * 
 * 문서 관리 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once "./_common.php";

$wikiArticle =& wiki_class_load("Article");
$write = &$wikiArticle->getArticle($ns, $docname);
if(!$write) {
	alert("존재하지 않는 문서입니다.");
	exit;
}
if( !$is_wiki_admin && ($member['mb_id'] && $member['mb_id'] != $write['mb_id']) )
{	
	alert("권한이 없습니다");
	exit;
}

if(!$w || !$doc || !$wiki_folder_switch || !$wiki_doc ||!$write || !$wiki_access_level || (!$is_wiki_admin && $member['mb_id'] != $write['mb_id']))
{
	alert("잘못된 접근입니다.");
	exit;	
}

if($wiki_folder_switch == "wiki_folder_select") {
	$wiki_folder = $wiki_folder_select;
} else {
	$wiki_folder = $wiki_folder_input;
}

$wiki_doc = stripcslashes($wiki_doc);
$wiki_folder = stripcslashes($wiki_folder);

// 문자열 끝에 '/' 가 있다면 '/' 삭제
$wiki_folder = preg_replace("/\/$/", "", $wiki_folder);

// target folder 가 없다면 '/' 로 셋팅
$toDoc = ($wiki_folder == "" ? "/" : $wiki_folder."/") . $wiki_doc;

// target folder 유효성 체크
wiki_validate_doc($toDoc);

if($write[ns] != $wiki_folder || $write[doc] != $wiki_doc) {
	$wikiArticle->moveDoc($fulldoc, $toDoc, $write['wr_id']);
}

$wikiArticle->updateLevel($toDoc, $wiki_access_level, $wiki_edit_level);

header("location:".wiki_url('read', array('doc'=>$toDoc)));

?>
