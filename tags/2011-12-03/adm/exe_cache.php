<?
/**
 * 
 * 위키 관리 : thumbnail 실행 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("_common.php");


if($md == 'clear') {	
	
	$dir = $wiki['path'].'/data/'.$bo_table.'/thumb/';
	if(file_exists($dir)) {
		foreach(glob($dir.'*.*') as $v){
			@unlink($v);
		}
	}	
	
	$wikiCache = wiki_class_load("Cache");
	$wikiCache->clear();
	
	echo wiki_json_encode(array('code'=>1));
	exit;
}

if($md == 'rc' && $page) {
	$page_rows = 10;
	$sql = "SELECT count(id) AS count FROM ".$wiki['nsboard_table']." WHERE bo_table = '".$wiki['bo_table']."'";
	$res = sql_fetch($sql);
	$total = $res['count'];
	$from_record = ($page - 1) * $page_rows;
	$sql = "SELECT nb.wr_id, nb.ns, wb.wr_subject AS doc, wb.* FROM ".$wiki['nsboard_table']." AS nb
					LEFT JOIN ".$wiki['write_table']." AS wb 
						ON wb.wr_id = nb.wr_id
					WHERE nb.bo_table = '".$wiki['bo_table']."' 					
					ORDER BY id LIMIT $from_record, $page_rows";	
	$res = sql_query($sql);
	$idx = 0;
	$wikiCache = wiki_class_load("Cache");
	$wikiParser = wiki_class_load("Parser");
	while($write = sql_fetch_array($res)) {		
		if(!$write['wr_id']) { $idx++; continue; }
		$content = $wikiParser->parse($write);
		$wikiCache->update($write['wr_id'], $content);
		$idx++;
	}
	if($idx < $page_rows) {
		list($file_size, $file_count) = wiki_dir_filesize($wiki['path'].'/data/'.$bo_table.'/thumb');
		echo wiki_json_encode(array('code'=>100, 'file_size'=>wiki_file_size($file_size), 'file_count'=>$file_count)); 		// 더이상 없음
	} else {
		echo wiki_json_encode(array('code'=>1, 'total'=>$total, 'from'=>$from_record, 'to'=>($from_record+$page_rows)));
	}
	
	exit;
}

?>


