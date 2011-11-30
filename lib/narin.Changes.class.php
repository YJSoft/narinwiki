<?
/**
 *
 * 나린위키 변경내역 클래스
 *
 * 문서/폴더의  생성, 편집, 삭제, 변경 등에 대한 기록을 남기기 위한 클래스
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

class NarinChanges extends NarinClass {
		
	/**
	 * 생성자
	 */
	public function __construct() {
  		parent::__construct();
	}

	/**
	 * 
	 * 변경내역 등록
	 * 
	 * @param string $type 유형 (DOC 또는 FOLDER)
	 * @param string $target 변경되는 문서 경로
	 * @param string $status 변경 내역
	 * @param string $user 사용자 아이디 또는 이름
	 */
	function update($type, $target, $status, $user)
	{		
		$type = mysql_real_escape_string($type);
		$target = mysql_real_escape_string($target);
		$status = mysql_real_escape_string($status);
		$user = mysql_real_escape_string($user);
				
		$sql = "INSERT INTO ".$this->wiki['changes_table']." 
						(bo_table, target_type, target, status, user, ip_addr, reg_date) 
				VALUES ('".$this->wiki['bo_table']."', '$type', '$target', '$status', '$user', '".$this->user_ip."', '".$this->g4['time_ymdhis']."')";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_UPDATE", array("type"=>$type, 
													"target"=>$target, 
													"status"=>$status, 
													"user"=>$user, 
													"ip_addr"=>$this->user_ip));
	}
	
	/**
	 * 
	 * 변경내역 삭제
	 * 
	 * @param int $cid 변경내역 id
	 */
	function delete($cid)
	{
		$cid = mysql_real_escape_string($cid);
		$sql = "DELETE FROM ".$this->wiki['changes_table']." WHERE id = '$cid'";
		sql_query($sql);
		
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_DELETE", array("cid"=>$cid));		
	}
	
	/**
	 * 
	 * 모든변경내역 삭제
	 */
	function clear()
	{
		sql_query("DELETE FROM ".$this->wiki['changes_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."'");	
		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("CHANGES_DELETE_ALL", array());			
	}
}

?>