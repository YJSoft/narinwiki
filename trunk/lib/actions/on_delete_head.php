<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 문서 삭제 후 처리 (HEAD)
 * 문서가 삭제되기 전에 호출됨 (권한 검사 등으로 삭제 안될 수 도 있음)
 */
$wr_id = $params[wr_id];
$wikiArticle = wiki_class_load("Article");
$article = $wikiArticle->getArticleById($wr_id);
$shared[delete_article] = $article;		

?>