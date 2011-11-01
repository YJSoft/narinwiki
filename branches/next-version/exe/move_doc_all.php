<?
/**
 * 문서관리 : 문서 이동 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once "./_common.php";

if( !$is_wiki_admin )
{	
	alert("권한이 없습니다");
	exit;
}

$folder = stripcslashes($folder);
$move_to_folder = stripcslashes($move_to_folder);

if($folder != $move_to_folder) {
	$wikiArticle = wiki_class_load("Article");
	for($i=0; $i<count($chk_wr_id); $i++) {
		$wr = $wikiArticle->getArticleById($chk_wr_id[$i]);
		if(!$wr) continue;
		$fromDoc = ($folder == "/" ? "/" : $folder."/").$wr[wr_subject];
		$toDoc = ($move_to_folder == "/" ? "/" : $move_to_folder."/").$wr[wr_subject];
		$wikiArticle->moveDoc($fromDoc, $toDoc, $wr[wr_id]);
	}
}

header("location:{$wiki[path]}/folder.php?bo_table={$wiki[bo_table]}&loc=".urlencode($move_to_folder));

?>