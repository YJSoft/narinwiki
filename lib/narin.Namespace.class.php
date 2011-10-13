<?
if (!defined('_GNUBOARD_')) exit;

class NarinNamespace extends NarinClass {
	
	var $fromDoc;
	var $toDoc;
	
	protected $cache = array();
			

	/**
	 * Add namespace
	 */
	function addNamespace($ns) {
		$nslists = $this->_getWikiNamespaceArray($ns);		
		$bo_table = $this->wiki[bo_table];
		foreach($nslists as $k => $v) {
			if($v) $v = mysql_real_escape_string($v);			
			if($v == "") $v = "/";
			if($k < count($nslists)-1) $has_child = 1;
			else $has_child = 0;
			
			$ns = sql_fetch("SELECT * FROM {$this->wiki[ns_table]} WHERE bo_table = '$bo_table' AND ns = '$v'");
			if($ns) {
				if(!$ns[has_child] && $has_child) {
					sql_query("UPDATE {$this->wiki[ns_table]} SET has_child = 1 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				} else if($ns[has_child] && !$has_child) {
					sql_query("UPDATE {$this->wiki[ns_table]} SET has_child = 0 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				}
			} else sql_query("INSERT INTO {$this->wiki[ns_table]} VALUES ('$v', '{$this->wiki[bo_table]}', '1', $has_child, '')");
		}
	}
	
	/**
	 * Update namespace
	 */
	function updateNamespace($srcNS, $dstNS)
	{
		if($srcNS == "/") return;
		$wikiArticle = wiki_class_load("Article");

		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedDstNS = mysql_real_escape_string($dstNS);
		
		// $srcNS 의 하위 ns 를 읽어온다
		$list = sql_list("SELECT * FROM {$this->wiki[ns_table]} WHERE ns like '$escapedSrcNS/%' AND bo_table='{$this->wiki[bo_table]}'");		
		
		foreach($list as $k=>$v) {
			// $srcNS 의 하위 ns 를 업데이트한다.
			$to = preg_replace("/^(".preg_quote($srcNS, "/").")(.*?)/", $dstNS, $v[ns]);
			$this->updateNamespace($v[ns], $to);
		}
		
		// $srcNS 를 업데이트한다.
		$this->_updateNamespace($wikiArticle, $srcNS, $dstNS);		
	}
	
	function _updateNamespace($wikiArticle, $srcNS, $toNS)
	{
		$wikiHistory = wiki_class_load("History");
		
		// $srcNS 에 포함된 documents 목록을 읽어온다.
		$list = $this->getList($srcNS, $withArticle = true);

		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedToNS = mysql_real_escape_string($toNS);
		
		// $srcNS / $document[] 에 대한 백 링크들을 업데이트한다.
		for($i=0; $i<count($list); $i++) {
			if($list[$i][type] == 'folder')	continue;
			$wikiArticle->fromDoc = $fromDoc = $list[$i][path];
			$wikiArticle->toDoc = preg_replace("/^(".preg_quote($srcNS, "/").")(.*?)/", $toNS, $fromDoc);
			
			// 백링크 업데이트
			$backLinks = $wikiArticle->getBackLinks($fromDoc, $includeSelf=true);
			
			for($k=0; $k<count($backLinks); $k++) {

				$content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$wikiArticle, 'wikiLinkReplace'), $backLinks[$k][wr_content]));			
				
				// 문서 이력에 백업
				$wikiHistory->update($backLinks[$i][wr_id], stripcslashes($content), $this->member[mb_id], "폴더명 변경에 따른 자동 업데이트");
				$wikiArticle->shouldUpdateCache($backLinks[$i][wr_id], 1);
				
				sql_query("UPDATE {$this->wiki[write_table]} SET wr_content = '$content' WHERE wr_id = {$backLinks[$k][wr_id]}");
			}			
		}

		sql_query("UPDATE {$this->wiki[ns_table]} SET ns = '$escapedToNS' WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$escapedSrcNS'", false);				
		sql_query("UPDATE {$this->wiki[nsboard_table]} SET ns = '$escapedToNS' WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$escapedSrcNS'", false);
		
		$this->addNamespace($toNS);
		$this->checkAndRemove($srcNS);
	}		
	
	
	public function setTemplate($ns, $tpl) {
		$ns = mysql_real_escape_string($ns);
		$tpl = mysql_real_escape_string($tpl);

		sql_query("UPDATE {$this->wiki[ns_table]} SET tpl = '$tpl' WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$ns'");				
	}
	
	/**
	 * Update access level
	 */
	function updateAccessLevel($ns, $level)
	{
		if($ns) $ns = mysql_real_escape_string($ns);			
		if($level) $level = mysql_real_escape_string($level);			
		sql_query("UPDATE {$this->wiki[ns_table]} SET ns_access_level = '$level' WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$ns'", false);		
	}
	
	
	/**
	 * Add namespace
	 */
	function checkAndRemove($ns) {		

		if($ns == "/") return;
	
		$escapedNS = mysql_real_escape_string($ns);
		
		// load ns
		$namespace = sql_fetch("SELECT * FROM {$this->wiki[ns_table]} AS ns LEFT JOIN {$this->wiki[nsboard_table]} AS nb ON ns.bo_table = nb.bo_table AND ns.ns = nb.ns WHERE ns.bo_table = '{$this->wiki[bo_table]}' AND ns.ns = '$escapedNS'");
	
		// if there's no document
		if(!$namespace[wr_id]) {
			
			// if there's no child, delete
			$child = sql_fetch("SELECT * FROM {$this->wiki[ns_table]} WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child[ns]) {				
				sql_query("DELETE FROM {$this->wiki[ns_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$escapedNS'");
				
				// call recursivly about parent
				$this->checkAndRemove(wiki_get_parent_path($ns));
			}
		} else {
			// if there's no child, set has_child false
			$child = sql_fetch("SELECT * FROM {$this->wiki[ns_table]} WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child[ns]) {				
				sql_query("UPDATE {$this->wiki[ns_table]} SET has_child = 0 WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$escapedNS'");
			}			
		}
	}	

	/**
	 * Return namespaces array
	 */
	function namespaces($parent = "/", $withArticle=true) {
		if($this->cache[namespaces][$parent][$withArticle]) return $this->cache_ns[namespaces][$parent][$withArticle];
		
		$escapedParent = mysql_real_escape_string($parent);
		
		$sql = "SELECT ns FROM {$this->wiki[ns_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND ns LIKE '{$escapedParent}%' ORDER BY ns";		
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc FROM {$this->wiki[ns_table]} AS nt LEFT JOIN {$this->wiki[nsboard_table]} AS nb ON nt.ns = nb.ns LEFT JOIN {$this->wiki[write_table]} AS wb ON nb.wr_id = wb.wr_id WHERE nt.bo_table = '{$this->wiki[bo_table]}' AND nt.ns LIKE '{$escapedParent}%' ORDER BY nt.ns";
		}		
		$result = sql_query($sql);
		$list = array();
		while ($row = sql_fetch_array($result))	
		{
			$full = ($row[ns] == "/" ? "/" :  $row[ns]);
			if($withArticle) $full = ($row[ns] == "/" ? "/" :  $row[ns] . "/") . $row[doc];
			$real_path = $full;
			if($parent == "/") {
				$full = "/".$this->wiki[tree_top]. $full;
			}
			$list[$full] = $real_path;
		}				
		
		$this->cache[namespaces][$parent][$withArticle] = $list;
		return $list;
	}
	
	/**
	 * Return namespaces array
	 */
	function inNamespace($parent = "/", $withArticle=true) {		
		if($this->cache[inNamespace][$parent][$withArticle]) return $this->cache[inNamespace][$parent][$withArticle];
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($escapedParent == "/" ? "/" : $escapedParent."/");		
		$sql = "SELECT *  FROM {$this->wiki[ns_table]} WHERE ns LIKE '$escapedParent%' AND ns NOT REGEXP '^$regp(.*)/' AND bo_table ='{$this->wiki[bo_table]}'";
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc FROM {$this->wiki[ns_table]} AS nt LEFT JOIN {$this->wiki[nsboard_table]} AS nb ON nt.ns = nb.ns LEFT JOIN {$this->wiki[write_table]} AS wb ON nb.wr_id = wb.wr_id WHERE nt.ns = '$escapedParent' OR nt.ns LIKE '$escapedParent%' AND nt.ns NOT REGEXP '^$regp(.*)/' AND nt.bo_table = '{$this->wiki[bo_table]}' ORDER BY nt.ns";
		}		
		$result = sql_query($sql);
		$list = array();
		while ($row = sql_fetch_array($result))	
		{
			$full = ($row[ns] == "/" ? "/" :  $row[ns]);
			if($withArticle) $full = ($row[ns] == "/" ? "/" :  $row[ns] . "/") . $row[doc];
			$real_path = $full;
			if($parent == "/") {
				$full = "/".$this->wiki[tree_top]. $full;
			}
			
			$list[$full] = $real_path;
		}
		$this->cache[inNamespace][$parent][$withArticle] = $list;
		return $list;
	}	
	
	/**
	 * Return namespace
	 */
	function get($ns) {
		if($this->cache[get][$ns]) return $this->cache[get][$ns];
		$ns = mysql_real_escape_string($ns);		
		$row = sql_fetch("SELECT * FROM {$this->wiki[ns_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND ns = '$ns'");
		$this->cache[get][$ns] = $row;
		return $row;
	}
	
	/**
	 * Return namespaces and files array
	 */
	function getList($parent = "/", $withArticle=true) {		
		if($this->cache[getList][$parent][$withArticle]) return $this->cache[getList][$parent][$withArticle];
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($parent == "/" ? "/" : $escapedParent."/");	
		if($parent != "/") {
			$add =	"nt.ns = '$escapedParent' OR ";
			$addSlash = "/";
		}
		
		$sql = "SELECT *  FROM {$this->wiki[ns_table]} WHERE $add ns LIKE '$escapedParent%' AND ns NOT REGEXP '^$regp(.*)/' AND bo_table ='{$this->wiki[bo_table]}'";
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc, wb.wr_id FROM {$this->wiki[ns_table]} AS nt LEFT JOIN {$this->wiki[nsboard_table]} AS nb ON nt.ns = nb.ns AND nt.bo_table = nb.bo_table LEFT JOIN {$this->wiki[write_table]} AS wb ON nb.wr_id = wb.wr_id WHERE $add nt.ns LIKE '$escapedParent$addSlash%' AND nt.ns NOT REGEXP '^$regp(.*)/' AND nt.bo_table = '{$this->wiki[bo_table]}' ORDER BY wb.wr_subject";
		}		
		
		$folders = array();
		$files = array();
		$already = array();
		$result = sql_query($sql);
		while ($row = sql_fetch_array($result))	
		{
			if($row[ns] == $parent) {
				if(!$row[doc]) continue;
				$path = ($row[ns] == "/" ? "/" : $row[ns]."/").$row[doc];
				$href = $this->wiki[path].'/narin.php?bo_table='.$this->wiki[bo_table].'&doc='.urlencode($path);
				$ilink = "[[".$path."]]";
				array_push($files, array("name"=>$row[doc], "path"=>$path, "href"=>$href, "internal_link"=>$ilink, "wr_id"=>$row[wr_id], "type"=>"doc"));
			} else {				
				$href = $this->wiki[path].'/folder.php?bo_table='.$this->wiki[bo_table].'&loc='.urlencode($row[ns]);
				$name = ereg_replace($parent."/", "", $row[ns]);
				$name = ereg_replace($parent, "", $name);			
				if($already[$name]) continue;
				$already[$name] = $name;
				$ilink = "[[folder=".$row[ns]."]]";
				array_push($folders, array("name"=>$name, "path"=>$row[ns], "href"=>$href, "internal_link"=>$ilink, "type"=>"folder"));
			}		
		}
		if(count($folders)) $folders = subval_asort($folders, "name");
		$list = array_merge($folders, $files);
		$this->cache[getList][$parent][$withArticle] = $list;
		return $list;
	}		
	
	/**
	 * Remove all empty namespaces
	 */
	function removeAllEmptyNS()
	{
		$res = sql_query("SELECT * FROM {$this->wiki[ns_table]} WHERE has_child = 0 AND (ns, bo_table) NOT IN ( SELECT ns, bo_table FROM {$this->wiki[nsboard_table]} WHERE bo_table = '{$this->wiki[bo_table]}')");
		while($row = sql_fetch_array($res)) {
			$this->checkAndRemove($row[ns]);
		}
	}
	
	
	/**
	 * Return namespace list from string
	 * e.g.> if ( $ns = "park/chongmyung" )
	 *       return array("park", "park/chongmyung")
	 */
	function _getWikiNamespaceArray($ns) {
		$array = explode("/", $ns);
		$list = array();
		$node = "";
		for($i=0; $i<count($array); $i++) {
			if(!$i) $node = $array[$i];
			else $node = $node . "/" . $array[$i];
			array_push($list, $node);
		}
		return $list;
	}

}

?>