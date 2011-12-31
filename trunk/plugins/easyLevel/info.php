<?
/**
 * 
 * 나린위키 EasyLevel 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 EasyLevel 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfoEasyLevel extends NarinPluginInfo {

	/**
	 * 
	 * 생성자
	 */		  	
	public function __construct() {
		parent::__construct();
				
		$this->id = "easy_level";				
		$this->init();
	}	  	

	/**
	 * 
	 * 플러그인 설명
	 * 
	 * @return string 플러그인설명
	 */
	public function description()
	{
		return "EasyLevel 플러그인 (저자 : byfun, byfun@byfun.com)";
	}
}



?>
