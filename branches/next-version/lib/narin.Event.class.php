<?
/**
 * 나린위키 이벤트 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */

class NarinEvent extends NarinClass
{
	var $actions = array();
	
	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();					
		$this->loadPlugins();		
	}	
	
	/**
	 * 플러그인 로드
	 */
	protected function loadPlugins()
	{
		include_once $this->wiki[path]."/lib/narin.Plugin.class.php";
		include_once $this->wiki[path]."/lib/narin.ActionPlugin.class.php";
				
		$path = $this->wiki[path]."/plugins";
		$use_plugins = array();
		foreach($this->wiki_config->using_plugins as $v) $use_plugins[$v] = $v;
		
		// 기본 액션 로드
		include_once "narin.action.php";
		$action = new NarinAction();
		$action->register($this);
		
		// Action 플러그인 로드
		
		$d = dir($path);		
		while ($entry = $d->read()) {
			
			$pluginPath = $path ."/". $entry;
			
			if(is_dir($pluginPath) && substr($entry, 0, 1) != ".") {
			
				if(!$use_plugins[$entry]) continue;
			
				$classFile = $pluginPath ."/action.php";				

				if(file_exists($classFile)) {
			
					$realClassName = "NarinAction".ucfirst($entry);

					include_once $classFile;
			
					if(class_exists($realClassName)) {

						$p = new $realClassName();
						array_push($this->actions, $p);
						if(!is_a($p, "NarinActionPlugin")) continue;				
						$p->register($this);
					}
					
				} // if(file_exts...
				
			} // if(is_dir(....
			
		} // while
				
	}


	/**
	* 이벤트 핸들러 추가
	*
	* @param $event (string) 이벤트명
	* @param $obj   (object) 이벤트를 처리할 객체 (class instance)
	* @param $handler (string) $obj 내에 구현된 이벤트 핸들러
	*/
	public function addHandler($event, $obj, $handler)
	{
		$this->actions[$event][] = array("object"=>$obj, "handler"=>$handler);
	}
	
	/**
	 * 액션 실행
	 * @params 문자열		$type 이벤트 타입
	 */
	public function trigger($type, $params) {		
		$params[_type_] = $type;
		$returnValue = array();		
		if(is_array($this->actions[$type])) {			
			foreach($this->actions[$type] as $idx => $p) {
				$ret = $p[object]->$p[handler]($params);
				if(is_array($ret)) {
					foreach($ret as $k=>$v) { $returnValue[$k] = $v; }
				}
			}
		}
		return $returnValue;		
	}

	/**
	 * 연관배열인가?
	 */
	function is_assoc ($arr) {
  	return (is_array($arr) && count(array_filter(array_keys($arr),'is_string')) == count($arr));
	}

}