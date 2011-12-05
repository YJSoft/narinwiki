<?
/**
 * history 보기 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
include_once("./_common.php");

$wikiConfig = wiki_class_load("Config");
$history_access_level = $wikiConfig->setting[history_access_level];

$wikiControl = wiki_class_load("Control");
if($member[mb_level] < $history_access_level) {
	$wikiControl->error("문서 이력 보기 권한 없음", "문서 이력보기 권한이 없습니다.");	
}


if(!$doc) $doc = "/".$wiki[front];
if(!$page) $page = 1;

list($ns, $docname, $doc) = wiki_validate_doc($doc);

$wikiArticle = wiki_class_load("Article");
$view = $wikiArticle->getArticle($ns, $docname);

if(!$view) {
	$wikiControl->noDocument($ns, $docname, $doc);
} else {
	
	$wikiControl->acl($doc);

	// 권한 체크
	if($view[mb_id] && $view[mb_id] == $member[mb_id]) $is_doc_owner = true;
	else $is_doc_owner = false;	
	
	if( !$is_doc_owner && $is_wiki_admin && $member[mb_level] < $history_access_level)
	{
		$wikiControl->notAllowedDocument($ns, $docname, $doc);
	}

	$wikiHistory = wiki_class_load("History");
	
	list($history, $paging) = $wikiHistory->getHistory($view[wr_id], stripcslashes($doc), $page, $rows=15);	
		
	if( ($member[mb_id] && $view[mb_id] == $member[mb_id]) || $is_wiki_admin) {
		$clear_href = "javascript:clear_history({$view[wr_id]});";
		$delete_selected_href = "javascript:delete_selected_history({$view[wr_id]});";
	} else {
		$clear_href = "";
		$delete_selected_href = "";
	}
	$wikiControl->includePage($wiki[inc_skin_path] . "/history.skin.php", $layout=true);	
}

?>