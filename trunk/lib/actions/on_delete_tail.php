<?
/**
 * 액션 스크립트 : 문서 삭제 (삭제된 후)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined('_GNUBOARD_')) exit;

$wikiArticle = wiki_class_load("Article");
$article = $delete_article;

$doc = wiki_doc($article[ns], $article[doc]);

$backlinks = $wikiArticle->getBackLinks($doc, $includeSelf = false);
for($i=0; $i<count($backlinks); $i++) {
	$wikiArticle->shouldUpdateCache($backlinks[$i][wr_id], 1);
}

$wikiArticle->deleteArticleById($wr_id);

// 문서 이력 삭제
$wikiHistory = wiki_class_load("History");
$wikiHistory->setUnlinked($wr_id, $doc);
//$wikiHistory->clear($wr_id, $delete_all = true);

// 캐시 삭제				
$wikiCache = wiki_class_load("Cache");
$wikiCache->delete($wr_id);

// 최근 변경 내역 업데이트
$wikiChanges = wiki_class_load("Changes");
$wikiChanges->update("DOC", $doc, "삭제", ($member[mb_id] ? $member[mb_id] : $write[wr_name]));


?>