<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 댓글 삭제 시 (HEAD)
 */
$wr = sql_fetch("SELECT wr_parent FROM $wiki[write_table] WHERE wr_id = $comment_id");
$wikiArticle = wiki_class_load("Article");
$article = $wikiArticle->getArticleById($wr[wr_parent]);
$shared[article_of_delete_comment] = $article;

?>