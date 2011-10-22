<?
if (!defined('_GNUBOARD_')) exit;

class NarinConfig {
	
	protected $narinConfig;
	protected $wiki;
	protected $default_setting = array("skin"=>"basic", "head_file"=>"", "tail_file"=>"", "edit_level"=>5, "history_access_level"=>1, "folder_view_level"=>1);
	protected $default_using_plugins = array("code", "html", "lock");
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
			$value = json_decode($row[content], $assoc=true);
			$path = explode("/", $row[name]);
			array_shift($path);array_shift($path);array_shift($path);
			$this->pathToArray($this->narinConfig, $path, $value);
		}	
		if(!$this->narinConfig[setting]) $this->narinConfig[setting] = $this->default_setting;
		if(!$this->narinConfig[using_plugins]) $this->narinConfig[using_plugins] = $this->default_using_plugins;
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
}

?>