<?
/**
 * 
 * 나린위키 코드(code) 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 코드(code) 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfoCode extends NarinPluginInfo {

	/**
	 * 
	 * 생성자
	 */		  	
	public function __construct() {
		parent::__construct();
		
		$this->id = "wiki_code_highlighter";				
		$this->init();
	}	  	

	/**
	 * 
	 * 플러그인 설명
	 */
	public function description()
	{
		return "문법 강조 플러그인 (저자 : byfun, byfun@byfun.com)";
	}
}



?>
