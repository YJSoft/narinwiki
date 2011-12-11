<?
/**
 * 
 * 문서 이력 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once "./_common.php";

wiki_only_ajax();

if($w == 'r' || $w == 'da' || $w == 'ds') {
	$wr = get_write($wiki['write_table'], $wr_id);
	
	if(!$wr) {
		echo "존재하지 않는 문서입니다.";
		exit;
	}
}

if(!$is_wiki_admin && $w != 'r') {
	echo "권한이 없습니다.";
	exit;
}

if($w != 'da' && $w != 'ds') {
	$h = sql_fetch("SELECT * FROM ".$wiki['history_table']." WHERE id = '$hid'");
	if(!$h) {
		echo "존재하지 않는 문서 이력입니다.";
		exit;
	}
}

$wikiHistory =& wiki_class_load("History");

if($w == 'r') {
	if(!$is_wiki_admin && ( !$member['mb_id'] || $member['mb_id'] != $wr['mb_id'])) {
		echo "권한이 없습니다.";
		exit;
	}

	$content = mysql_real_escape_string($h['content']);
	$sql = "UPDATE ".$wiki['write_table']." SET wr_content = '$content' WHERE wr_id = $wr_id";
	sql_query($sql);

	$first = sql_fetch("SELECT * FROM ".$wiki['history_table']." ORDER BY id DESC LIMIT 1");
	$wikiHistory->delete($first['id']);	
	$wikiHistory->delete($hid);
	
	$wikiHistory->update($wr_id, $wr['wr_content'], $member['mb_id'], "문서 복원에 따른 이전문서 백업");	
	$wikiHistory->update($wr_id, $h['content'], $member['mb_id'], "문서 복원에 따른 업데이트");	
	

	
	$wikiArticle =& wiki_class_load("Article");
	$wikiArticle->shouldUpdateCache($wr_id, 1);
	echo 1;
	exit;
}

if($w == 'd') {
	$wikiHistory->delete($hid);
	echo 1;
	exit;
}

if($w == 'da') {
	$wikiHistory->clear($wr_id);
	echo 1;
	exit;	
}


if($w == 'ds') {
	for($i=0; $i<count($hids); $i++) {
		$wikiHistory->delete($hids[$i]);		
	}
	echo 1;
	exit;
}
?>
