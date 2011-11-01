<?
/**
 * 액션 스크립트 : 댓글 삭제 (삭제되기 전)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined('_GNUBOARD_')) exit;

$wr = sql_fetch("SELECT wr_parent FROM $wiki[write_table] WHERE wr_id = $comment_id");
$wikiArticle = wiki_class_load("Article");
$article = $wikiArticle->getArticleById($wr[wr_parent]);
$shared[article_of_delete_comment] = $article;

?>