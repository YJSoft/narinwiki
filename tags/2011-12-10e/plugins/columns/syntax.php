<?
/**
 *
 * 나린위키 Columns 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 Columns 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxColumns extends NarinSyntaxPlugin {
	
	/**
	 *
	 * @var 칼럼모드가 시작되고 첫 칼럼이 열렸는지를 나타내는 flag
	 */
	var $opened;
	
	/**
	 *
	 * @var 칼럼모드가 시작될 때 이전의 기본 파서의 section 정보를 백업
	 */
	var $prevSections;

	/**
	 *
	 * @var 칼럼모드가 시작될 때 이전의 기본 파서의 section_level 정보를 백업
	 */	
	var $prevSectionLevel;

	/**
	 * 파싱 시작되기 전에 변수 초기화
	 */
	function init()
	{
		$this->opened = false;		
	}

	/**
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		// 칼럼모드 시작 (table)
		$parser->addLineParser(
	          $id = "wiki_columns_open",
	          $klass = $this,
	          $regx = "^&lt;columns(.*?)&gt;$",
	          $method = "columns_start");
		          
		// 새 칼럼 (td)		          
		$parser->addLineParser(
	          $id = "wiki_columns_new",
	          $klass = $this,
	          $regx = "^&lt;col(.*?)&gt;$",
	          $method = "columns_new");		
	          
		// 새 행 (tr)		          
		$parser->addLineParser(
	          $id = "wiki_columns_newrow",
	          $klass = $this,
	          $regx = "^&lt;row(.*?)&gt;$",
	          $method = "columns_new_row");			          
		          
		// 칼럼모드 종료 (/table)		          
		$parser->addLineParser(
	          $id = "wiki_columns_close",
	          $klass = $this,
	          $regx = "^&lt;\/columns&gt;$",
	          $method = "columns_close");				          
		                    
	}

	/**
	 * 
	 * 컬럼 열기
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function columns_start($matches, $params) {
		$params['parser']->stop = true;
		$this->save_section(&$params);
		return $this->get_close(&$params).'<!-- wiki_columns--><div style="overflow:auto"><table class="wiki_columns" border="0" cellspacing="0" cellpadding="0" '.strip_tags($matches[1]).'><tr>';
	}
	
	/**
	 * 
	 * 새 칼럼
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function columns_new($matches, $params) {
		$params['parser']->stop = true;
		$closeTd = ($this->opened ? '</td>' : '');
		$this->opened = true;
		return $this->get_close(&$params).$closeTd.'<td '.strip_tags($matches[1]).'>';
	}
	
	/**
	 * 
	 * 새 행
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function columns_new_row($matches, $params) {
		$params['parser']->stop = true;
		$this->opened = false;
		return $this->get_close(&$params).'</td><tr '.strip_tags($matches[1]).'>';
	}	
	

	/**
	 * 
	 * 컬럼 닫기
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function columns_close($matches, $params) {
		$params['parser']->stop = true;
		$this->opened = false;
		$closeTag = $this->get_close(&$params);
		$this->recover_section(&$params);
		return $closeTag."</td></tr></table></div><!--// wiki_columns -->";
	}

	/**
	 * 
	 * 컬럼 모드에 들어오기전의 section 과 section_level 저장
	 * 
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 */
	protected function save_section($params) {
		$plugins = &$params['plugins'];
		$default = &$plugins[array_search('wiki_default_parser', $plugins)];
		$this->prevSections = $default->sections;
		$this->prevSectionLevel = $default->section_level;
		$default->sections = array();
		$default->section_level = -1;			
	}
	
	/**
	 * 
	 * 컬럼 모드에 들어오기전의 section 과 section_level 복원
	 * 
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 */
	protected function recover_section($params)
	{
		$plugins = &$params['plugins'];
		$default = &$plugins[array_search('wiki_default_parser', $plugins)];
		$default->sections = $this->prevSections;
		$default->section_level = $this->prevSectionLevel;		
	}
	
	/**
	 * 
	 * 기본 문법 해석기의 열린 태그 닫음
	 *   - section, table, p, ul, ol 등의 태그가 열려있으면 닫아줌
	 * 
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 닫는 태그
	 */
	public function get_close($params) {
		$plugins = &$params['plugins'];
		$default = &$plugins[array_search('wiki_default_parser', $plugins)];

		$close_tag = '';
		
		if ($default->list_level>0)
		{
			$close_tag .= $default->wiki_list(false, array(), '', '', true);
		}
		if ($default->boxformat)
		{
			$close_tag .= $default->wiki_box(false, array(), true);
		}
		if ($default->pformat)
		{
			$close_tag .= $default->wiki_par(false, array(), true);
		}
		if ($default->table_opened)
		{
			$close_tag .= $default->wiki_table(false, array(), true);
		}
		
		while($pSection = array_pop($default->sections)) {
			$close_tag .= $pSection['close_tag'];
		}			
				
		return $close_tag;						
	}
}

?>