<?
class NarinPluginInfo extends NarinClass {
	
	protected $id;	
	protected $plugin_path;
	protected $setting;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
			
		// get default setting from plugin instance
		$default = $this->getSetting();
		if($default) {
			
			// load saved setting from db
			//$setting = wiki_get_option("plugin_setting/".$this->id);			
			$setting = $this->wiki_config->plugin_setting[$this->id];
			// set setting as loaded value
			if($setting) {
				foreach($default as $k => $v) {
					// if default config is different with saved setting by updating plugin
					if(isset($setting[$k])) $default[$k][value] = $setting[$k];					
				}
			} 
			
			$this->setting = $default;
		} 
	
		$this->plugin_path = $this->wiki[path]."/plugins/".basename(dirname(__FILE__));
	}	

	/**
	 * Return ID
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return plugin path
	 */
	public function getPluginPath() {
		return $this->plugin_path;
	}

	public function getPluginSetting() {
		return $this->setting;
	}
	
	/**
	 * 설정 리턴
	 * syntax / action 플러그인에서 중복구현 됨
	 */
	public function getSetting() {}
	
	/*
	 * 설정은 다음과 같이 되야 함	 
		$setting = array(
			"name"=>array("type"=>"text", "label"=>"이름 : ", "desc"=>"이름을 입력해요", "default"=>"it's default value", "value"=>"test"),
			"desc"=>array("type"=>"textarea", "label"=>"설명 : ", "default"=>"it's default value", "value"=>"ohohoh"),
			"year"=>array("type"=>"select", "label"=>"연도 : ", "options"=>array(1, 2, 3, 4, 5, 6, 7, 8), "default"=>2, "value"=>4),
			"lunar"=>array("type"=>"checkbox", "label"=>"음력 : ", "default"=>"checked", "setval"=>1)
		);	
	*/
    // 나린위키 관리 페이지에서 플러그인에 대한 설정을 할 수 있도록,
    // 다음과 같은 형식으로 setting 제공
    // setting 형식 : array( 필드1=>array(속성), 필드2=>array(속성) ...);
    // 속성 형식
    //    type=>text : <input type="text" ...> 형식으로 설정페이지에서 보임
    //    type=>textarea : <textarea ...></textarea> 형식으로 설정페이지에서 보임
    //    type=>select : <select ..><option...></select> 형식으로 설정페이지에서 보임
    //          select 의 경우 options 를 array 형식으로 반드시 제공해야 함
    //    type=>checkbox : <input type="checkbox" ..> 형식으로 설정페이지에서 보임
    //    value : 기본값
    //    label : 설정페이지 입력 폼의 왼쪽칸에 보일 레이블
    //    desc : 입력값에 대한 설명이 필요하다면 입력	
	public function checkSetting($setting) {
		foreach($setting as $name => $attr) {
			if(!isset($attr[type]) || !isset($attr[label]) || !isset($attr[value])) {
				return false;
			}
			if($attr[type] == "select" && ( !isset($attr[options]) || !is_array($attr[options]))) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 플러그인 설치해야 하나?
	 * syntax / action 플러그인에서 중복구현 됨
	 */
	public function shouldInstall() { return false;	}

	/**
	 * 플러그인 언인스톨해야 하나?
	 * syntax / action 플러그인에서 중복구현 됨
	 */
	public function shouldUnInstall() { return false;	}
	
	/**
	 * 플러그인 설치
	 * syntax / action 플러그인에서 중복구현 됨
	 */
	public function install() { }
	
	/**
	 * 플러그인 삭제
	 * syntax / action 플러그인에서 중복구현 됨
	 */
	public function uninstall() { }	
	
	/**
	 * 관리자페이지에서 플러그인 설정 후 호출
	 * syntax / action 플러그인에서 중복구현 됨
	 */	
	public function afterSetSetting($setting) {}
	
}
?>