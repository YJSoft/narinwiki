<?
if (!defined('_GNUBOARD_')) exit;

// 최근 변경 내역 업데이트
$wikiChanges = wiki_class_load("Changes");
$toDoc = $params[to];
$doc = $params[from];
$wikiChanges->update($doc, "이름 변경 (이전)", $this->member[mb_id]);		
$wikiChanges->update($toDoc, "이름 변경 (이후)", $this->member[mb_id]);

?>