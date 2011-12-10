<?
/**
 * 
 * 나린위키 최근문서 플러그인 액션 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 최근문서 플러그인 : 액션 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinActionLatest extends NarinActionPlugin {
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "wiki_action_latest";		
		parent::__construct();
	}	  	

	/**
	 * 
	 * @see lib/NarinActionPlugin::register()
	 */
	public function register($ctrl)
	{
		$ctrl->addHandler("PX_LATEST_LIST", $this, "on_ajax_call");
	}	
	
	/**
	 * 
	 * AJAX 콜에 대한 응답
	 * 
	 * ajax로 문서 lock 갱신
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_ajax_call($params) {

		$member = $this->member;
		
		$get = $params['get'];
		$ns = $get['path'];
		$recursive = (isset($get['nosub']) ? false : true);
		$rows = (isset($get['rows']) ? $get['rows'] : 5);
		$cutstr = (isset($get['title_length']) ? $get['title_length'] : 512);
		$dateformat = (isset($get['dateformat']) ? $get['dateformat'] : "Y-m-d h:i:s");
		$list = $this->recentUpdate(stripslashes($ns), $recursive, $dateformat, $rows, $cutstr);
		

		echo wiki_json_encode(array('code'=>1, 'current_time'=>$this->g4['time_ymdhis'], 'list'=>$list));
		exit;
	}

	
	/**
	 *
	 * 최근 업데이트 된 문서 목록
	 * 
	 * @param string $ns 폴더 경로
	 * @param boolean $recursive 하위 폴더도 읽어올 것인가
	 * @param string $dateformat 날짜 출력 포맷
	 * @param int $coubnt 몇개의 문서를 읽어올 것인가
	 * @param int $subject_len 제목 길이를 얼마로 자를 것인가
	 * @return array 문서 목록
	 */
	protected function recentUpdate($ns, $recursive = true, $dateformat, $count=5, $subject_len=255) {
		$ns = mysql_real_escape_string($ns);
		
		if($recursive) $ns_match = " nt.ns LIKE '".$ns."%'";
		else $ns_match = " nt.ns = '$ns'";
		
		$sql = "SELECT ht.id, ht.wr_id, ht.editor_mb_id AS editor, mt.mb_name as name, mt.mb_nick as nick, ht.reg_date as date, wt.wr_subject AS title, nt.ns, wt.wr_comment AS comments 
						FROM ".$this->wiki['history_table']." AS ht 
						JOIN ( SELECT MAX(id) AS id FROM ".$this->wiki['history_table']." GROUP BY wr_id ORDER BY id DESC)
									 AS ht2 ON ht.id = ht2.id
						JOIN ".$this->wiki['write_table']." AS wt ON ht.wr_id = wt.wr_id 
						JOIN ".$this->wiki['nsboard_table']." AS nt ON nt.wr_id = wt.wr_id AND nt.bo_table = '".$this->bo_table."' AND $ns_match
						JOIN ".$this->g4['member_table']." AS mt ON ht.editor_mb_id = mt.mb_id
						LIMIT $count
						";
				
		$res = sql_query($sql);
		$list = array();
		while($row = sql_fetch_array($res)) {
			$href = $this->wiki['path']."/narin.php?bo_table=".$this->wiki['bo_table']."&doc=".urlencode(wiki_doc($row['ns'], $row['title']));
			$row['title'] = conv_subject($row['title'], $subject_len, "…");
			$row['href'] = $href;
			$row['datetime'] = $row['date'];			
			$row['date'] = date($dateformat, strtotime($row['date']));
			$row['elapsed'] = $this->elapsedTime($row['datetime']);
			if(!$row['nick']) $row['nick'] = $row['editor'];
			if(!$row['name']) $row['name'] = $row['editor'];
			array_push($list, $row);
		}
		return $list;		
	}	

	/**
	 *
	 * 몇초전, 몇분전, 지난월요일 ... 식으로 날짜 변환
	 * 
	 * @param string $date 날짜 데이터 (e.g. 2011-12-09 12:05:30)
	 * @return string 흐른시간
	 */
	protected function elapsedTime($date) {
	
		$timeyear = 365 * 24 * 60 * 60;
		$timemonth = 30 * 7 * 24 * 60 * 60;
		$timeweek = 7 * 24 * 60 * 60;
		$timeday = 24 * 60 * 60;
		$timehour = 60 * 60;
		$timemins = 60;
		$timeseconds = 1;
		$dates = array("", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일", "일요일");
		$argtime = strtotime($date);
		$x = $this->g4['server_time'] - $argtime;
	
		if($x >= $timeyear) 
		{	
			return date("Y년 M월 j일", strtotime($argtime));
		} else if($x >= $timemonth) {
			return date("M월 j일", strtotime($argtime));
		} else if($x >= $timeday) {
			$x = round($x / $timeday);
			if($x < 7)	return "지난 " . $dates[date("N", $argtime)];
			else return date("n월 j일", strtotime($argtime));	
		} else if($x >= $timehour) {
			$x = round($x / $timehour); 
			return $x . ' 시간전';
		} else if($x >= $timemins) { 
			$x = round($x / $timemins); 
			return $x . ' 분전';
		} else if($x >= $timeseconds) { 
			$x = round($x / $timeseconds); 
			return $x . ' 초전';
		}
		
		// may not be reached here
		return date("j M Y", strtotime($argtime));
	}		

}


?>