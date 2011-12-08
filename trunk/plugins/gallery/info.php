<?
/**
 * 
 * 나린위키 갤러리 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 갤러리 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
 
class NarinPluginInfoGallery extends NarinPluginInfo {

	/**
	 *
	 * @var string 저장할 JS 파일 경로
	 */
	var $data_js_file;
	
	/**
	 *
	 * @var string 저장할 CSS 파일 경로
	 */
	var $data_css_file;	


	/**
	 * 생성자
	 */
	public function __construct() {
		
		parent::__construct();			
		
		$this->id = "wiki_gallery";				
		$this->data_js_file = $this->data_path."/js/gallery_plugin.js";
		$this->data_css_file = $this->data_path."/css/gallery_plugin.css";
		
		// {@link NarinPluginInfo} 클래스의 생성자에서 getSetting() 을 호출함		
		$this->init();
	}

	/**
	 * 
	 * 플러그인 설명
	 * 
	 * @return string 플러그인 설명
	 */
	public function description()
	{
		return "갤러리 플러그인 (저자 : byfun, byfun@byfun.com)";
	}

	/**
	 * 
	 * js 파일이 없으면 설치하자
	 * 
	 * @see lib/NarinPluginInfo::shouldInstall()
	 */
	public function shouldInstall() {	
		return !file_exists($this->data_js_file) || !file_exists($this->data_css_file);
	}

	/**
	 * 
	 * js 파일이 있으면 삭제하자
	 * 
	 * @see lib/NarinPluginInfo::shouldUnInstall()
	 */
	public function shouldUnInstall() {
		return file_exists($this->data_js_file) && file_exists($this->data_css_file);
	}

	/**
	 * 
	 * 설치 : js 파일을 데이터 폴더에 저장함
	 * 
	 * @see lib/NarinPluginInfo::install()
	 */
	public function install() {
		$this->setJsCss();
	}

	/**
	 * 
	 * 삭제 : js 파일을 삭제함
	 * 
	 * @see lib/NarinPluginInfo::uninstall()
	 */
	public function uninstall() {
		// data 폴더의 js 파일 삭제
		@unlink($this->data_js_file);
		@unlink($this->data_css_file);
		$this->wiki_config->delete('/plugin_setting/' . $this->id);
	}

	/**
	 * 
	 * 관리자 페이지에서 플러그인 설정 후
	 * js 파일 다시 설정
	 * 
	 * @see lib/NarinPluginInfo::afterSetSetting()
	 */
	public function afterSetSetting($setting) {
		$this->setJsCss($setting['css']);
	}


	/**
	 *
	 * @see lib/NarinPluginInfo::getSetting()
	 */
	public function getSetting() {
		$css = file_exists($this->data_css_file) ? file_get_contents($this->data_css_file) : file_get_contents($this->plugin_path."/gallery.css");
		return array("css"=>array("type"=>"textarea", 
															"label"=>"CSS : ", 
															"desc"=>"갤러리 CSS를 설정해주세요.", 
															"value"=>$css));
	}

	/**
	 * 
	 * JS 파일을 데이터 폴더에 작성
	 * 
	 */
	protected function setJsCss($css = "")
	{
		
		$js = file_get_contents($this->plugin_path."/gallery.js");
		if(!$css) $css = file_get_contents($this->plugin_path."/gallery.css");
		
		$js = str_replace("{_pluginpath_}", $this->plugin_path, $js);
		// js 파일 작성
		$fp = fopen($this->data_js_file, "w");
		fwrite($fp, $js);
		fclose($fp);
		
		// css 파일 작성
		$fp = fopen($this->data_css_file, "w");
		fwrite($fp, $css);
		fclose($fp);
	}

}



?>