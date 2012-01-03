<?
/**
 *
 * 나린위키 HTML 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 HTML 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxHtml extends NarinSyntaxPlugin {

	/**
	 *
	 * @var array HTML 코드 블럭을 임시로 저장할 버퍼
	 */
	var $blocks = array();

	/**
	 *
	 * @var int HTML 사용 권한
	 */
	var $allow_level;

	/**
	 *
	 * @var int script 태그 사용 권한
	 */
	var $allow_script_level;

	/**
	 *
	 * @var int iframe 태그 사용 권한
	 */
	var $allow_iframe_level;

	/**
	 *
	 * @var int 작성자 회원 level
	 */
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
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		$setting = $this->plugin_info->getPluginSetting();
		$this->allow_level = $setting['allow_level']['value'];
		$this->allow_iframe_level = $setting['allow_iframe_level']['value'];
		$this->allow_script_level = $setting['allow_script_level']['value'];

		$parser->addBlockParser(
		$id = $this->plugin_info->getId()."_wiki_html",
		$klass = $this,
		$startRegx = "&lt;(html)&gt;",
		$endRegx = "&lt;\/html&gt;",
		$method = "wiki_html");

		$parser->addEvent(EVENT_AFTER_PARSING_ALL, $this, "wiki_restore_html");
	}

	/**
	 * 
	 * HTML 변환
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function wiki_html($matches, $params) {
		// $matches[1] = html 또는 HTML
		// $matches[2] = content

		// 작성자 레벨 셋팅
		if($this->writer_level < 0) {
			if($params['view']['mb_id']) {
				$writer = get_member($params['view']['mb_id']);
				$this->writer_level = $writer['mb_level'];
			} else $this->writer_level = 0;
		}

		// HTML 사용 방지
		if($this->allow_level > $this->writer_level) return $matches[2];

		array_push($this->blocks, $matches[2]);
		return "<".$matches[1]."></".$matches[1].">";
	}

	/**
	 *
	 * 코드 복구 (after line parsing)
	 * 
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 */	
	public function wiki_restore_html($params) {
		if($this->allow_level > $this->writer_level) return;

		$params['output'] = preg_replace_callback('/<(html)><\/html>/i', array($this,"_restoreHtml"), $params['output']);
	}

	/**
	 *
	 * 코드 복구 (실제루틴)
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @return string HTML로 변환 된 코드
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
	 * 
	 * 나쁜 태그 변환 (g4/lib/common.lib.php 파일 참고)
	 * 
	 * @param string $html HTML 데이터
	 * @return 변환된 HTML
	 */
	protected function bad_tag_convert($html)
	{
		if ($this->is_admin && $this->member['mb_id'] != $this->write['mb_id']) {
			// embed 또는 object 태그를 막지 않는 경우 필터링이 되도록 수정
			$html = preg_replace_callback("#(\<(embed|object)[^\>]*)\>?(\<\/(embed|object)\>)?#i",
			create_function('$matches', 'return "<div class=\"embedx\">보안문제로 인하여 관리자 아이디로는 embed 또는 object 태그를 볼 수 없습니다. 확인하시려면 관리권한이 없는 다른 아이디로 접속하세요.</div>";'),
			$html);
		}
		return $html;
	}

	/**
	 * 
	 * script 태그 방지
	 * 
	 * @param string $html HTML 데이터
	 * @return 변환된 HTML
	 */
	protected function prevent_script($html)
	{
		return preg_replace("/\<([\/]?)(script)([^\>]*)\>?/i", "&lt;$1$2$3&gt;", $html);
	}

	/**
	 * iframe 태그 방지
	 * 
	 * @param string $html HTML 데이터
	 * @return 변환된 HTML
	 */
	protected function prevent_iframe($html)
	{
		return preg_replace("/\<([\/]?)(iframe)([^\>]*)\>?/i", "&lt;$1$2$3&gt;", $html);
	}

}

?>
