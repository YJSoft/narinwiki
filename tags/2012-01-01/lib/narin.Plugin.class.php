<?
/**
 * 
 * 나린위키 플러그인 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 플러그인 클래스
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinPlugin extends NarinClass {
	
	/**
	 * 
	 * @var 플러그인 고유 id
	 */	
	var $id;
	
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
		$this->id = $class_name;
		$this->plugin_info = wiki_plugin_info($class_name);
	}
}
?>
