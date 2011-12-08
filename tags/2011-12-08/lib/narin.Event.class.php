<?
/**
 *
 * 나린위키 이벤트 클래스 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 이벤트 클래스
 *
 * 나린위키 시스템 이벤트 처리를 담당한다. 액션 플러그인을 로드하고, 핸들러를 등록하고 이벤트 trigger를 처리한다.
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiEvent = wiki_class_load("Event");
 * 
 * // 문서가 저장된 후 발생하는 이벤트에 대한 핸들러 등록하기 (class에서 사용)
 * $wikiEvent->addHandler("WRITE_UPDATE", $this, "on_write_update");
 * 
 * // WRITE_UPDATE 이벤트를 발생시키며 파라미터로 wr_id와 doc 를 넘겨주기
 * // extract 는 이벤트 핸들러에서 반환한 값을 변수로 사용하기 위한 처리
 * extract($wikiEvent->trigger("WRITE_UPDATE", array("wr_id"=>79, "doc"=>"/narinwiki/매뉴얼")));
 *  
 * </code>
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinEvent extends NarinClass
{
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
		$this->loadPlugins();
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
		include_once $this->wiki[path]."/lib/narin.Plugin.class.php";
		include_once $this->wiki[path]."/lib/narin.ActionPlugin.class.php";

		$path = $this->wiki['path']."/plugins";
		$use_plugins = array();
		foreach($this->wiki_config->using_plugins as $v) $use_plugins[$v] = $v;

		// 기본 액션 로드
		include_once "narin.action.php";
		$action = new NarinActionDefault();
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
	 * 
	 * 이벤트 핸들러 추가
	 *
	 * @param string $event 이벤트명
	 * @param object $obj {@link NarinActionPlugin} 객체
	 * @param string $handler $obj 내에 구현된 이벤트 핸들러
	 */
	public function addHandler($event, $obj, $handler)
	{
		if(!is_a($obj, "NarinActionPlugin")) return;
		$name = strtoupper(preg_replace("/^NarinAction/", "", get_class($obj)));
		$this->actions[$event][] = array("name"=>$name, "object"=>$obj, "handler"=>$handler);
	}

	/**
	 * 
	 * 이벤트 핸들러 실행
	 * 
	 * @see http://narin.byfun.com/bbs/board.php?bo_table=wiki&wr_id=22
	 * @param string $type 이벤트 타입 (WRITE_UPDATE, DELETE_TAIL, COMMENT_UPDATE 등..)
	 * @param array $params 이 매소드를 호출하는 곳에서 넘겨주는 파라미터 array
	 * @return array 리턴되는 배열 event_trigger()를 호출한 곳에서 extract()해서 global 하게 사용할 수 있도록 처리한다. (연관배열이어야 함)
	 */
	public function trigger($type, $params) {
		$params['_type_'] = $type;
		$returnValue = array();
		if(is_array($this->actions[$type])) {
			foreach($this->actions[$type] as $idx => $p) {
				$ret = $p['object']->$p['handler']($params);
				if(is_array($ret)) {
					foreach($ret as $k=>$v) { $returnValue[$k] = $v; }
				}
			}
		}
		return $returnValue;
	}

	/**
	 * 
	 * 주어진 클래스의 이벤트 핸들러 실행
	 * 
	 * @see http://narin.byfun.com/bbs/board.php?bo_table=wiki&wr_id=22
	 * @param string $plugin_name 플러그인명 (plugin 폴더명)
	 * @param string $type 이벤트 타입 (WRITE_UPDATE, DELETE_TAIL, COMMENT_UPDATE 등..)
	 * @param array $params 이 매소드를 호출하는 곳에서 넘겨주는 파라미터 array
	 * @return array 리턴되는 배열 event_trigger()를 호출한 곳에서 extract()해서 global 하게 사용할 수 있도록 처리한다. (연관배열이어야 함)
	 */
	public function trigger_one($plugin_name, $type, $params)
	{
		$plugin_name = strtoupper($plugin_name);		
		$params['_type_'] = $type;
		$returnValue = array();
		if(is_array($this->actions[$type])) {
			foreach($this->actions[$type] as $idx => $p) {
				if($p['name'] == $plugin_name) {
					$ret = $p['object']->$p['handler']($params);
					if(is_array($ret)) {
						foreach($ret as $k=>$v) { $returnValue[$k] = $v; }
					}
					break;
				}
			}
		}
		return $returnValue;		
	}

}