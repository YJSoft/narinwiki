<?
/**
 * 나린위키 HTML 플러그인 : 문법 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
class NarinSyntaxHtml extends NarinSyntaxPlugin {

	var $blocks = array();
	var $allow_level;
	var $allow_script_level;
	var $allow_iframe_level;
	var $writer_level = -1;
		  	
	/**
	 * 파싱 시작되기 전에 변수 초기화
	 */
	function init()
	{
		$this->blocks = array();
		$this->writer_level = -1;
	}

	/**
	 * 파서 등록
	 */	
	function register($parser)
	{
		$setting = $this->plugin_info->getPluginSetting();		
		$this->allow_level = $setting[allow_level][value];
		$this->allow_iframe_level = $setting[allow_iframe_level][value];
		$this->allow_script_level = $setting[allow_script_level][value];
		
    $parser->addBlockParser(
				$id = $this->plugin_info->getId()."_wiki_html", 
				$klass = $this, 
				$startRegx = "&lt;(html)&gt;", 
				$endRegx = "&lt;\/html&gt;",
				$method = "wiki_html");	   									   
					   				
		$parser->addEvent(EVENT_AFTER_PARSING_ALL, $this, "wiki_restore_html");				
	}

	/**
	 * 코드 하이라이터
	 */	
	public function wiki_html($matches, $params) {
		// $matches[1] = html 또는 HTML
		// $matches[2] = content		
		
		// 작성자 레벨 셋팅
		if($this->writer_level < 0) {
			if($params[view][mb_id]) {
				$writer = get_member($params[view][mb_id]);
				$this->writer_level = $writer[mb_level];
			} else $this->writer_level = 0;
		}
		
		// HTML 사용 방지
		if($this->allow_level > $this->writer_level) return $matches[2];
		
		array_push($this->blocks, $matches[2]);
		return "<".$matches[1]."></".$matches[1].">";
	}
		
	/**
	 * 코드 복구 (after line parsing)
	 */
	public function wiki_restore_html($params) {
		if($this->allow_level > $this->writer_level) return;
		
		$params[output] = preg_replace_callback('/<(html)><\/html>/i', array($this,"_restoreHtml"), $params[output]);
	}
	
	/**
	 * 개별 코드 복구
	 */
  protected function _restoreHtml($matches)
  {
    $m = $this->blocks[0];
    array_shift($this->blocks);
    
    // text -> html
    $html = wiki_html($m);
    
    // 나쁜 태그 방지
    $html = $this->bad_tag_convert($html);
    
    // script 태그 방지
    if($this->allow_script_level > $this->writer_level) $html = $this->prevent_script($html);
    
    // iframe 태그 방지
    if($this->allow_iframe_level > $this->writer_level) $html = $this->prevent_iframe($html);
    
    // 복구
    if($matches[1] == "HTML") return "<div class='wiki_box'>".$html."</div>";
    else return $html;
  }
	
	/**
	 * 나쁜 태그 변환 (g4/lib/common.lib.php 파일 참고)
	 */
	protected function bad_tag_convert($html)
	{	
	    if ($this->is_admin && $this->member[mb_id] != $this->write[mb_id]) {
	        // embed 또는 object 태그를 막지 않는 경우 필터링이 되도록 수정
	        $html = preg_replace_callback("#(\<(embed|object)[^\>]*)\>?(\<\/(embed|object)\>)?#i",
	                    create_function('$matches', 'return "<div class=\"embedx\">보안문제로 인하여 관리자 아이디로는 embed 또는 object 태그를 볼 수 없습니다. 확인하시려면 관리권한이 없는 다른 아이디로 접속하세요.</div>";'),
	                    $html);
	    }
			return $html;
	}
	
	/**
	 * script 태그 방지
	 */
	protected function prevent_script($html)
	{
		return preg_replace("/\<([\/]?)(script)([^\>]*)\>?/i", "&lt;$1$2$3&gt;", $html);		
	}
	
	/**
	 * iframe 태그 방지
	 */
	protected function prevent_iframe($html)
	{
		return preg_replace("/\<([\/]?)(iframe)([^\>]*)\>?/i", "&lt;$1$2$3&gt;", $html);		
	}	

	
}



?>