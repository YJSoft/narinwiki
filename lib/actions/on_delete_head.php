<?
/**
 * 액션 스크립트 : 문서 삭제 (삭제되기 전)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
if (!defined('_GNUBOARD_')) exit;

$wr_id = $params[wr_id];
$wikiArticle = wiki_class_load("Article");
$article = $wikiArticle->getArticleById($wr_id);
$shared[delete_article] = $article;		

?>