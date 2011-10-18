<?
if (!defined('_GNUBOARD_')) exit;

class NarinArticle extends NarinClass {
	
	var $fromDoc;
	var $toDoc;
	var $nowikis = array();	
	
	var $cache = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
  	
  	parent::__construct();
		$this->namespace = wiki_class_load("Namespace");
		$this->history = wiki_class_load("History");
	}

	/**
	 * Return article
	 */
	public function getArticle($ns, $docname, $file='', $line='')
	{	    
		if($this->cache[getArticle][$ns][$docname]) return $this->cache[getArticle][$ns][$docname];
		$e_ns = mysql_real_escape_string($ns);
		$e_docname = mysql_real_escape_string($docname);
		$sql = "SELECT *, wb.wr_subject AS doc FROM {$this->wiki[write_table]} AS wb LEFT JOIN {$this->wiki[nsboard_table]} AS nt ON wb.wr_id = nt.wr_id WHERE nt.bo_table = '{$this->wiki[bo_table]}' AND nt.ns = '$e_ns' AND wb.wr_subject = '$e_docname'";
		
		$row = sql_fetch($sql);
		$this->cache[getArticle][$ns][$docname] = &$row;
		$this->cache[getArticleById][$wr_id] = &$row;
		return $row;
	}
	
	/**
	 * Exists article ?
	 */
	public function exists($ns, $docname) {
		$ns = mysql_real_escape_string($ns);
		$docname = mysql_real_escape_string($docname);
		return sql_fetch("SELECT id FROM {$this->wiki[nsboard_table]} AS nb LEFT JOIN {$this->wiki[write_table]} AS wt ON nb.wr_id = wt.wr_id WHERE nb.bo_table = '{$this->wiki[bo_table]}' AND nb.ns = '$ns' AND wt.wr_subject = '$docname'");
	}

	/**
	 * Return article
	 */
	function getArticleById($wr_id)
	{
		if($this->cache[getArticleById][$wr_id]) {
			return $this->cache[getArticleById][$wr_id];
		}
		$wr_id = mysql_real_escape_string($wr_id);
		$sql = "SELECT *, wb.wr_subject AS doc FROM {$this->wiki[write_table]} AS wb LEFT JOIN {$this->wiki[nsboard_table]} AS nt ON wb.wr_id = nt.wr_id WHERE nt.bo_table = '{$this->wiki[bo_table]}' AND wb.wr_id = '$wr_id'";
		
		$row = sql_fetch($sql);
		$this->cache[getArticle][$row[ns]][$row[doc]] = &$row;
		$this->cache[getArticleById][$wr_id] = &$row;
		
		return $row;		
	}
	
	function shouldUpdateCache($wr_id, $value) {
		$sql = "UPDATE {$this->wiki[nsboard_table]} SET should_update_cache = '$value' WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr_id'";
		sql_query($sql);
	}
	
	/**
	 * Return front page
	 */
	function getFrontPage() {
		return $this->getArticle("/", $this->board[bo_subject], __FILE__, __LINE__);
	}
	
	/**
	 * Return back links
	 */
	function getBackLinks($doc, $includeSelf = false)
	{
		if($this->cache[getBackLinks][$doc][$includeSelf]) return $this->cache[getBackLinks][$doc][$includeSelf];
		$escapedDoc = mysql_real_escape_string($doc);
		$list = array();
		$sql = "SELECT *, wb.wr_subject AS doc from {$this->wiki[write_table]} AS wb LEFT JOIN {$this->wiki[nsboard_table]} AS nt ON wb.wr_id = nt.wr_id WHERE nt.bo_table= '{$this->wiki[bo_table]}' AND ( wb.wr_content LIKE '%[[".$escapedDoc."]]%' OR wb.wr_content LIKE '%[[".$escapedDoc."|%') AND wb.wr_content NOT LIKE '%[[".$escapedDoc."/%'";	
			
		$result = sql_query($sql);
		while($row = sql_fetch_array($result))
		{
			if(!$this->hasInternalLink($row[wr_content], $doc)) {
				continue;
			}
			$bdoc = ($row[ns] == "/" ? "/" : $row[ns] . "/") . $row[doc];
			if(!$includeSelf && $bdoc == $doc) continue;
			
			$row[href] = $this->wiki[path]."/narin.php?bo_table={$this->wiki[bo_table]}&doc=".urlencode($bdoc);
			array_push($list, $row);
		}
		
		$this->cache[getBackLinks][$doc][$includeSelf] = $list;
		return $list;
	}
	
	/**
	 * Delete article
	 */
	function deleteArticleById($wr_id)
	{
		$wr_id = mysql_real_escape_string($wr_id);
		$write = $this->getArticleById($wr_id);
		if(!$write) return;
				
		$sql = "DELETE FROM {$this->wiki[nsboard_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr_id'";
		sql_query($sql);
		
		$this->namespace->checkAndRemove($write[ns]);		
	}	
			
	/**
	 * Add article
	 * @params $doc document name with namespace e.g. /chongmyungpark/gnuboard/wiki/start page
	 */
	function addArticle($doc, $wr_id)
	{
		list($ns, $docname, $fullname) = wiki_page_name($doc, $strip=false);
		$this->namespace->addNamespace($ns);
		if($ns) $ns = mysql_real_escape_string($ns);
		if($docname) $docname = mysql_real_escape_string($docname);
		if($fullname) $fullname = mysql_real_escape_string($fullname);
		if($wr_id) $wr_id = mysql_real_escape_string($wr_id);		
		$sql = "INSERT INTO {$this->wiki[nsboard_table]} VALUES ('', '{$this->wiki[bo_table]}', $wr_id, '$ns', '1', '', '')";
		sql_query($sql);
		$sql = "UPDATE {$this->wiki[write_table]} SET wr_subject = '$docname' WHERE wr_id = $wr_id";
		sql_query($sql);
	}
	
	/**
	 * Update article that has wr_id = $wr_id
	 * @params $doc document name with namespace e.g. /chongmyungpark/gnuboard/wiki/start page
	 */
	function updateArticle($toDoc, $wr_id)
	{
		list($ns, $docname, $fullname) = wiki_page_name($toDoc, $strip=false);	
		$this->namespace->addNamespace($ns);
		if($ns) $ns = mysql_real_escape_string($ns);
		if($docname) $docname = mysql_real_escape_string($docname);
		if($fullname) $fullname = mysql_real_escape_string($fullname);
		if($wr_id) $wr_id = mysql_real_escape_string($wr_id);		
		$sql = "UPDATE {$this->wiki[nsboard_table]} SET ns='$ns' WHERE bo_table='{$this->wiki[bo_table]}' AND wr_id='$wr_id'";
		sql_query($sql);
		$sql = "UPDATE {$this->wiki[write_table]} SET wr_subject = '$docname' WHERE wr_id = $wr_id";
		sql_query($sql);
	}	
	
	/**
	 * Move folder that has wr_id = $wr_id
	 */	
	function moveDoc($fromDoc, $toDoc, $wr_id)
	{	
		list($toNS, $toDocName, $toFullName) = wiki_page_name($toDoc, $strip=false);		
		list($fromNS, $fromDocName, $fromFullName) = wiki_page_name($fromDoc, $strip=false);		
		$this->fromDoc = $fromFullName;
		$this->toDoc = $toFullName;				
		
		// 이미 존재한다면 이동하지 않음
		$ex = $this->getArticle($toNS, $toDocName, __FILE__, __LINE__);
		if($ex) return;
		
		$this->updateArticle($toDoc, $wr_id);
		
		// 백링크 업데이트
		$backLinks = $this->getBackLinks($fromDoc, $includeSelf=true);
		for($i=0; $i<count($backLinks); $i++) {
			$content = $backLinks[$i][wr_content];
			$content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$this, 'wikiLinkReplace'), $content));	
			/* FIXME : <pre></pre>, <nowiki></nowiki> 에 있는거는 제외하고자 했으나 something wrong
	    $content = preg_replace_callback('/(<pre>)([\s\S]*)(<\/pre>)/i',array($this,"_saveNoWiki"),$content); 
	    $content = preg_replace_callback('/(<nowiki>)(.*?)(<\/nowiki>)/i',array($this,"_saveNoWiki"),$content);    			
			$content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$this, 'wikiLinkReplace'), $content));	
			$content = preg_replace_callback('/<nowiki><\/nowiki>/i', array($this,"_restoreNoWiki"),$content);
			*/
			
			// 문서 이력에 백업
			$this->history->update($backLinks[$i][wr_id], stripcslashes($content), $this->member[mb_id], "문서명 업데이트로 인한 백링크 자동 업데이트");
			$this->shouldUpdateCache($backLinks[$i][wr_id], 1);
			
			sql_query("UPDATE {$this->wiki[write_table]} SET wr_content = '$content' WHERE wr_id = {$backLinks[$i][wr_id]}");
		}
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("MOVE_DOC", array("from"=>$fromFullName, "to"=>$toFullName));
		
		$this->namespace->checkAndRemove($fromNS);

	}	
	
	/**
	 * <nowiki></nowiki> 내용 저장
	 * @param array $matches 
	 */
  protected function _saveNoWiki($matches)
  {
    array_push($this->nowikis,$matches[0]);
    return "<nowiki></nowiki>";
  }
  
	/**
	 * <nowiki></nowiki> 내용 복구
	 * @param array $matches 
	 */
  protected function _restoreNoWiki($matches)
  {
    $m = $this->nowikis[0];
    array_shift($this->nowikis);
    return $m;
  }	
	
	/**
	 * Replace wiki link (called by $this->moveDoc())
	 */
	function wikiLinkReplace($matches) {
		$sp = explode("|", $matches[2]);		
		if(count($sp) > 1) {
			$doc = $sp[0];
			$opt = "|".$sp[1];
		} else {
			$doc = $sp[0];
			$opt = "";			
		}		
		if($doc != $this->fromDoc) return "[[".$matches[2]."]]";		
		return "[[{$this->toDoc}$opt]]";
	}	
	
	/**
	 * Update access level
	 */
	function updateLevel($doc, $access_level, $edit_level)
	{
		list($ns, $docname, $fullname) = wiki_page_name($doc, $strip=false);
		$wr = $this->getArticle($ns, $docname, __FILE__, __LINE__);
		if(!$wr) die("No Article");		
		$access_level = mysql_real_escape_string($access_level);			
		$edit_level = mysql_real_escape_string($edit_level);			
		sql_query("UPDATE {$this->wiki[nsboard_table]} SET access_level = '$access_level', edit_level = '$edit_level' WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id = '$wr[wr_id]'");		
	}	
	
	/**
	 * Remove all docs that not exists within write_table
	 */
	function removeAllNotExistsDoc()
	{
		sql_query("DELETE FROM {$this->wiki[nsboard_table]} WHERE bo_table = '{$this->wiki[bo_table]}' AND wr_id NOT IN (  SELECT wr_id FROM {$this->wiki[write_table]})");
	}
	
	/**
	 * Has internal link in the content (without nowiki)
	 */
function hasInternalLink($content, $doc)
{
	$text = preg_replace('/<nowiki_block>([\s\S]*)<\/nowiki_block>/i', "",$content); 
	$text = preg_replace('/<nowiki>(.*?)<\/nowiki>/i', "",$text); 
	$regx = '/\[\['.preg_quote($doc, '/').'(.*?)\]\]/';
	return preg_match($regx, $text);
}
}

?>