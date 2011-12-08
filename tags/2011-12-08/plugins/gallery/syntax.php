<?
/**
 *
 * 나린위키 갤러리 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 갤러리 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxGallery extends NarinSyntaxPlugin {
	 
	/**
	 * 파싱 시작되기 전에 변수 초기화
	 */
	function init()
	{
	}

	/**
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		$parser->addVariableParser(
          $id = $this->id."_gallery",
          $klass = $this,
          $startRegx = "(gallery=|gal=)",	// gallery= 또는 gal=
          $endRegx = "((\?)(.*?))?",	// 파라미터 ? 다음 문자열 (optional)
          $method = "wiki_gallery");
	}

	/**
	 * 
	 * HTML 변환
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function wiki_gallery($matches, $params) {
		// $matches[1] = gallery= 또는 gal=
		// $matches[2] = 경로
		// $matches[4] = 파라미터
		
		$args = array();

		if($matches[5]) {
			parse_str($matches[5], $args);	
		}
		
		$args['path'] = $matches[2];
		$options = wiki_json_encode($args);		
		
		return '<div class="wiki_gallery" style="display:none">'.$options.'</div>';
	}

}

?>