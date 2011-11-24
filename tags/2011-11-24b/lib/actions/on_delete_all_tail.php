<?
/**
 * 액션 스크립트 : 여러 문서 삭제 시 (삭제된 후)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
if (!defined('_GNUBOARD_')) exit;

/**
 * 여러 문서 삭제 후 처리 (TAIL)
 */		
$wikiArticle = wiki_class_load("Article");
$wikiArticle->removeAllNotExistsDoc();

$wikiNS = wiki_class_load("Namespace");
$wikiNS->removeAllEmptyNS();

// $wr_id_array 에 지워야할 wr_id 있음
// 하지만 권한이나 기타 문제로 지워지지 않는것도 있기 때문에
// 일일이 체크해가며 cache 삭제
$wikiCache = wiki_class_load("Cache");
$wikiHistory = wiki_class_load("History");	

// 최근 변경 내역 업데이트
$wikiChanges = wiki_class_load("Changes");
	
for ($i=count($wr_id_array)-1; $i>=0; $i--) 
{
	$write = sql_fetch(" select wr_id from {$this->wiki[write_table]} where wr_id = '{$wr_id_array[$i]}' ");
	if(!$write) {
		$wikiCache->delete($wr_id_array[$i]);
		$wikiHistory->clear($wr_id_array[$i], $delete_all = true);
		$d_doc = $delete_all_docs[$wr_id_array[$i]];
		$backlinks = $wikiArticle->getBackLinks($d_doc, $includeSelf = false);
		$wikiChanges->update("DOC", $d_doc, "삭제", $member[mb_id]);				
		for($k=0; $k<count($backlinks); $k++) {
			$wikiArticle->shouldUpdateCache($backlinks[$k][wr_id], 1);
		}
	}			
}		

if($folder) {
	$bo_table = $wiki[bo_table];
	$ns = $wikiNS->get($folder);
	if(!$ns) goto_url("{$this->wiki[path]}/narin.php?bo_table=$bo_table");
	else goto_url("{$this->wiki[path]}/folder.php?bo_table=$bo_table&loc=".urlencode($folder));
	exit;
}		

?>