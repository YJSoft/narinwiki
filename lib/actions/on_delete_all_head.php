<?
/**
 * 액션 스크립트 : 여러 문서 삭제 시 (삭제되기 전)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined('_GNUBOARD_')) exit;

/**
 * 여러 문서 삭제 후 처리 (HEAD)
 */	
$delete_all_docs = array();

$wr_id = $params[wr_id];
$chk_wr_id = $params[chk_wr_id];

$tmp_array = array();
if ($wr_id) // 건별삭제
    $tmp_array[0] = $wr_id;
else // 일괄삭제
    $tmp_array = $chk_wr_id;		

$wikiArticle = wiki_class_load("Article");
for($i=0; $i<count($tmp_array); $i++) {
	$wr = $wikiArticle->getArticleById($tmp_array[$i]);
	if($wr) {
		$delete_all_docs[$wr[wr_id]] = wiki_doc($wr[ns], $wr[doc]);
	}
}

$shared[delete_all_docs] = $delete_all_docs;

?>