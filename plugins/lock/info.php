<?
/**
 * 나린위키 문서잠금(Lock) 플러그인 : 플러그인 정보 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
class NarinPluginInfoLock extends NarinPluginInfo {

	var $js_file;
	var $default_release_time = 15;
	
	/**
	 * 생성자
	 */		  	
	public function __construct() {
		$this->id = "wiki_lock";		
		parent::__construct();
		$this->data_js_file = $this->data_path."/js/lock_plugin.js";
	}	  	

	/**
	 * 플러그인 설명
	 */
	public function description()
	{
		return "문서 Lock 플러그인 (저자 : byfun, byfun@byfun.com)";
	}
	
	/**
   * 플러그인 인스톨이 필요한가? (DB 추가작업 등)
   * 필요하다면 return true
   * 이미 설치되어있거나 설치가 필요없다면 return false
   */
  public function shouldInstall() {
  	// js 파일이 없으면 설치 해야 함
    return !file_exists($this->data_js_file);
  }
 
  /**
   * 플러그인 언인스톨해야 하나?
   * 이미 설치되어있다면 return true
   * 그렇지 않다면 return false
   */
  public function shouldUnInstall() { 
    return file_exists($this->data_js_file);
  }	
 
  /**
   * 플러그인 설치 
   * 이 매소드는 위키 관리의 플러그인 설정에서 설치 버튼 클릭시 수행됨
   */
  public function install() {		
		$this->setJs($this->default_release_time);		  	
  }
 
  /**
   * 플러그인 삭제
   * 이 매소드는 위키 관리의 플러그인 설정에서 제거 버튼 클릭시 수행됨
   */
  public function uninstall() {
    // data 폴더의 js 파일 삭제
    @unlink($this->data_js_file);
  }
	
	/**
	 * 플러그인 설정후 js reset
	 */ 		
	public function afterSetSetting($setting) {
  	// js 파일 작성
    $this->setJs($setting[lock_release_time]);
	}
		
		
	/**
	 * 플러그인 설정
	 */
	public function getSetting() {
		$option = array();
		for($i=2; $i<=60; $i+=1) array_push($option, $i);
		return array(		
			"lock_release_time"=>array("type"=>"select", "label"=>"Lock 해제 시간", "desc"=>"설정된 시간동안 입력이 없으면 lock 을 해제합니다. (단위 : 분)", "options"=>$option, "value"=>$this->default_release_time)
		);		
	}
	
	protected function setJs($release_time)
	{
  	// js 파일 작성
    $fp = fopen($this->data_js_file, "w");
    fwrite($fp, $this->js($release_time));
    fclose($fp);		
	}
	
	protected function js($release_time) {

		$expire_time = ($release_time) * 60 * 1000;
		$alert_time = ($release_time - 1) * 60 * 1000;
		
		return <<<END


// write.php 에서만 실행
if(wiki_script == 'write.php') {

	var lock_expire_timer;
	var lock_keep_alive_timer;
	var lock_alert_timer;
	var lock_expired = false;
	
	function lock_alert()
	{
		lock_alert_timer = setTimeout(function() {
			$("#confirm_lock_extend").trigger('click');
		}, $alert_time);
	}
	
	function lock_expire()
	{
		lock_expire_timer = setTimeout(function() {									
			lock_expired = true;
			lock_do_unlock();
			clearTimeout(lock_alert_timer);
			clearTimeout(lock_keep_alive_timer);			
			alert('문서 잠금이 해제되었습니다.\\n문서를 저장했을 때 다른 사용자가 이 문서를 작성중이라면 저장되지 않습니다.\\n작성중이던 문서를 다른 곳에 복사하시고 저장하세요.');	
			$.nmTop().close();
		}, $expire_time);
		
	}	
	
	function lock_keep_alive() {
		lock_keep_alive_timer = setTimeout(function() {
			lock_do();
		}, 30000);		
	}
	
	function lock_do() {
		$.post(wiki_path+"/exe/a.php", { w : "plugin", p : "lock", m : "keep_alive", bo_table : g4_bo_table, doc : wiki_doc }, function(data) {
		});			
	}
	
	function lock_do_unlock() {
		$.ajaxSetup({async:false});
		$.post(wiki_path+"/exe/a.php", { w : "plugin", p : "lock", m : "unlock", bo_table : g4_bo_table, doc : wiki_doc }, function(data) {
			
		});			
	}	
	
	function lock_extend()
	{
		clearTimeout(lock_alert_timer);
		clearTimeout(lock_expire_timer);
		lock_alert();
		lock_expire();
		$.nmTop().close();		
	}
	
	function close_lock_dialog() {		
		$.nmTop().close();
	}
	
	if(typeof wiki_is_locked != 'undefined' && !wiki_is_locked) {
		$(document).ready(function() {
	
				lc_link = $("<a></a>").attr('href', '#lock_msg').attr('id', 'confirm_lock_extend').attr('style', 'display:none');
				lc_dialog = $("<div></div>").attr('id', 'lock_msg').attr('style', 'display:none;').html([
					"<div style='background-color:#3A3A3A;padding:5px;color:#fff;margin-bottom:10px;font-size:14pt;font-weight:bold;'>문서 잠금 연장</div>",
					"<div style='line-height:170%'>",
					"1분 뒤 문서 잠금이 해제됩니다. 문서를 다시 잠그시겠습니까?<br/>",
					"문서 잠금이 해제되면 다른 사람이 문서를 편집할 수 있으며<br/>",
					"다른 사람이 문서를 편집중이라면 문서를 저장할 수 없게됩니다.",
					"</div>",
					"<div style='text-align:center;margin-top:10px;padding-top:10px;border-top:1px dashed #ccc'><span class='button green'><a href='javascript:lock_extend();'>예</a></span>&nbsp;",
					"<span class='button'><a href='javascript:close_lock_dialog();'>아니오</a></span></div>"			
				].join(''));
				$(document.body).append(lc_link);
				$(document.body).append(lc_dialog);
				lc_link.nm({closeOnClick : false, closeOnEscape : false, closeButton : '' });			
				lock_alert();			
				lock_expire();	
				lock_do();
				lock_keep_alive();		
		});	

		$(window).unload(function() {
			lock_do_unlock();
		});

		$(window).bind('beforeunload', function(){
		  return '저장하지 않고 다른 페이지로 이동하시겠습니까?';
		});
				
	}
}		

		
END;

	}
}



?>