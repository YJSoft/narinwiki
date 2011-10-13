<?
if (!defined('_GNUBOARD_')) exit;

class NarinHistory  extends NarinClass {

	protected $cache = array();
		
	/**
	 * Constructor
	 */
	public function __construct() {
  	parent::__construct();
	}

	function update($wr_id, $content, $mb_id, $summary='')
	{
		$wr_id = mysql_real_escape_string($wr_id);
		$content = mysql_real_escape_string($content);
		$mb_id = mysql_real_escape_string($mb_id);
		$summary = mysql_real_escape_string($summary);
		
		$sql = "INSERT INTO {$this->wiki[history_table]} (bo_table, wr_id, content, editor_mb_id, summary) VALUES ('{$this->wiki[bo_table]}', '$wr_id', '$content', '$mb_id', '$summary')";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_UPDATE", array("wr_id"=>$wr_id, "content"=>$content, "editor_mb_id"=>$mb_id, "summary"=>$summary));
	}

	function delete($hid)
	{
		$hid = mysql_real_escape_string($hid);
		$sql = "DELETE FROM {$this->wiki[history_table]} WHERE id = '$hid'";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_DELETE", array("hid"=>$hid));		
	}

	function clear($wr_id, $delete_all = false)
	{
		$wr_id = mysql_real_escape_string($wr_id);
		if($delete_all) sql_query("DELETE FROM {$this->wiki[history_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr_id'");
		else {
			$h = sql_fetch("SELECT id FROM {$this->wiki[history_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr_id'");		
			$sql = "DELETE FROM {$this->wiki[history_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr_id' AND id <> $h[id]";
			sql_query($sql);
		}
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_DELETE_ALL", array("wr_id"=>$wr_id, "delete_all"=>$delete_all));			
	}

	function getHistory($wr_id, $doc, $page = 1, $page_rows = 20)
	{			
		if($this->cache[$wr_id][$doc][$page][$page_rows])	return $this->cache[$wr_id][$doc][$page][$page_rows];
		$bo_table = $this->wiki[bo_table];
		$wikiParser = wiki_class_load("Parser");
		$wr_id = mysql_real_escape_string($wr_id);
		
		$sql_all = "SELECT id FROM {$this->wiki[history_table]} AS ht LEFT JOIN {$this->wiki[write_table]} AS wt ON ht.wr_id = wt.wr_id WHERE ht.bo_table = '{$this->wiki[bo_table]}' AND ht.wr_id = '$wr_id' ORDER BY ht.id DESC";	
		$result = sql_query($sql_all);
		$total_count = mysql_num_rows($result);
		
		$total_page  = ceil($total_count / $page_rows);
		$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

		$sql = "SELECT ht.*, wt.wr_option, wt.mb_id FROM {$this->wiki[history_table]} AS ht LEFT JOIN {$this->wiki[write_table]} AS wt ON ht.wr_id = wt.wr_id WHERE ht.bo_table = '{$this->wiki[bo_table]}' AND ht.wr_id = '$wr_id' ORDER BY ht.id DESC LIMIT $from_record, $page_rows";			
		$list = sql_list($sql);
							
		for($i=0; $i<count($list); $i++)
		{		
			$list[$i][content] = nl2br(wiki_text($list[$i][content]));
      $list[$i][date] = date("Y-m-d h:i", strtotime($list[$i][reg_date]));
      if($list[$i][mb_id] == $this->member[mb_id] || $this->is_admin) {
				if($page != 1 || $i > 0) {
      		$list[$i][recover_href] = "javascript:recover_history($wr_id, {$list[$i][id]});";
      		$list[$i][delete_href] = "javascript:delete_history({$list[$i][id]});";
      	}
      }
		}

		$paging = get_paging(10, $page, $total_page, $this->wiki[path]."/history.php?bo_table=$bo_table&doc=".urlencode($doc)."&page=");
		
		$ret = array($list, $paging);
		
		$this->cache[$wr_id][$doc][$page][$page_rows] = $ret;
		return $ret;
	}


	public function get($hid, $wr_id='') {
		if($this->cache[$hid])	return $this->cache[$hid];
		$wh = ($wr_id ? " AND wr_id='$wr_id'" : "");
		$sql = "SELECT * FROM {$this->wiki[history_table]} WHERE bo_table='".$this->bo_table."' AND id = '$hid' $wh";
		$row = sql_fetch($sql);		
		$this->cache[$hid] = $row;
		return $row;
	}
}

?>