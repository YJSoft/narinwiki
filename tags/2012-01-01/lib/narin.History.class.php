<?
/**
 * 
 * 나린위키 문서이력 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 문서이력 클래스
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiHistory =& wiki_class_load("History");
 * 
 * // 문서이력 정보 가져오기
 * $wikiHistory->get(히스토리id);
 *
 * // 문서이력 삭제
 * $wikiHistory->delete(히스토리id);
 * 
 * // wr_id = 79 인 문서의 모든 문서이력 삭제
 * $wikiHistory->delete(79);
 * 
 * // wr_id = 79 인 문서의 가장 최근의 문서이력(현재문서) 가져오기
 * $wikiHistory->getCurrent(79);  
 *  
 * // wr_id = 79 이고, 문서명은 "/narinwiki/플러그인" 인 문서의
 * // 문서이력의 첫 페이지 목록 가져오기 (한페이지는 15개)
 * list($history, $paging) = $wikiHistory->getHistory(79, "/narinwiki/플러그인", 1, 15);
 *  
 * // wr_id = 79 인 문서의 문서이력 남기기
 * $wikiHistory->update(79, "문서내용", 편집자 mb_id, "문서 요약...");
 * 
 * // wr_id = 79 이고 문서명이 "/narinwiki/플러그인" 인 문서가 삭제되었을 때, 
 * // unlinked 상태로 만들기
 * $wikiHistory->setUnlinked(79, "/narinwiki/플러그인");
 * 
 * // wr_id = 100 이고 문서명이 위와 같은 문서가 만들어졌을때, 
 * // 이전 문서 이력을 새로 생성된 문서의 문서 이력으로 만들기
 * $wikiHistory->setLinked(100, "/narinwiki/플러그인");
 * 
 * // unlinked 상태의 문서이력 목록
 * $wikiHistory->unlinkedHistory();
 * 
 * // unlinked 상태이고 문서명이 "/narinwiki/플러그인" 인 문서이력 삭제
 * // unlinked 상태가 아니면 삭제되지 않음
 * $wikiHistory->deleteUnlinked("/narinwiki/플러그인");
 * 
 * // 모든 unlinked 상태의 문서 삭제
 * $wikiHistory->clearUnlinked();
 * 
 * // 일주일 이전에 등록된 모든 문서이력 삭제
 * // unlinked 문서는 삭제되지 않음
 * $wikiHistory->clearHistoryByDate(7);
 * </code>
 * 
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinHistory  extends NarinClass {
		
	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * 문서 이력 업데이트
	 *
	 * @param int $wr_id 문서 id
	 * @param string $content 문서 내용
	 * @param string $mb_id 글 작성자 id 또는 name (비로그인시)
	 * @param int $summary 문서 요약
	 */
	function update($wr_id, $content, $mb_id, $summary='')
	{
		$wr_id = mysql_real_escape_string($wr_id);
		$content = mysql_real_escape_string($content);
		$mb_id = mysql_real_escape_string($mb_id);
		$summary = mysql_real_escape_string($summary);

		$sql = "INSERT INTO ".$this->wiki['history_table']."
					(bo_table, wr_id, content, editor_mb_id, summary, ip_addr, reg_date) 
					VALUES ('".$this->wiki['bo_table']."', '$wr_id', '$content', '$mb_id', '$summary', '".$this->user_ip."', '".$this->g4['time_ymdhis']."')";
		sql_query($sql);

		$wikiEvent =& wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_UPDATE", array("wr_id"=>$wr_id, "content"=>$content, "editor_mb_id"=>$mb_id, "summary"=>$summary));
	}

	/**
	 *
	 * 문서이력 삭제
	 * @param int $hid 문서이력 id
	 */
	function delete($hid)
	{
		$hid = mysql_real_escape_string($hid);
		$sql = "DELETE FROM ".$this->wiki['history_table']." WHERE id = '$hid'";
		sql_query($sql);

		$wikiEvent =& wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_DELETE", array("hid"=>$hid));
	}

	/**
	 *
	 * 문서에 대한 모든 문서 이력 삭제
	 * @param int $wr_id 문서 id
	 * @param boolean $delete_all	모든 이력을 삭제 할지, 최신 이력 하나는 남겨둘지
	 */
	function clear($wr_id, $delete_all = false)
	{
		$wr_id = mysql_real_escape_string($wr_id);
		if($delete_all) {
			sql_query("DELETE FROM ".$this->wiki['history_table']." 
						WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '$wr_id'");
		} else {
			$h = sql_fetch("SELECT id FROM ".$this->wiki['history_table']." 
							WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '$wr_id'");
			$sql = "DELETE FROM ".$this->wiki['history_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '$wr_id' AND id <> ".$h['id'];
			sql_query($sql);
		}

		$wikiEvent =& wiki_class_load("Event");
		$wikiEvent->trigger("HISTORY_DELETE_ALL", array("wr_id"=>$wr_id, "delete_all"=>$delete_all));
	}

	/**
	 *
	 * 문서이력 목록 반환
	 *
	 * @param int $wr_id 문서 id
	 * @param string $doc 문서명(경로포함)
	 * @param int $page 페이지
	 * @param int $page_rows 한 페이지당 보여줄 목록 수
	 * @return array 문서이력 목록
	 */
	function getHistory($wr_id, $doc, $page = 1, $page_rows = 20)
	{
		$bo_table = $this->wiki[bo_table];
		$wr_id = mysql_real_escape_string($wr_id);

		$sql_all = "SELECT id FROM ".$this->wiki['history_table']." AS ht
								LEFT JOIN ".$this->wiki['write_table']." AS wt ON ht.wr_id = wt.wr_id 
								WHERE ht.bo_table = '".$this->wiki['bo_table']."' AND ht.wr_id = '$wr_id' 
								ORDER BY ht.id DESC";	
		$result = sql_query($sql_all);
		$total_count = mysql_num_rows($result);

		$total_page  = ceil($total_count / $page_rows);
		$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

		$sql = "SELECT ht.*, wt.wr_option, wt.mb_id, mt.mb_name, mt.mb_nick FROM ".$this->wiki['history_table']." AS ht
						LEFT JOIN ".$this->wiki['write_table']." AS wt ON ht.wr_id = wt.wr_id 
						JOIN ".$this->g4['member_table']." AS mt ON wt.mb_id = mt.mb_id
						WHERE ht.bo_table = '".$this->wiki['bo_table']."' AND ht.wr_id = '$wr_id' 
						ORDER BY ht.id DESC LIMIT $from_record, $page_rows";			
		$list = wiki_sql_list($sql);
		for($i=0; $i<count($list); $i++)
		{
			// 로그인 안한 상태로 작성했다면...
			if(!$list[$i]['mb_name']) {
				$list[$i]['mb_name'] = $list['editor_mb_id'];
				$list[$i]['mb_nick'] = $list['editor_mb_id'];
			}
			$list[$i]['content'] = nl2br(wiki_text($list[$i]['content']));
			$list[$i]['date'] = date("Y-m-d h:i", strtotime($list[$i]['reg_date']));
			if($list[$i][mb_id] == $this->member[mb_id] || $this->is_admin) {
				if($page != 1 || $i > 0) {
					$list[$i]['recover_href'] = "javascript:recover_history($wr_id, ".$list[$i]['id'].");";
					if($this->is_admin) {
						$list[$i]['delete_href'] = "javascript:delete_history(".$list[$i]['id'].");";
					}
				}
			}
		}

		$paging = get_paging(10, $page, $total_page, $this->wiki['url']."/history.php?bo_table=$bo_table&doc=".urlencode($doc)."&page=");

		$ret = array($list, $paging);

		return $ret;
	}

	/**
	 * 
	 * 문서이력 리턴
	 * 
	 * @param int $hid 문서목록 id
	 * @param int $wr_id 문서 id
	 * @return array 문서이력 정보
	 */
	public function get($hid, $wr_id='') {
		$wh = ($wr_id ? " AND ht.wr_id='$wr_id'" : "");
		$sql = "SELECT * FROM ".$this->wiki['history_table']." AS ht
						LEFT JOIN ".$this->g4['member_table']." AS mt 
							ON ht.editor_mb_id = mt.mb_id 
					  WHERE ht.bo_table='".$this->bo_table."' AND ht.id = '$hid' $wh";
		$row = sql_fetch($sql);
		return $row;
	}

	/**
	 * 
	 * 현재 문서 이력 반환
	 * 
	 * @param int $wr_id 문서 id
	 * @return array 현재문서에 대한 이력 정보
	 */
	public function getCurrent($wr_id) {
		$sql = "SELECT * FROM ".$this->wiki['history_table']." AS ht
						LEFT JOIN ".$this->g4['member_table']." AS mt 
							ON ht.editor_mb_id = mt.mb_id 
		        WHERE ht.bo_table='".$this->bo_table."' AND ht.wr_id = '$wr_id' ORDER BY id DESC LIMIT 1";
		return sql_fetch($sql);
	}

	/**
	 * 
	 * 문서이력 unlink (삭제된 문서 이력)
	 * 
	 * @param int $wr_id 문서 id
	 * @param string $doc 문서명(경로포함)
	 */
	public function setUnlinked($wr_id, $doc) {
		$wr_id = mysql_real_escape_string($wr_id);
		$doc = mysql_real_escape_string($doc);
		$sql = "UPDATE ".$this->wiki['history_table']." SET wr_id = '-1', doc = '$doc' 
				WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = $wr_id";
		sql_query($sql);
	}

	/**
	 * 
	 * 문서이력 link (다시 생성된 문서 이력)
	 * 
	 * @param int $wr_id 문서 id
	 * @param string $doc 문서명(경로포함)
	 */
	public function setLinked($wr_id, $doc) {
		$wr_id = mysql_real_escape_string($wr_id);
		$doc = mysql_real_escape_string($doc);
		$sql = "UPDATE ".$this->wiki['history_table']." SET wr_id = '$wr_id', doc = '' 
				WHERE bo_table = '".$this->wiki['bo_table']."' AND doc = '$doc'";
		sql_query($sql);
	}

	/**
	 * 
	 * unlinked 문서 이력 목록 반환
	 * 
	 * @return array unlinked 문서 이력
	 */
	public function unlinkedHistory() {
		$sql = "SELECT doc FROM ".$this->wiki['history_table']." WHERE bo_table = '".$this->bo_table."' AND wr_id = -1 GROUP BY doc";
		return wiki_sql_list($sql);
	}

	/**
	 * 
	 * unlinked 문서이력 삭제
	 * 
	 * @param string $doc 문서명(경로포함)
	 */
	public function deleteUnlinked($doc) {
		$doc = mysql_real_escape_string($doc);
		sql_query("DELETE FROM ".$this->wiki['history_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = -1 AND doc = '$doc'");
	}

	/**
	 * 
	 * 모든 unlinked 문서이력 삭제
	 */
	public function clearUnlinked() {
		sql_query("DELETE FROM ".$this->wiki['history_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = -1");
	}

	/**
	 * 
	 * 문서이력 정리
	 * 
	 * @param int $day 주어진 날 이전의 문서이력을 모두 삭제
	 */
	public function clearHistoryByDate($day) {
		sql_query("DELETE FROM ".$this->wiki['history_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id <> -1 AND reg_date < DATE_SUB(NOW(), INTERVAL $day DAY)");
	}
}

?>
