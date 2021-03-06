<?
/**
 *
 * 검색 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

include_once("./_common.php");

$write_table = $wiki['write_table'];
if(!trim($stx)) {
	goto_url(wiki_url());
	exit;
}
$sfl = "wr_subject||wr_content";
$sql_search = get_sql_search($sca, $sfl, $stx, "or");


$sql = " select MIN(wr_num) as min_wr_num from $write_table ";
$row = sql_fetch($sql);
$min_spt = $row['min_wr_num'];

if (!$spt) $spt = $min_spt;

// 폴더 접근 권한 / 문서 접근 권한 검사
$wiki_search = " LEFT JOIN ".$wiki['nsboard_table']." AS nb ON nb.bo_table = '$bo_table' AND nb.wr_id = wt.wr_id  ";
$wiki_search .= " LEFT JOIN ".$wiki['ns_table']." AS ns ON ns.bo_table = '$bo_table' AND nb.ns = ns.ns ";
$wiki_where = " AND ns.ns_access_level <= '".$member['mb_level']."' AND nb.access_level <= '".$member['mb_level']."' ";
$sql_search .= " AND (wr_num between '".$spt."' AND '".($spt + $config['cf_search_part'])."') ";

// 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
$sql = " SELECT DISTINCT wr_parent FROM $write_table AS wt $wiki_search WHERE $sql_search $wiki_where";
$result = sql_query($sql);
$total_count = mysql_num_rows($result);

$total_page  = ceil($total_count / $board['bo_page_rows']);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $board['bo_page_rows']; // 시작 열을 구함
$qstr2 = "bo_table=$bo_table&sop=or";

$sql = "SELECT DISTINCT wr_parent FROM $write_table AS wt $wiki_search WHERE $sql_search $wiki_where ORDER BY wr_num LIMIT $from_record, ".$board['bo_page_rows'];
$result = sql_query($sql);

$list = array();

$page_base_url = wiki_url('search', array('stx'=>$stx, 'page'=>''));
$write_pages = get_paging($config['cf_write_pages'], $page, $total_page, $page_base_url);
$board['bo_use_list_content'] = 1;

for ($i=0, $k=0; $row = sql_fetch_array($result); $i++, $k++)
{
	// 검색일 경우 wr_id만 얻었으므로 다시 한행을 얻는다
	$row = sql_fetch(" SELECT wt.*, nt.*, ct.content as cache FROM $write_table AS wt 
					   LEFT JOIN ".$wiki['cache_table']." AS ct ON ct.bo_table = '$bo_table' AND ct.wr_id = wt.wr_id
					   LEFT JOIN ".$wiki['nsboard_table']." AS nt ON nt.bo_table = '$bo_table' AND nt.wr_id = wt.wr_id 
					   LEFT JOIN ".$wiki['ns_table']." AS nb ON nt.ns = nb.ns AND nb.bo_table = '$bo_table' 
					   WHERE wt.wr_id = '$row[wr_parent]' ");
	
	$list[$i] = get_list($row, $board, SKIN_URL, 128);
	$list[$i]['folder'] = $row['ns'];
	$list[$i]['doc'] = wiki_doc($row['ns'], $row['wr_subject']);
	$list[$i]['doc_href'] = wiki_url('read', array('doc'=>wiki_doc($row['ns'], $row['wr_subject'])));

	if (strstr($sfl, "subject")) {
		$list[$i]['subject'] = wiki_search_font($stx, $list[$i]['subject']);
		$list[$i]['doc'] = wiki_search_font($stx, $list[$i]['doc']);
	}

	$list[$i]['content'] = $row['cache'];

	// remove TOC
	$list[$i]['content'] = preg_replace("/<div id='wiki_toc'>(.*?)<\!--\/\/ wiki_toc -->/si", "", $list[$i]['content']);

	// pre 안의 html 태그 제거를 위해
	$list[$i]['content'] = wiki_html($list[$i]['content']);

	if ($board[bo_read_lvel] <= $member[mb_level] && $row[access_level] <= $member[mb_level])
	{
		$content = cut_str(get_text(strip_tags($list[$i]['content'])),300,"…");
		if (strstr($sfl, "wr_content")) $content = wiki_search_font($stx, $content);
	}
	else
	$content = '';

	$list[$i]['content'] = $content;

	$list[$i][is_notice] = false;
	//$list[$i]['num'] = number_format($total_count - ($page - 1) * $board['bo_page_rows'] - $k);
	$list[$i]['num'] = $total_count - ($page - 1) * $board['bo_page_rows'] - $k;
}

$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
	$nobr_begin = "<nobr style='display:block; overflow:hidden;'>";
	$nobr_end   = "</nobr>";
}

// 관리자 링크
$wiki_admin_href = $wiki['url']."/adm/index.php?bo_table=".$bo_table;

// 머리문서 include
include_once WIKI_PATH . "/head.php";

// 스킨 include
include_once $wiki['inc_skin_path'] . "/search.skin.php";

// 꼬리 include
include_once WIKI_PATH . "/tail.php";


?>
