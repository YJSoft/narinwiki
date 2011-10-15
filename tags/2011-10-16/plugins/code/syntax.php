<?
include_once "geshi.php";

class NarinSyntaxCode extends NarinSyntaxPlugin {
	
	var $blocks = array();
	  	
	/**
	 * 파싱 시작되기 전에 변수 초기화
	 */
	function init()
	{
		$this->blocks = array();
	}

	/**
	 * 파서 등록
	 */	
	function register($parser)
	{
    $parser->addBlockParser(    
				$id = $this->plugin_info->getId()."_wiki_code", 
				$klass = $this, 
				$startRegx = "&lt;code\s?([\w]{0,})&gt;", 
				$endRegx = "&lt;\/code&gt;",
				$method = "wiki_code");	     									   
		
		$parser->addLineParser(
				$id = $this->plugin_info->getId()."_wiki_pre", 
				$klass = $this, 
				$regx = "^(&lt;[\/]?code&gt;)(.*?)", 
				$method = "wiki_pre");	     							   		
		
		$parser->addEvent(EVENT_AFTER_PARSING_ALL, $this, "wiki_restore_code");				
	}

	/**
	 * 코드 하이라이터
	 */	
	public function wiki_code($matches, $params) {
		// $matches[1] = language
		// $matches[2] = code		
		//print_r2($matches);	
		if(!trim($matches[1])) {
			array_push($this->blocks, $matches[2]);
			return "<code></code>";
		}
		$geshi = new GeSHi(wiki_html($matches[2]), trim($matches[1]));
		$highlighted_code = trim(preg_replace('!^<pre[^>]*>|</pre>$!','',$geshi->parse_code()),"\n\r");
		array_push($this->blocks, $highlighted_code);
		return "<code></code>";
	}
	
	/**
	 * 코드 복구 (after line parsing)
	 */
	public function wiki_restore_code($params) {
		$params[output] = preg_replace_callback('/<code><\/code>/i', array($this,"_restoreCode"), $params[output]);
	}
	
	/**
	 * 개별 코드 복구
	 */
  protected function _restoreCode($matches)
  {
    $m = $this->blocks[0];
    array_shift($this->blocks);
    // $m 앞뒤에 &nbsp;\n 문자 제거;;;;
    $m = preg_replace("/^(&nbsp;\s)/i", "", $m); 
    $m = preg_replace("/(&nbsp;)$/i", "", $m); 
    return "\n<pre class='wiki_code'>".$m."</pre><!--// code -->\n";
  }
}



?>