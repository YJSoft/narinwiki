<?
/**
 *
 * 나린위키 최근문서 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 최근문서 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxLatest extends NarinSyntaxPlugin {
	 
	/**
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		$parser->addVariableParser(
          $id = "wiki_latest",
          $klass = $this,
          $startRegx = "latest=",	// latest=
          $endRegx = "((\?)(.*?))?",	// 파라미터 ? 다음 문자열 (optional)
          $method = "wiki_latest");
	}

	/**
	 * 
	 * HTML 변환
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function wiki_latest($matches, $params) {
		// $matches[1] = latest=
		// $matches[1] = 경로
		// $matches[4] = 파라미터
		
		$args = array();

		if($matches[4]) {
			parse_str($matches[4], $args);	
		}
		
		$args['path'] = $matches[1];
		$options = wiki_json_encode($args);		
		
		return '<div class="wiki_latest" style="display:none">'.$options.'</div>';
	}

}

?>