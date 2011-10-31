<?
if (!defined('_GNUBOARD_')) exit;

/**
 * 나린위키 외부에서 사용하기 위한 함수 모음
 */

class NarinWikiLib {

	var $wiki = array();
	var $wiki_path;
	var $bo_table;
	var $write_table;
	var $g4;
	
	/**
	 * 생성자
	 * @params (string) $wiki_path 위키 경로 (g4로 부터 상대경로)
	 * @params (string) $bo_table 위키로 사용되는 bo_table
	 */
	public function __construct($wiki_path, $bo_table)
	{
		global $g4;
		$this->g4 = $g4;
		$this->wiki_path = $wiki_path;
		$this->bo_table = $bo_table;
		include $wiki_path . "/narin.config.php";	 
		$this->wiki = $wiki;
		$this->write_table = $g4['write_prefix'] . $bo_table;
	}

	/**
	 * 문법분석(parsing) 모듈 대신 사용
	 * 위키에서 parsing 해서 cache 해 놓은 내용을 읽어옴
	 * @params (number) $wr_id 게시물 id
	 */
	public function getCache($wr_id) {
		$row = sql_fetch("SELECT content FROM {$this->wiki[cache_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = $wr_id");
		return $row[content];
	}

	/**
	 * 위키 폴더 내의 문서/폴더 목록 반환
	 * @params (string) $folder 조회하고자 하는 폴더
	 * @params (boolean) $witharticle true 면 폴더 목록과 함께 문서목록 반환, false 면 폴더 목록만 반환
	 * @return (array) 목록 배열
	 */	 
	public function folderList($folder, $withArticle=true) {	
		$bo_table = $this->bo_table;	
		$wiki_path = $this->wiki[path];
		$ns_table = $this->wiki['ns_table'];
		$nsboard_table = $this->wiki['nsboard_table'];
		$write_table = $this->write_table;
		$escapedParent = mysql_real_escape_string($folder);
		$regp = ($folder == "/" ? "/" : $escapedParent."/");	
		if($folder != "/") {
			$add =	"nt.ns = '$escapedParent' OR ";
			$addSlash = "/";
		}
		
		$sql = "SELECT *  FROM $ns_table WHERE $add ns LIKE '$escapedParent%' AND ns NOT REGEXP '^$regp(.*)/' AND bo_table ='$bo_table'";
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc, wb.wr_id FROM $ns_table AS nt LEFT JOIN $nsboard_table AS nb ON nt.ns = nb.ns AND nt.bo_table = nb.bo_table LEFT JOIN $write_table AS wb ON nb.wr_id = wb.wr_id WHERE ( $add nt.ns LIKE '$escapedParent$addSlash%' AND nt.ns NOT REGEXP '^$regp(.*)/' ) AND nt.bo_table = '$bo_table'ORDER BY wb.wr_subject";
		}		
		$folders = array();
		$files = array();
		$already = array();
		$result = sql_query($sql);
		while ($row = sql_fetch_array($result))	
		{
			if($row[ns] == $folder) {
				if(!$row[doc]) continue;
				$path = ($row[ns] == "/" ? "/" : $row[ns]."/").$row[doc];
				$href = $wiki_path.'/narin.php?bo_table='.$bo_table.'&doc='.urlencode($path);
				$ilink = "[[".$path."]]";
				array_push($files, array("name"=>$row[doc], "path"=>$path, "href"=>$href, "wr_id"=>$row[wr_id], "type"=>"doc"));
			} else {				
				$href = $wiki_path.'/folder.php?bo_table='.$bo_table.'&loc='.urlencode($row[ns]);				
				$name = ereg_replace($folder."/", "", $row[ns]);
				$name = ereg_replace($folder, "", $name);			
				if($already[$name]) continue;
				$already[$name] = $name;
				array_push($folders, array("name"=>$name, "path"=>$row[ns], "href"=>$href, "type"=>"folder"));
			}		
		}
		if(count($folders)) $folders = $this->subval_asort($folders, "name");
		$list = array_merge($folders, $files);
		return $list;
	}
	
	/**
	 * 최근 업데이트 된 문서 목록
	 */
	public function recentUpdate($count=5, $subject_len=40) {
		
		$sql = "SELECT ht.id, ht.wr_id, ht.editor_mb_id, mt.mb_name, mt.mb_nick, ht.reg_date, wt.wr_subject AS docname, nt.ns 
						FROM {$this->wiki[history_table]} AS ht 
						JOIN ( SELECT MAX(id) AS id FROM {$this->wiki[history_table]} GROUP BY wr_id ORDER BY id DESC LIMIT 5 ) AS ht2 ON ht.id = ht2.id
						JOIN {$this->wiki[write_table]} AS wt ON ht.wr_id = wt.wr_id 
						JOIN {$this->wiki[nsboard_table]} AS nt ON nt.wr_id = wt.wr_id AND nt.bo_table = 'wiki' 
						JOIN {$this->g4[member_table]} AS mt ON wt.mb_id = mt.mb_id";
		
		
		$res = sql_query($sql);
		$list = array();
		while($row = sql_fetch_array($res)) {
			$href = $this->wiki[path]."/narin.php?bo_table=".$this->wiki[bo_table]."&doc=".urlencode($this->doc($row[ns], $row[docname]));
			$row['docname'] = conv_subject($row['docname'], $subject_len, "…");
			$row['href'] = $href;
			array_push($list, $row);
		}
		return $list;		
	}	
	
	/**
	 * 최근 변경 내역 목록
	 */	
	public function recentChanges($count = 5) {
		$sql = "SELECT * FROM {$this->wiki[changes_table]} WHERE bo_table = '{$this->wiki[bo_table]}' ORDER BY id DESC LIMIT $count";		
		$list = array();
		$res = sql_query($sql);
		while($row = sql_fetch_array($res)) {
			$target = urlencode($row[target]);
			if($row[target_type] == "DOC") {
				$row[view_href] = $this->wiki[path]."/narin.php?bo_table=".$this->wiki[bo_table]."&doc=".$target;
			} else if($row[target_type] == "FOLDER") {
				$row[view_href] = $this->wiki[path]."/folder.php?bo_table=".$this->wiki[bo_table]."&loc=".$target;		
			}
			array_push($list, $row);
		}
		return $list;
	}
	
	/**
	 * 연관배열의 키 순으로 정렬 (asort)
	 * @params (array) $a 정렬할 배열
	 * @params (string) $subkey 배열의 키값
	 */
	protected function subval_asort($a,$subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		asort($b);
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	}
	
	/**
	 * 연관배열의 키 순으로 정렬 (sort)
	 * @params (array) $a 정렬할 배열
	 * @params (string) $subkey 배열의 키값
	 */
	protected function subval_sort($a,$subkey) {
		$c = subval_asort($a, $subkey);
		$c = array_reverse($c);
		return $c;
	}
	
	/** 
	 * 폴더명과 문서명 합치기
	 */
	protected function doc($ns, $docname) {
		return ($ns == "/" ? "" : $ns ) . "/" . $docname;
	}	
	
	/**
	 * 몇초전, 몇분전, 지난월요일 ... 식으로 날짜 변환
	 */
	public function  getElapsedTime($date, $f = 'h:ma M. j Y T') {
	
		$timeyear = 365 * 24 * 60 * 60;
		$timemonth = 30 * 7 * 24 * 60 * 60;
		$timeweek = 7 * 24 * 60 * 60;
		$timeday = 24 * 60 * 60;
		$timehour = 60 * 60;
		$timemins = 60;
		$timeseconds = 1;
		$dates = array("", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일", "주일");
		$argtime = strtotime($date);
		$x = time() - $argtime;
	
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



// 최신글 추출
function narin_latest($skin_dir="", $wiki_path, $bo_table, $rows=10, $subject_len=40, $options="")
{
    global $g4;

		// 나린위키 라이브러리 객체 생성
		$narinLib = new NarinWikiLib($wiki_path, $bo_table);

    if ($skin_dir)
        $latest_skin_path = "$g4[path]/skin/latest/$skin_dir";
    else
        $latest_skin_path = "$g4[path]/skin/latest/basic";



    $sql = " select * from $g4[board_table] where bo_table = '$bo_table'";
    $board = sql_fetch($sql);

    $tmp_write_table = $g4['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
		
		$list = $narinLib->recentUpdate($rows, $subject_len=40);
    
    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
} 

?>