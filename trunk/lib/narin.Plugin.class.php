<?
/**
 * 나린위키 플러그인 클래스
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

class NarinPlugin extends NarinClass {
	
	/**
	 * 
	 * @var NarinPluginInfo 플러그인 정보 클래스 인스턴스
	 */
	protected $plugin_info;

	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
		//$class_name = strtolower(substr(get_class($this), 11));
		$class_name = substr(get_class($this), 11);
		$class_name{0} = strtolower($class_name{0});
		$this->plugin_info = wiki_plugin_info($class_name);
	}

	/**
	 * 변수 초기화를 위해 사용
	 */
	public function init() {}

}
?>