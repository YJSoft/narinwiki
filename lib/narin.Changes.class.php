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

	function update($doc, $status, $mb_id)
	{
		global $_SERVER;

		$doc = mysql_real_escape_string($doc);
		$status = mysql_real_escape_string($status);
		$mb_id = mysql_real_escape_string($mb_id);
				
		$sql = "INSERT INTO {$this->wiki[changes_table]} (bo_table, doc, status, mb_id, ip_addr, reg_date) VALUES ('{$this->wiki[bo_table]}', '$doc', '$status', '$mb_id', '$_SERVER[REMOTE_ADDR]', '{$this->g4[time_ymdhis]}')";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_UPDATE", array("doc"=>$wr_id, "status"=>$content, "mb_id"=>$mb_id, "ip_addr"=>$_SERVER[REMOTE_ADDR]));
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