<?
/**
 * 
 * 나린위키 Columns 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 Columns 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfoColumns extends NarinPluginInfo {

	/**
	 * 
	 * 생성자
	 */		  	
	public function __construct() {
		parent::__construct();
				
		$this->id = "wiki_columns";				
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
		return "칼럼 플러그인 (저자 : byfun, byfun@byfun.com)";
	}

}



?>