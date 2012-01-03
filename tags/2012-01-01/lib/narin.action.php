<?
/**
 * 
 * 나린위키 시스템 액션 플러그인 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 시스템 액션 플러그인
 *
 * 나린위키 시스템 액션 플러그인으로 그누보드의 글 등록시, 편집시, 삭제시 등의 이벤트에 대한 처리를 담당한다.
 * lib/actions 안에 실제로 이벤트 처리 루틴이 있으며,
 * 이 클래스에서는 나린위키 이벤트 발생 시 lib/actions/on_(이벤트명).php 파일을 include 하여 처리한다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinActionDefault extends NarinActionPlugin {

	/**
	 *
	 * @uses delete 의 head 와 tail 에서 공유해야 할 경우
	 * @var array event 간에 공유해야할 데이터가 있는 경우 사용할 변수
	 */
	protected $shared;

	/**
	 *
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
		$this->id = "wiki_default_action";
		$this->shared = array();		
	}

	/**
	 *
	 * 액션 등록
	 *
	 * lib/actions 폴더에 있는 이벤트 처리 파일들을 등록
	 * lib/actions 의 이벤트 처리 파일 네이밍 규칙 : on_(이벤트명).php
	 *
	 * @param NarinEvent $ctrl NarinEvent 클래스
	 */
	public function register($ctrl)
	{
		$d = dir(dirname(__FILE__)."/actions");
		while ($entry = $d->read()) {
			if(is_dir($pluginPath) || substr($entry, 0, 3) != 'on_' || substr($entry, -4) != '.php') continue;
			$event = strtoupper(substr($entry, 3, strlen($entry)-7));
			$ctrl->addHandler($event, $this, "common_event_handler", 9999); // 시스템 이벤트 핸들러 order = 9999
		}
	}

	/**
	 *
	 * 공통 이벤트 핸들러
	 *
	 * @param array 이벤트 트리거에서 전달하는 파라미터
	 * @return array 리턴되는 배열은 {@link NarinEvent}에서 반환하고,
	 *               event_trigger()를 호출한 곳에서 extract()해서 global 하게 사용할 수 있도록 처리한다.
	 */
	public function common_event_handler($params) {
		$member = $this->member;
		$wiki = $this->wiki;
		$g4 = $this->g4;
		$config = $this->config;
		$is_member = $this->is_member;
		$is_admin = $this->is_admin;
		$is_wiki_admin = $this->is_wiki_admin;
		$wiki_config = $this->wiki_config;
		$write = &$this->write;
		$bo_table = $wiki['bo_table'];
		extract($this->shared);
		extract($params);

		$return_array = array();
		$shared = array();

		$include_file = "on_".strtolower($params['_type_'].'.php');
		include_once dirname(__FILE__)."/actions/$include_file";

		// included_file 에서 $shared 에 값을 할당했다면 저장
		if(!empty($shared)) foreach($shared as $k => $v) $this->shared[$k] = $v;

		return $return_array;
	}

}

?>
