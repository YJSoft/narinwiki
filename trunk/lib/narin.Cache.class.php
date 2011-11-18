<?
/**
 * 나린위키 캐시(Cache) 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */

class NarinCache extends NarinClass {
	
	/**
	 * 
	 * 해당 글에 대한 캐시 반환
	 * @param (integer) $wr_id	글 아이디
	 */
	public function get($wr_id) {
		$row = sql_fetch("SELECT content FROM {$this->wiki['cache_table']} WHERE bo_table = '{$this->wiki['bo_table']}' AND wr_id = $wr_id");
		return $row[content];
	}
	
	/**
	 * 
	 * 캐시 생성/업데이트
	 * @param (integer) $wr_id 글 아이디
	 * @param (string) $content 내용 (parsing 된 html)
	 */
	public function update($wr_id, $content) {		
		$content = mysql_real_escape_string($content);
		$ex = $this->get($wr_id);
		if(!$ex) sql_query("INSERT INTO {$this->wiki['cache_table']} VALUES ('', '{$this->wiki['bo_table']}', $wr_id, '$content')");
		else {
			sql_query("UPDATE {$this->wiki['cache_table']} SET content = '$content' WHERE bo_table = '{$this->wiki['bo_table']}' AND wr_id = $wr_id");
			sql_query("UPDATE {$this->wiki['nsboard_table']} SET should_update_cache = 0 WHERE bo_table = '{$this->wiki['bo_table']}' AND wr_id = $wr_id");
		}
	}
	
	/**
	 * 
	 * 캐시 삭제
	 * @param (integer) $wr_id 글 아이디
	 */
	public function delete($wr_id) {
		sql_query("DELETE FROM {$this->wiki['cache_table']} WHERE bo_table = '{$this->wiki['bo_table']}' AND wr_id = $wr_id");
	}
	
	/**
	 * 
	 * 모든 캐시 초기화
	 */
	public function clear() {
		sql_query("DELETE FROM {$this->wiki['cache_table']} WHERE bo_table = '{$this->wiki['bo_table']}'");
		sql_query("UPDATE {$this->wiki['nsboard_table']} SET should_update_cache = 1 WHERE bo_table = '{$this->wiki['bo_table']}'");		
	}

}

?>