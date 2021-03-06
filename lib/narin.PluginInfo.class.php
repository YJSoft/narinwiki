<?
/**
 * 
 * 나린위키 플러그인 정보 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 플러그인 정보 클래스
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinPluginInfo extends NarinClass {

	/**
	 *
	 * @var string 플러그인 고유 아이디
	 */
	protected $id;

	/**
	 *
	 * @var string 플러그인 경로
	 */
	protected $plugin_path;

	/**
	 *
	 * @var string 데이터 경로
	 */
	protected $data_path;

	/**
	 *
	 * @var array 플러그인 설정
	 */
	protected $setting;

	/**
	 * 생성자
	 */
	public function __construct() {
		
		parent::__construct();			
		
		$class_name = substr(get_class($this), 15);
		$class_name{0} = strtolower($class_name{0});
		$this->plugin_path = WIKI_PATH."/plugins/".$class_name;
		$this->data_path = WIKI_PATH."/data/".$this->wiki[bo_table];		
		
	}
	
	/**
	 *
	 * PluginInfo 에서 생성자 실행 후 호출하는 초기화 매소드
	 * 
	 * 플러그인 info 클래스에서 중복구현 되어야 함
	 */
	public function init() {
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

		
	}

	/**
	 *
	 * 플러그인 아이디
	 *
	 * @return string 플러그인 id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * 플러그인 경로 : narinwiki/plugins/플러그인폴더
	 *
	 * @return string 플러그인 경로
	 */
	public function getPluginPath() {
		return $this->plugin_path;
	}

	/**
	 *
	 * 데이터 저장 경로 : narinwiki/data/$bo_table
	 *
	 * @return string 플러그인
	 */
	public function getDataPath() {
		return $this->data_path;
	}

	/**
	 *
	 * 플러그인 설정
	 *
	 * DB에 저장된 플러그인 설정을 반환함
	 * (처음 실행이라면 기본값)
	 *
	 * @return string 플러그인
	 */
	public function getPluginSetting() {
		return $this->setting;
	}

	/**
	 *
	 * 설정 리턴
	 *
	 *  나린위키 관리 페이지에서 플러그인에 대한 설정을 할 수 있도록  다음과 같은 형식으로 setting 제공
	 *  setting 형식 : array( 필드1=>array(속성), 필드2=>array(속성) ...);
	 *  속성 형식
	 *  - type : text, textarea, select, checkbox 중 1
	 *      - type=>text : <input type="text" ...> 형식으로 설정페이지에서 보임
	 *      - type=>textarea : <textarea ...></textarea> 형식으로 설정페이지에서 보임
	 *      - type=>select : <select ..><option...></select> 형식으로 설정페이지에서 보임
	 *      - type=>checkbox : <input type="checkbox" ..> 형식으로 설정페이지에서 보임
	 *  - default : 기본값
	 *  - value : 설정값
	 *  - label : 설정페이지 입력 폼의 왼쪽칸에 보일 레이블
	 *  - desc : 입력값에 대한 설명이 필요하다면 입력
	 *  - options : type=select 의 경우 options 를 array 형식으로 반드시 제공해야 함
	 *
	 * 설정 예제 :
	 * <code>
	 * $setting = array(
	 *		"name"=>array("type"=>"text", "label"=>"이름 : ", "desc"=>"이름을 입력해요", "default"=>"it's default value", "value"=>"test"),
	 *		"desc"=>array("type"=>"textarea", "label"=>"설명 : ", "default"=>"it's default value", "value"=>"ohohoh"),
	 *		"year"=>array("type"=>"select", "label"=>"연도 : ", "options"=>array(1, 2, 3, 4, 5, 6, 7, 8), "default"=>2, "value"=>4),
	 *		"lunar"=>array("type"=>"checkbox", "label"=>"음력 : ", "default"=>"checked", "setval"=>1)
	 *	);
	 * </code>
	 *
	 * syntax / action 플러그인에서 중복구현 되어야 함
	 */
	public function getSetting() {}

	/**
	 *
	 * 플러그인 설정 유효성 검사
	 *
	 * @see adm/exe_plugin_setting.php
	 * @param array $setting 플러그인 설정 정보
	 */
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
	 *
	 * 플러그인 설치해야 하나?
	 *
	 * 플러그인에서 중복구현 되어야 함
	 *
	 * @see adm/plugin.php
	 * @return true|false 설치해야 하면 true, 아니면 false
	 */
	public function shouldInstall() { return false;	}

	/**
	 *
	 * 플러그인 언인스톨해야 하나?
	 *
	 * 플러그인에서 중복구현 되어야 함
	 *
	 * @see adm/plugin.php
	 * @return true|false 언인스톨해야 하면 true, 아니면 false
	 */
	public function shouldUnInstall() { return false;	}

	/**
	 *
	 * 플러그인 설치
	 *
	 * @see adm/plugin_install.php
	 * 플러그인에서 중복구현 되어야 함
	 */
	public function install() { }

	/**
	 *
	 * 플러그인 삭제
	 *
	 * @see adm/plugin_install.php
	 * 플러그인에서 중복구현 되어야 함
	 */
	public function uninstall() { }

	/**
	 *
	 * 관리자페이지에서 플러그인 설정 후 호출
	 *
	 * 플러그인에서 중복구현 되어야 함
	 *
	 * @see adm/exe_plugin_setting.php
	 * @param array 업데이트된 설정 값
	 */
	public function afterSetSetting($setting) {}

	/**
	 *
	 * 관리자페이지에서 플러그인을 사용안함 으로 설정할 때 호출
	 *
	 * 플러그인에서 중복구현 되어야 함
	 *
	 * @see adm/exe_plugin.php
	 * @param array 업데이트된 설정 값
	 */	
	public function onUnused() {}
}
?>
