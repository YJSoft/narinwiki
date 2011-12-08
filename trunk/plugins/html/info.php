<?
/**
 * 
 * 나린위키 HTML 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 HTML 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfoHtml extends NarinPluginInfo {

	/**
	 * 
	 * 생성자
	 */		  	
	public function __construct() {
		parent::__construct();
				
		$this->id = "wiki_html";				
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
		return "Embedded HTML 플러그인 (저자 : byfun, byfun@byfun.com)";
	}
		
	/**
	 * 
	 * @see lib/NarinPluginInfo::getSetting()
	 */
	public function getSetting() {
		return array(
			"allow_level"=>array("type"=>"select", "label"=>"플러그인 사용 권한", "desc"=>"설정된 권한보다 낮은 레벨의 사용자가 작성한 문서의 html 태그는 화면에 그대로 출력됩니다.", "options"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), "value"=>2),
			"allow_iframe_level"=>array("type"=>"select", "label"=>"iframe 태그 사용 권한", "desc"=>"설정된 권한보다 낮은 레벨의 사용자가 iframe 태그를 사용했을 경우 출력하지 않습니다.", "options"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), "value"=>9),
			"allow_script_level"=>array("type"=>"select", "label"=>"script 태그 사용 권한", "desc"=>"설정된 권한보다 낮은 레벨의 사용자가 script 태그를 사용했을 경우 출력하지 않습니다.", "options"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), "value"=>9)
		);		
	}
}



?>