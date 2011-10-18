<?
if (!defined('_GNUBOARD_')) exit;

class NarinChanges extends NarinClass {

	protected $cache = array();
		
	/**
	 * Constructor
	 */
	public function __construct() {
  	parent::__construct();
	}

	function update($type, $target, $status, $user)
	{
		global $_SERVER;
		
		$type = mysql_real_escape_string($type);
		$target = mysql_real_escape_string($target);
		$status = mysql_real_escape_string($status);
		$user = mysql_real_escape_string($user);
				
		$sql = "INSERT INTO {$this->wiki[changes_table]} (bo_table, target_type, target, status, user, ip_addr, reg_date) VALUES ('{$this->wiki[bo_table]}', '$type', '$target', '$status', '$user', '$_SERVER[REMOTE_ADDR]', '{$this->g4[time_ymdhis]}')";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_UPDATE", array("type"=>$type, "target"=>$target, "status"=>$status, "user"=>$user, "ip_addr"=>$_SERVER[REMOTE_ADDR]));
	}

	function delete($cid)
	{
		$cid = mysql_real_escape_string($cid);
		$sql = "DELETE FROM {$this->wiki[changes_table]} WHERE id = '$cid'";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_DELETE", array("cid"=>$cid));		
	}

	function clear()
	{
		sql_query("DELETE FROM {$this->wiki[changes_table]} WHERE bo_table = '{$this->wiki[bo_table]}'");	
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_DELETE_ALL", array());			
	}
}

?>