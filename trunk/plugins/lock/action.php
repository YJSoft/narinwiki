<?
/**
 * 나린위키 문서잠금(Lock) 플러그인 : 액션 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
class NarinActionLock extends NarinActionPlugin {

	var $id;
	var $locked;
	var $initialized = false;
	var $lock_life = 40;
	
	public function __construct() {
		$this->id = "wiki_action_lock";		
		parent::__construct();
	}	  	

	/**
	 * 액션 등록
	 */	
	public function register($ctrl)
	{
		$ctrl->addHandler("WRITE_HEAD", $this, "on_write_head");
		$ctrl->addHandler("WRITE_UPDATE_HEAD", $this, "on_write_update_head");
		$ctrl->addHandler("WRITE_UPDATE", $this, "on_write_update");
		$ctrl->addHandler("AJAX_CALL", $this, "on_ajax_call");
		$ctrl->addHandler("LOAD_HEAD", $this, "on_load");
	}	
	
	public function on_load($params) {
		if($params[script] != 'write.php') return;
		
		$setting = $this->plugin_info->getPluginSetting();
		list($ns, $docname, $doc) = wiki_page_name($params[doc], $strip=false);
		
		$this->initialize_lock($doc);		
		
		$l_duration = ( time() - $this->locked['time'] );	// in sconds
		$wiki_is_locked = ( $this->lock_life < $l_duration ? "false" : "true" );
		
		echo <<<END
<script type="text/javascript">
var wiki_is_locked = $wiki_is_locked;
</script>			

END;

	}
	
	/**
	 * 문서가 lock 되어있는지 검사하고 lock
	 */
	public function on_write_head($params) {		
		if($this->plugin_info->shouldInstall()) return;
		$doc = $params[doc];
		$setting = $this->plugin_info->getPluginSetting();
		$this->initialize_lock($doc);
		if(!$this->locked) {
			$this->lock($doc);
		} else {
			$l_duration = ( time() - $this->locked['time'] );	// in seconds
			if($this->lock_life > $l_duration) {
				$wikiControl = wiki_class_load("Control");
				$wikiControl->error("문서 잠김", "편집중인 문서입니다.");
			} else {
				$this->lock($doc);
			}
		}
	}
	
	/**
	 * lock 검사 : 저장할때 다른 사용자가 편집중이라면 저장하지 않음
	 */
	public function on_write_update_head($params) {
		list($ns, $docname, $doc) = wiki_page_name($params[wr_doc], $strip=false);
		$this->initialize_lock();
		if($this->locked && $this->locked[ip] != $this->user_ip) {
			alert("편집중인 문서입니다.");
		}
	}	
	
	/**
	 * lock 해제
	 */
	public function on_write_update($params) {
		list($ns, $docname, $doc) = wiki_page_name($params[wr_doc], $strip=false);
		$this->unlock($doc);
	}
	
	/**
	 * ajax로 문서 lock 갱신
	 */
	public function on_ajax_call($params) {
		list($ns, $docname, $doc) = wiki_page_name($params[post][doc], $strip=true);
		$doc_code = md5($doc);		
		
		if($params[post][p] == "lock" && $params[post][m] == "keep_alive") {
			$this->initialize_lock($doc);			
			if(!$this->locked) { echo "0"; return; }	// lock 되어있지 않은 문서를 extend 할 수 없음			
			if($this->locked[ip] != $this->user_ip) { echo "0"; return; }	// 자신이 lock 한 문서만 extend 할 수 있음
			$this->lock($doc);
			echo "1";
			return;
		}
		
		if($params[post][p] == "lock" && $params[post][m] == "unlock") {
			$this->initialize_lock($doc);			
			if($this->locked[ip] != $this->user_ip) { echo "0"; return; }	// 자신이 lock 한 문서만 unlock 할 수 있음
			$this->unlock($doc);
			echo "1";
			return;			
		}
		echo "0";
	}
	
	/**
	 * 잠겼는가?
	 */
	protected function initialize_lock($doc) {
		if($this->initialized) return;
		$doc_code = md5($doc);
		$this->locked = wiki_get_option("locked_docs/$doc_code");
		$this->initialized = true;
	}
	
	/**
	 * 잠금
	 */
	protected function lock($doc) {
		$doc_code = md5($doc);
		wiki_set_option("locked_docs/$doc_code", array("time", "ip"), array(time(), $this->user_ip));		
	}
	
	/**
	 * 잠금 해제
	 */
	protected function unlock($doc) {
		$doc_code = md5($doc);		
		wiki_set_option("locked_docs/$doc_code", null, null);	// remove
	}
}


?>