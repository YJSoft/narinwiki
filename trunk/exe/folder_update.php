<?
/**
 * 폴더 관리 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once "./_common.php";


$wikiArticle =& wiki_class_load("Article");
$wikiNS =& wiki_class_load("Namespace");

//현재 폴더
$loc = stripcslashes($loc);

// 변경할 폴더
$wiki_loc = stripcslashes($wiki_loc);

// 템플릿
$wiki_template = stripcslashes($wiki_template);

// 이름 유효성 검사
wiki_validate_folder($wiki_loc);

// 폴더 정보 로드
$ns = $wikiNS->get($loc);

// 권한 검사
if($w != 'u' || !$loc || !$wiki_loc || !$ns || !$wiki_access_level || !$is_wiki_admin)
{
	alert("권한이 없습니다");
	exit;
}

// 문자열 끝에 '/' 가 있다면 '/' 삭제
$loc = preg_replace("/\/$/", "", $loc);
$wiki_loc = preg_replace("/\/$/", "", $wiki_loc);
if(!$wiki_loc) $wiki_loc = "/";

// 폴더 업데이트 (이동)
if($ns['ns'] != "/" && $wiki_loc != "/" && $ns['ns'] != $wiki_loc) {
	$wikiNS->updateNamespace($loc, $wiki_loc);
}

// 템플릿 설정
$wikiNS->setTemplate($wiki_loc, $wiki_template);

// 권한 설정
$wikiNS->updateAccessLevel($wiki_loc, $wiki_access_level);

// 리다이렉트
wiki_goto_url(wiki_url('folder', array('loc'=>stripcslashes($wiki_loc))));
?>
