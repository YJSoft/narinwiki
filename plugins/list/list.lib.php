<?
if(!defined("_LIST_PLUGIN_")) die("잘못된 접근");

	/**
	 *
	 * 폴더내 문서 목록
	 * 
	 * @param array $wiki 위키 설정 정보
	 * @param array $g4 그누보드 설정 정보
	 * @param string $ns 폴더 경로
	 * @param string $order 정렬 기준
	 * @param boolean $recursive 하위 폴더도 읽어올 것인가
	 * @param string $dateformat 날짜 출력 포맷
	 * @param int $count 몇개의 문서를 읽어올 것인가
	 * @param int $subject_len 제목 길이를 얼마로 자를 것인가
	 * @param boolean $reverse 정렬을 ASC로 할지
	 * @param boolean $with_content 내용도 읽어올지 (HTML 캐시)
	 * @return array 문서 목록
	 */
	function wiki_list_docs($wiki, $g4, $ns, $order = 'date', $wild = '', $recursive = true, $dateformat, $count=5, $subject_len=255, $reverse = false, $with_content = false) {		
		
		$ns = mysql_real_escape_string($ns);
		$regp = ($ns == "/" ? "/" : $ns."/");
		if($ns != "/") {
			$add =	"nt.ns = '$ns' OR ";
		}		
		
		$addselect = '';
		if($with_content) {
			$addselect = ' , ct.content';
			$addjoin = " LEFT JOIN ".$wiki['cache_table']." AS ct ON ct.wr_id = wt.wr_id ";
		}
		
		$addwild = '';
		if($wild) {
			$addwild = ' AND wt.wr_subject LIKE "' . addslashes(str_replace('*', '%', $wild)) . '"';
		}
		
		
		if($recursive) $ns_match = " nt.ns LIKE '".$ns."%'";
		else $ns_match = " nt.ns = '$ns'";
		
		$possible_order = array('date'=>'ht.reg_date', 'title'=>'wt.wr_subject', 'hits'=>'wt.wr_hit', 'comments'=>'wt.wr_comment');
		$ordering = $possible_order[$order] . ($reverse ? ' ASC' : ' DESC');
		if(!$possible_order[$order]) return array();
		
		$sql = "SELECT wt.wr_id, nt.ns, wt.wr_subject AS title, ht.editor_mb_id AS editor, mt.mb_name as name, mt.mb_nick as nick, ht.reg_date as date, wt.wr_comment AS comments, wt.wr_hit AS hits $addselect
				FROM ".$wiki['ns_table']." AS nt 
				LEFT JOIN ".$wiki['nsboard_table']." AS nb ON nt.ns = nb.ns AND nt.bo_table = nb.bo_table 
				INNER JOIN ".$wiki['write_table']." AS wt ON nb.wr_id = wt.wr_id $addwild
				LEFT JOIN ".$g4['member_table']." AS mt ON mt.mb_id = wt.mb_id 
				LEFT JOIN ( SELECT wr_id, editor_mb_id, reg_date  FROM ".$wiki['history_table']." ORDER BY reg_date DESC ) AS ht 
					ON nb.wr_id = ht.wr_id 
				$addjoin
				WHERE $add $ns_match 
					AND nt.bo_table = '".$wiki['bo_table']."' 
				GROUP BY wt.wr_id 
				ORDER BY $ordering
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
			$row['elapsed'] = wiki_list_elapsedTime($row['datetime'], $g4);
			
			if($with_content) $row['content'] = cut_str(strip_tags($row['content']), 200);			
			if(!$row['nick']) $row['nick'] = $row['editor'];
			if(!$row['name']) $row['name'] = $row['editor'];
			array_push($list, $row);
		}

		return $list;		
	}	

	/**
	 *
	 * 최근 업데이트 된 문서 목록
	 * 
	 * @deprecated
	 * @param string $ns 폴더 경로
	 * @param boolean $recursive 하위 폴더도 읽어올 것인가
	 * @param string $dateformat 날짜 출력 포맷
	 * @param int $coubnt 몇개의 문서를 읽어올 것인가
	 * @param int $subject_len 제목 길이를 얼마로 자를 것인가
	 * @return array 문서 목록
	 */
	function wiki_list_recentUpdate($wiki, $g4, $ns, $recursive = true, $dateformat, $count=5, $subject_len=255, $reverse = false, $with_content = false) {		
		$ns = mysql_real_escape_string($ns);
		
		$addselect = ($with_content ? ' , wt.wr_content AS content' : '');
		
		if($recursive) $ns_match = " nt.ns LIKE '".$ns."%'";
		else $ns_match = " nt.ns = '$ns'";
		
		$sort = ($reverse ? ' ASC' : ' DESC');
		
		$sql = "SELECT nt.ns, wt.wr_subject AS title, ht.editor_mb_id AS editor, mt.mb_name as name, mt.mb_nick as nick, ht.reg_date as date,   wt.wr_comment AS comments, wt.wr_hit AS hits $addselect
						FROM ".$wiki['history_table']." AS ht 
						JOIN ( SELECT MAX(id) AS id FROM ".$wiki['history_table']." GROUP BY wr_id ORDER BY id $sort)
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
			$row['elapsed'] = wiki_list_elapsedTime($row['datetime'], $g4);
			if(!$row['nick']) $row['nick'] = $row['editor'];
			if(!$row['name']) $row['name'] = $row['editor'];
			array_push($list, $row);
		}
		return $list;		
	}	


	function wiki_list_elapsedTime($date, $g4) {
	
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
