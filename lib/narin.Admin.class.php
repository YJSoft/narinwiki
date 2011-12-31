<?
/**
 *
 * 나린위키 관리 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 관리 클래스
 *
 * 나린위키 관리자 플러그인을 실행한다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
 
include_once WIKI_PATH."/lib/narin.Plugin.class.php";
include_once WIKI_PATH."/lib/narin.AdminPlugin.class.php";
 
class NarinAdmin extends NarinClass
{
	
	/**
	 * 
	 * @var array 플러그인 목록 저장
	 */	
	protected $plugin_list = array();
	
	/**
	 * 
	 * @var boolean 플러그인 목록이 로드되었나
	 */	
	protected $is_loaded = false;
	
	/**
	 * 
	 * @var array 이벤트 핸들러를 저장할 배열
	 */
	protected $actions = array();

	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 
	 * 플러그인 로드
	 * 
	 * 시스템 기본 플러그인인 lib/narin.action.php 을 먼저 로드한 뒤,
	 * plugins/ 에 있는 action.php 파일들을 로드한다.
	 */
	protected function loadPlugins()
	{
		if($this->is_loaded) return;
		$this->is_loaded = true;
			
		$path = WIKI_PATH."/plugins";
		$use_plugins = array();
		foreach($this->wiki_config->using_plugins as $v) $use_plugins[$v] = $v;

		// Admin 플러그인 로드
		$d = dir($path);
		while ($entry = $d->read()) {
				
			$pluginPath = $path ."/". $entry;
				
			if(is_dir($pluginPath) && substr($entry, 0, 1) != ".") {
					
				if(!$use_plugins[$entry]) continue;
					
				$classFile = $pluginPath ."/admin.php";

				if(file_exists($classFile)) {
						
					$realClassName = "NarinAdmin".ucfirst($entry);

					include_once $classFile;
											
					if(class_exists($realClassName)) {

						$p = new $realClassName();
						if(!is_a($p, "NarinAdminPlugin")) continue;
						array_push($this->plugin_list, array('id'=>$entry, 'name'=>$p->getName(), 'description'=>$p->getDescription(), 'order'=>$p->getOrder()));
						$p = null;
					}
						
				} // if(file_exts...

			} // if(is_dir(....
				
		} // while
		

		// 플러그인을 order 순으로 정렬
		//$this->plugin_list = wiki_subval_asort($this->plugin_list, 'order');
	}
	
	
	/**
	 *
	 * 플러그인 목록 반환
	 *
	 */
	public function getPlugins() {
		$this->loadPlugins();
		return $this->plugin_list;
	}


	/**
	 *
	 *
	 *
	 */
	public function getPlugin($id) {
	
		$plugin_path = WIKI_PATH."/plugins/".$id."/admin.php";
		if(!$this->isUsable($id)) return null;

		if(file_exists($plugin_path)) {				
			$realClassName = "NarinAdmin".ucfirst($id);
			include_once $plugin_path;									
			if(class_exists($realClassName)) {
				$p = new $realClassName();
				if(!is_a($p, "NarinAdminPlugin")) return null;
				return $p;
			}
		}		
		return null;			
	}

	public function isUsable($id) {
		foreach($this->wiki_config->using_plugins as $v) {
			if($id == $v) return true;
		}		
		return false;
	}
	
}
