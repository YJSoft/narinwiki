<?
/**
 * 
 * 나린위키 HTML 플러그인  액션 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 HTML 플러그인 : 액션 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinActionHtml extends NarinActionPlugin {

	/**
	 * 
	 * @var string 플러그인 id
	 */
	var $id;
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "wiki_action_html";		
		parent::__construct();
	}	  	

	/**
	 * 
	 * @see lib/NarinActionPlugin::register()
	 */
	public function register($ctrl)
	{
		$ctrl->addHandler("WRITE_HEAD", $this, "on_write_head");
	}	
	
	/**
	 * 
	 * 문서 등록/수정 시 처리
	 * 
	 * @param array $params {@link NarinEvent} 에서 전달하는 파라미터
	 */
	public function on_write_head($params) {
		$wr_content = $params['write']['wr_content'];
		$member = $this->member;
		$setting = $this->plugin_info->getPluginSetting();		
		$allow_level = $setting['allow_level']['value'];
		$allow_iframe_level = $setting['allow_iframe_level']['value'];
		$allow_script_level = $setting['allow_script_level']['value'];
		
		if($allow_level > $member['mb_level'])
		{
			if(preg_match("/<html>/i", $wr_content)) {
				$wikiControl = wiki_class_load("Control");
				$wikiControl->error("권한 없음", "접근할 수 없는 내용을 가지고 있습니다. (html)");
			}
		}
		
		if($allow_iframe_level > $member['mb_level'])
		{
			if(preg_match("/<iframe([^\>]*)/i", $wr_content)) {
				$wikiControl = wiki_class_load("Control");
				$wikiControl->error("권한 없음", "접근할 수 없는 내용을 가지고 있습니다. (iframe)");
			}			
		}
		if($allow_script_level > $member['mb_level'])
		{
			if(preg_match("/<script([^\>]*)/i", $wr_content)) {
				$wikiControl = wiki_class_load("Control");
				$wikiControl->error("권한 없음", "접근할 수 없는 내용을 가지고 있습니다. (script)");
				exit;
			}					
		}				
	}
	
	
	/**
	 * 
	 * 문서 등록/수정 시 처리
	 * 
	 * @param array $params {@link NarinEvent} 에서 전달하는 파라미터
	 */
	public function on_write_update_head($params) {
		$wr_content = $params['wr_content'];
		$member = $this->member;
		$setting = $this->plugin_info->getPluginSetting();		
		$allow_level = $setting['allow_level']['value'];
		$allow_iframe_level = $setting['allow_iframe_level']['value'];
		$allow_script_level = $setting['allow_script_level']['value'];
		
		if($allow_level > $member['mb_level'])
		{
			if(preg_match("/<html>/i", $wr_content)) {
				alert("HTML 태그를 사용할 수 없습니다.");
				exit;
			}
		}
		
		if($allow_iframe_level > $member['mb_level'])
		{
			if(preg_match("/<iframe([^\>]*)/i", $wr_content)) {
				alert("IFRAME 태그를 사용할 수 없습니다.");
				exit;
			}			
		}
		if($allow_script_level > $member['mb_level'])
		{
			if(preg_match("/<script([^\>]*)/i", $wr_content)) {
				alert("SCRIPT 태그를 사용할 수 없습니다.");
				exit;
			}					
		}				
	}
		
}


?>