<?
/**
 * 나린위키 환경설정(config) 클래스
 *
 * $option_table의 /config를 root 로 하는 레지스트리를 사용하여
 * 환경설정 정보를 저장하고 읽어오는 역할,
 *
 * 모든 클래스의 property 를 제공하는 역할($narinGlobal 맴버 참조)을 하는 클래스
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

class NarinConfig {

	/**
	 *
	 * {@link NarinClass}를 상속받는 모든 배열에서 property 로 접근하기 위한 배열
	 *
	 * <code>
	 * // NarinClass 를 상속받은 클래스 안에서, $narinGlobal['wiki']['bo_table'] 에 접근하기 위한 코드
	 * echo $this->wiki['bo_table'];
	 * </code>
	 *
	 * @var array 전역 변수 저장 배열
	 */
	protected $narinGlobal = array();

	/**
	 *
	 * $wiki['option_table']의 /config/* 에 저장된 설정을 저장하기 위한 배열
	 * @var array 환경설정 정보
	 */
	protected $narinConfig;

	/**
	 *
	 * @var array 위키 정보 (narin.config.php 와 narin.wiki.lib.php 파일에서 설정된)
	 */
	protected $wiki;

	/**
	 *
	 * @var array 나린위키 기본 설정
	 */
	protected $default_setting = array("skin"=>"basic", "head_file"=>"", "tail_file"=>"", "edit_level"=>5, "history_access_level"=>1, "folder_view_level"=>1);

	/**
	 *
	 * @var array 나린위키 기본 사용-플러그인 설정
	 */
	protected $default_using_plugins = array("code", "html");

	/**
	 *
	 * @var array 나린위키 기본 미디어관리자 설정
	 */
	protected $default_media_manager = array("allow_extensions"=>"txt,docx,xlsx,pptx,hwp,doc,xls,ppt,pps,ppsx,pdf,odt,odp,odf,jpg,jpeg,gif,png,psd,ai,zip,rar,tar,gz,7z,wmv,avi,swf,flv,asf,mp3,wma,ogg",
											 "max_file_size"=>"100mb", 
											 "small_size"=>100, 
											 "medium_size"=>200, 
											 "large_size"=>300);
	/**
	 *
	 * @var string 환경설정 저장 위치 : $wiki['option_table']의 /config/* 에 저장
	 */
	protected $reg;

	/**
	 * 생성자
	 */
	public function __construct() {

		global $wiki, $g4, $member, $_GET, $_POST, $_SESSION, $_SERVER, $doc, $wr_doc, $board, $write, $view, $write_table, $is_member, $is_admin, $is_guest, $is_wiki_admin, $config, $urlencode;

		$this->narinGlobal['wiki'] = &$wiki;
		$this->wiki = $wiki;
		$this->narinGlobal['g4'] = &$g4;
		$this->narinGlobal['member'] = &$member;
		$this->narinGlobal['get'] = &$_GET;
		$this->narinGlobal['post'] = &$_POST;
		$this->narinGlobal['session'] = &$_SESSION;
		$this->narinGlobal['is_member'] = &$is_member;
		$this->narinGlobal['is_wiki_admin'] = &$is_wiki_admin;
		$this->narinGlobal['is_admin'] = $is_admin;
		$this->narinGlobal['is_guest'] = $is_guest;
		$this->narinGlobal['config'] = &$config;
		$this->narinGlobal['urlencode'] = &$urlencode;
		$this->narinGlobal['write'] = &$write;
		$this->narinGlobal['board'] = &$board;
		$this->narinGlobal['bo_table'] = &$wiki[bo_table];
		if(!$doc && $wr_doc) $doc = $wr_doc;
		list($ns, $docname, $full) = wiki_page_name(stripslashes($doc));
		$this->narinGlobal['docname'] = $docname;
		$this->narinGlobal['doc'] = $full;
		$this->narinGlobal['folder'] = $ns;
		$this->narinGlobal['user_ip'] = $_SERVER['REMOTE_ADDR'];

		$this->reg = "/".$wiki[bo_table] . "/config";

		$result = sql_query("SELECT * FROM ".$wiki['option_table']." WHERE name LIKE '{$this->reg}/%'");

		for($i=0; $row = sql_fetch_array($result); $i++) {
			$value = json_decode($row['content'], $assoc=true);
			$path = explode("/", $row['name']);
			array_shift($path);array_shift($path);array_shift($path);
			$this->pathToArray($this->narinConfig, $path, $value);
		}
		$this->_extend($this->default_setting, $this->narinConfig['setting']);
		$this->_extend($this->default_media_manager, $this->narinConfig['media_setting']);
		//if(!$this->narinConfig['setting']) $this->narinConfig['setting'] = $this->default_setting;
		if(!$this->narinConfig['using_plugins']) $this->narinConfig['using_plugins'] = $this->default_using_plugins;
		//if(!$this->narinConfig['media_setting']) $this->narinConfig['media_setting'] = $this->default_media_manager;
	}

	/**
	 *
	 * 환경설정 업데이트
	 *
	 * @uses 사용-플러그인 설정할 때 : $config->update("/using_plugins", array("html", "code"));
	 * @param string $path 레지스트리 패스
	 * @param mixed $value 저장할 값
	 */
	public function update($path, $value) {
		$name = mysql_real_escape_string($this->reg.$path);
		$json = mysql_real_escape_string(json_encode($value));
		$opt = sql_fetch("SELECT content FROM ".$this->wiki['option_table']." WHERE name = '".$name."'");
		if($opt) {
			sql_query("UPDATE ".$this->wiki['option_table']." SET content = '$json' WHERE name = '".$name."'");
		} else {
			sql_query("INSERT INTO ".$this->wiki['option_table']." VALUES ( '".$name."', '$json' )");
		}
	}
	
	/**
	 * 
	 * 환경설정 삭제
	 * 
	 * @param string $path 래지스트리 패스
	 */
	public function delete($path) {
		$path = mysql_real_escape_string($this->reg.$path);
		sql_query("DELETE FROM ".$this->wiki['option_table']." WHERE name = '".$path."'");
	}
	
	/**
	 * 
	 * 프로퍼티 매소드
	 * 
	 * 
	 * @uses 환경설정 데이터 접근 : $config->settings
	 * 
	 * @param string $key 프로퍼티 필드명
	 */
	public function __get($key)
	{
		if($this->narinConfig[$key]) return $this->narinConfig[$key];
		else return array();
	}

	/**
	 * 
	 * 자주사용하는 변수들 접근
	 * 
	 * {@link NarinClass}를 상속한 모든 클래스에서 자주 사용하는 변수 접근
	 * {@link NarinClass}에서 프로퍼티 매소드로 접근함.
	 * 
	 * @param string $key 프로퍼티 필드명
	 */
	public function getGlobal($key)
	{
		return $this->narinGlobal[$key];
	}

	/**
	 * 
	 * 레지스트리 패스를 배열에 담음
	 * 
	 * @param array $array 저장할 배열
	 * @param array $keys 키 배열 (e.g. /plugin_setting/wiki_html/ 을 나타내는 배열 => array('plugin_setting', 'wiki_html')
	 * @param mixed $value 값
	 */
	protected function pathToArray(&$array, array $keys, $value) {
		$last = array_pop($keys);
		foreach($keys as $key) {
			if(!@array_key_exists($key, $array) ||
			@array_key_exists($key, $array) && !is_array($array[$key])) {
				$array[$key] = array();
			}
			$array = &$array[$key];
		}
		$array[$last] = $value;
	}
	
	/**
	 * 
	 * 두 배열을 병합 ($default 배열에 $arr을 합침)
	 * @param array $default
	 * @param array $arr
	 */
	protected function _extend($default, &$arr) {
		foreach($default as $k=>$v) {
			if(!isset($arr[$k])) $arr[$k] = $v;
		}
	}
		
}

?>