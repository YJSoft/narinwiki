<?
if (!defined('_GNUBOARD_')) exit;

class NarinCache extends NarinClass {
	
	public function get($wr_id) {
		$row = sql_fetch("SELECT content FROM {$this->wiki[cache_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = $wr_id");
		return $row[content];
	}

	public function update($wr_id, $content) {		
		$content = mysql_real_escape_string($content);
		$ex = $this->get($wr_id);
		if(!$ex) sql_query("INSERT INTO {$this->wiki[cache_table]} VALUES ('', '{$this->wiki[bo_table]}', $wr_id, '$content')");
		else {
			sql_query("UPDATE {$this->wiki[cache_table]} SET content = '$content' WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = $wr_id");
			sql_query("UPDATE {$this->wiki[nsboard_table]} SET should_update_cache = 0 WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = $wr_id");
		}
	}
	
	public function delete($wr_id) {
		sql_query("DELETE FROM {$this->wiki[cache_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = $wr_id");
	}

}

?>