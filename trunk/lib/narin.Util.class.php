<?php
	
class NarinUtil extends NarinClass
{
	var $nowikis;
	var $current_pattern_id;
	var $nowiki_patterns;

	public function __construct() {	
		parent::__construct();
		$this->nowiki_patterns = array(
				array("start_regex"=>"<pre>", "end_regex"=>"<\/pre>", "id"=>"pre"),
				array("start_regex"=>"<nowiki>", "end_regex"=>"<\/nowiki>", "id"=>"nowiki"),
				array("start_regex"=>"<code(.*?)>", "end_regex"=>"<\/code>", "id"=>"code")
		);
	}
	
	//
	// '/' 로 시작하지 않는 문서 제목에 현재 폴더 경로를 덭붙임
	//
	public function wiki_fix_internal_link($wr_content) {
		
		$nowikis = array();			
		$content = $this->nowiki_backup($wr_content, $nowikis);							
		$content = preg_replace_callback('/(\[\[)(.*?)(\]\])/', array($this, 'wiki_add_folder_to_link'), $content);			
		$content = $this->nowiki_restore($content, $nowikis);

		return $content;
	}
	
	// nowiki, pre, code 태그를 제외한 내용 반환
	public function no_nowiki_content($wr_content) {
		$nowikis = array();	
		$content = $this->nowiki_backup($wr_content, $nowikis);
		return $content;
	}

	//
	// '/' 로 시작하지 않는 문서 제목에 현재 폴더 경로를 덭붙임
	//
	protected function wiki_add_folder_to_link($matches) {
		
		$link = $matches[2];
		if(preg_match("/^#/", $link)) {
			return $matches[0];
		}
		$prefix = "[[";
		$subfix = "]]";
		
		if(!preg_match("/^\//", $link))	return $prefix.($this->folder == "/" ? "" : $this->folder)."/".$link.$subfix;
		else return $matches[0];
	}	
		
	public function nowiki_backup($content, &$nowikis) {
		$this->nowikis = &$nowikis;
    foreach($this->nowiki_patterns as $pattern) {
    	$this->current_pattern_id = $pattern[id];
    	$regex = "/".$pattern[start_regex]."(.*?)".$pattern[end_regex] . "/si";
    	$content = preg_replace_callback($regex, array($this,"_saveNoWikiBlock"), $content);     
    }    
    return $content;
	}

  protected function _saveNoWikiBlock($matches)
  {
  	$id = $this->current_pattern_id;
  	if(!isset($this->nowikis[$id])) $this->nowikis[$id] = array();
    array_push($this->nowikis[$id], $matches[0]);
    return "<$id></$id>";
  }  

	
	public function nowiki_restore($content, &$nowikis) {
		
		$this->nowikis = &$nowikis;
    asort($this->nowiki_patterns);
    foreach($this->nowiki_patterns as $pattern) {
    	$this->current_pattern_id = $id = $pattern[id];
    	$regex = "/<".$pattern[id]."><\/".$pattern[id] . ">/i";
    	$content = preg_replace_callback($regex, array($this,"_restoreNoWikiBlock"), $content);		  	
    }  
    asort($this->nowiki_patterns);
    return $content;
	}
	
	
  protected function _restoreNoWikiBlock($matches)
  {  	  	
  	$id = $this->current_pattern_id;
  	$nowiki = $this->nowikis[$id][0];
    array_shift($this->nowikis[$id]);
		return $nowiki;
  }  

}
?>