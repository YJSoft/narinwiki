<?
/**
 * 나린위키 환경설정(config) 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */


class NarinConfig {
	
	protected $narinConfig;
	protected $wiki;
	protected $default_setting = array("skin"=>"basic", "head_file"=>"", "tail_file"=>"", "edit_level"=>5, "history_access_level"=>1, "folder_view_level"=>1);
	protected $default_using_plugins = array("code", "html");
	protected $default_media_manager = array("allow_extensions"=>"txt,docx,xlsx,pptx,hwp,doc,xls,ppt,pps,ppsx,pdf,odt,odp,odf,jpg,jpeg,gif,png,psd,ai,zip,rar,tar,gz,7z,wmv,avi,swf,flv,asf,mp3,wma,ogg",
																					 "max_file_size"=>"100mb", "small_size"=>100, "medium_size"=>200, "large_size"=>300
																		 );
	protected $reg;
	
	/**
	 * Constructor
	 */
	public function __construct() {  	
		global $wiki;
		$this->wiki = $wiki;
		$this->reg = "/".$wiki[bo_table] . "/config";
		
		$result = sql_query("SELECT * FROM {$wiki[option_table]} WHERE name LIKE '{$this->reg}/%'");

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
	
	public function update($path, $value) {
		$name = mysql_real_escape_string($this->reg.$path);
		$json = mysql_real_escape_string(json_encode($value));
		$opt = sql_fetch("SELECT content FROM {$this->wiki[option_table]} WHERE name = '".$name."'");
  	if($opt) {
  		sql_query("UPDATE {$this->wiki[option_table]} SET content = '$json' WHERE name = '".$name."'");
  	} else {
  		sql_query("INSERT INTO {$this->wiki[option_table]} VALUES ( '".$name."', '$json' )");
  	}			
	}
	
	public function delete($path) {
		$path = mysql_real_escape_string($path);
		sql_query("DELETE FROM {$this->wiki[option_table]} WHERE name = '".$path."'");
	}
	
	public function __get($key)
	{
	    if($this->narinConfig[$key]) return $this->narinConfig[$key];
	    else return array();
	}	
	
	function pathToArray(&$array, array $keys, $value) {
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
	
	function _extend($default, &$arr) {
		foreach($default as $k=>$v) {
			if(!isset($arr[$k])) $arr[$k] = $v;
		}
	}
			
}

?>