<?

class NarinAction extends NarinActionPlugin {

	var $id;
	
	/**
	 * event 간에 공유해야할 데이터가 있는 경우 사용할 변수
	 * e.g. delete 의 head 와 tail 에서 공유해야 할 경우
	 */	
	var $shared;
	
	public function __construct() {
		$this->id = "wiki_default_action";		
		$this->shared = array();
		parent::__construct();
	}	  	

	/**
	 * 액션 등록
	 * lib/actions 폴더에 있는 이벤트 처리 파일들을 등록
	 *
	 * lib/actions 의 이벤트 처리 파일 네이밍 규칙 : on_(이벤트명).php 
	 */	
	public function register($ctrl)
	{
		$d = dir(dirname(__FILE__)."/actions");		
		while ($entry = $d->read()) {
			if(is_dir($pluginPath) || substr($entry, 0, 3) != 'on_' || substr($entry, -4) != '.php') continue;
			$event = strtoupper(substr($entry, 3, strlen($entry)-7));
			$ctrl->addHandler($event, $this, "common_event_handler");			
		}
	}

	/**
	 * 공통 이벤트 핸들러
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
		
		extract($this->shared);
		extract($params);			
		
		$return_array = array();
		$shared = array();
		
		$include_file = "on_".strtolower($params[_type_].'.php');		
		include_once dirname(__FILE__)."/actions/$include_file";		
		
		// included_file 에서 $shared 에 값을 할당했다면 저장
		if(!empty($shared)) foreach($shared as $k => $v) $this->shared[$k] = $v;
		
		return $return_array;
	}
	
}

?>