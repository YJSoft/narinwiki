<?
/**
 * 
 * 나린위키 문서잠금(Lock) 플러그인 액션 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 문서잠금(Lock) 플러그인 : 액션 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinActionLock extends NarinActionPlugin {
	
	/**
	 * 
	 * @var string 플러그인 id
	 */	
	var $id;

	/**
	 * 
	 * @var boolean 잠긴 문서인가
	 */	
	var $locked;
	
	/**
	 * 
	 * @var boolean 초기화 되었는가
	 */	
	var $initialized = false;
	
	/**
	 * 
	 * @var int 몇초동안 lock 할 것인가
	 */	
	var $lock_life = 40;
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "wiki_action_lock";		
		parent::__construct();
	}	  	

	/**
	 * 
	 * @see lib/NarinActionPlugin::register()
	 */
	public function register($ctrl)
	{
		$ctrl->addHandler("WRITE_HEAD", $this, "on_write_head");
		$ctrl->addHandler("WRITE_UPDATE_HEAD", $this, "on_write_update_head");
		$ctrl->addHandler("WRITE_UPDATE", $this, "on_write_update");
		$ctrl->addHandler("PX_LOCK_KEEP_ALIVE", $this, "on_keep_alive");
		$ctrl->addHandler("PX_LOCK_UNLOCK", $this, "on_unlick");
		$ctrl->addHandler("LOAD_HEAD", $this, "on_load");
	}	
	
	/**
	 * 
	 * 문서 작성 페이지 로드시
	 * 
	 * 문서가 잠겨있는지 안잠겨있는지를 확인하여,
	 * head에 자바스크립트 wiki_is_locked 변수를 설정한다.
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_load($params) {
		if($params['script'] != 'write.php') return;
		
		$setting = $this->plugin_info->getPluginSetting();
		list($ns, $docname, $doc) = wiki_page_name($params['doc']);
		
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
	 * 
	 * 문서 작성 로드시
	 * 
	 * 문서가 lock 되어있는지 검사하고 lock
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_write_head($params) {		
		if($this->plugin_info->shouldInstall()) return;
		$doc = $params['doc'];
		$setting = $this->plugin_info->getPluginSetting();
		$this->initialize_lock($doc);
		if(!$this->locked) {
			$this->lock($doc);
		} else {
			$l_duration = ( time() - $this->locked['time'] );	// in seconds
			if($this->lock_life > $l_duration) {
				$wikiControl =& wiki_class_load("Control");
				$wikiControl->error("문서 잠김", "편집중인 문서입니다.");
			} else {
				$this->lock($doc);
			}
		}
	}
	
	/**
	 * 
	 * 문서 저장시
	 * 
	 * lock 검사 : 저장할때 다른 사용자가 편집중이라면 저장하지 않음
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_write_update_head($params) {
		list($ns, $docname, $doc) = wiki_page_name($params[wr_doc]);
		$this->initialize_lock();
		if($this->locked && $this->locked['ip'] != $this->user_ip) {
			alert("편집중인 문서입니다.");
		}
	}	
	
	/**
	 * 문서 저장 후
	 * 
	 * lock 해제
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_write_update($params) {
		list($ns, $docname, $doc) = wiki_page_name($params[wr_doc]);
		$this->unlock($doc);
	}
	
	/**
	 * 
	 * 문서 작성 중 keep_alive AJAX 콜에 대한 응답
	 * 
	 * ajax로 문서 lock 갱신
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_keep_alive($params) {
		list($ns, $docname, $doc) = wiki_page_name(stripslashes($params['post']['doc']));
		$doc_code = md5($doc);		

		$this->initialize_lock($doc);			
		if(!$this->locked) { echo "0"; return; }	// lock 되어있지 않은 문서를 extend 할 수 없음			
		if($this->locked['ip'] != $this->user_ip) { echo "0"; return; }	// 자신이 lock 한 문서만 extend 할 수 있음
		$this->lock($doc);
		echo "keep alive";
	}
	
	/**
	 * 
	 * 문서 작성 중 unlick AJAX 콜에 대한 응답
	 * 
	 * ajax로 문서 lock 갱신
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_unlock($params) {
		list($ns, $docname, $doc) = wiki_page_name(stripslashes($params['post']['doc']));
		$doc_code = md5($doc);		
		$this->initialize_lock($doc);			
		if($this->locked['ip'] != $this->user_ip) { echo "0"; return; }	// 자신이 lock 한 문서만 unlock 할 수 있음
		$this->unlock($doc);
		echo "unlock";
	}	
	
	/**
	 * 
	 * 잠겼는가?
	 * 
	 * @param string $doc 경로를 포함한 문서명
	 */
	protected function initialize_lock($doc) {
		if($this->initialized) return;
		$doc_code = md5($doc);
		$this->locked = wiki_get_option("locked_docs/$doc_code");
		$this->initialized = true;
	}
	
	/**
	 * 
	 * 문서 잠금	
	 * 
	 * @param string $doc 경로를 포함한 문서명
	 */
	protected function lock($doc) {
		$doc_code = md5($doc);
		wiki_set_option("locked_docs/$doc_code", array("time", "ip"), array(time(), $this->user_ip));		
	}
	
	/**
	 * 
	 * 문서 잠금 해제
	 * 
	 * @param string $doc 경로를 포함한 문서명
	 */
	protected function unlock($doc) {
		$doc_code = md5($doc);		
		wiki_set_option("locked_docs/$doc_code", null, null);	// remove
	}
}


?>
