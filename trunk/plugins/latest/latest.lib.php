<?
if(!defined("_LATEST_PLUGIN_")) die("잘못된 접근");

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
	function wiki_latest_recentUpdate($wiki, $g4, $ns, $recursive = true, $dateformat, $count=5, $subject_len=255) {		
		$ns = mysql_real_escape_string($ns);
		
		if($recursive) $ns_match = " nt.ns LIKE '".$ns."%'";
		else $ns_match = " nt.ns = '$ns'";
		
		$sql = "SELECT ht.id, ht.wr_id, ht.editor_mb_id AS editor, mt.mb_name as name, mt.mb_nick as nick, ht.reg_date as date, wt.wr_subject AS title, nt.ns, wt.wr_comment AS comments 
						FROM ".$wiki['history_table']." AS ht 
						JOIN ( SELECT MAX(id) AS id FROM ".$wiki['history_table']." GROUP BY wr_id ORDER BY id DESC)
									 AS ht2 ON ht.id = ht2.id
						JOIN ".$wiki['write_table']." AS wt ON ht.wr_id = wt.wr_id 
						JOIN ".$wiki['nsboard_table']." AS nt ON nt.wr_id = wt.wr_id AND nt.bo_table = '".$wiki['bo_table']."' AND $ns_match
						JOIN ".$g4['member_table']." AS mt ON ht.editor_mb_id = mt.mb_id
						LIMIT $count
						";
				
		$res = sql_query($sql);
		$list = array();
		while($row = sql_fetch_array($res)) {
			$href = $wiki['path']."/narin.php?bo_table=".$wiki['bo_table']."&doc=".urlencode(wiki_doc($row['ns'], $row['title']));
			$row['title'] = conv_subject($row['title'], $subject_len, "…");
			$row['href'] = $href;
			$row['datetime'] = $row['date'];			
			$row['date'] = date($dateformat, strtotime($row['date']));
			$row['elapsed'] = wiki_latest_elapsedTime($row['datetime'], $g4);
			if(!$row['nick']) $row['nick'] = $row['editor'];
			if(!$row['name']) $row['name'] = $row['editor'];
			array_push($list, $row);
		}
		return $list;		
	}	

	/**
	 *
	 * 시간이 얼마나 흘렀나
	 *
	 * @param string $date 날짜문자열
	 * @param string $g4 그누보드 config
	 * @return string 흘러간 시간
	 */
	function wiki_latest_elapsedTime($date, $g4) {
	
		$timeyear = 365 * 24 * 60 * 60;
		$timemonth = 30 * 7 * 24 * 60 * 60;
		$timeweek = 7 * 24 * 60 * 60;
		$timeday = 24 * 60 * 60;
		$timehour = 60 * 60;
		$timemins = 60;
		$timeseconds = 1;
		$dates = array("", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일", "일요일");
		$argtime = strtotime($date);
		$x = $g4['server_time'] - $argtime;
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
