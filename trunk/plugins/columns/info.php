<?
/**
 * 
 * 나린위키 Columns 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 Columns 플러그인 : 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfoColumns extends NarinPluginInfo {

	
	var $jsfile;
	
	var $cssfile;
	
	var $iconfile;
	
	/**
	 * 
	 * 생성자
	 */		  	
	public function __construct() {
		parent::__construct();
				
		$this->id = "wiki_columns";				
		$this->init();
		
		$this->jsfile = $this->data_path . '/js/columns_plugin.js';
		$this->cssfile = $this->data_path . '/css/columns_plugin.css';
		$this->iconfile = $this->data_path . '/css/columns_plugin.png';
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

	public function shouldInstall() {
		return !file_exists($this->jsfile);
	}
	
	public function shouldUnInstall() {
		return file_exists($this->jsfile);
	}
	
	public function install() {		
		copy($this->plugin_path."/columns.js", $this->jsfile);
		copy($this->plugin_path."/columns.css", $this->cssfile);
		copy($this->plugin_path."/columns.png", $this->iconfile);
	}
	
	public function uninstall() {
		@unlink($this->jsfile);
		@unlink($this->cssfile);
		@unlink($this->iconfile);
	}
	
}



?>
