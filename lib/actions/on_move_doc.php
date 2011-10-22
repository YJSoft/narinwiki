<?
if (!defined('_GNUBOARD_')) exit;

// 최근 변경 내역 업데이트
$wikiChanges = wiki_class_load("Changes");
$toDoc = $params[to];
$doc = $params[from];
$wikiChanges->update("DOC", $doc, "이름 변경 (이전)", $member[mb_id]);		
$wikiChanges->update("DOC", $toDoc, "이름 변경 (이후)", $member[mb_id]);

?>