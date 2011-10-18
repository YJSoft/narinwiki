<?

class NarinActionHtml extends NarinActionPlugin {

	var $id;
	
	public function __construct() {
		$this->id = "wiki_action_html";		
		parent::__construct();
	}	  	

	/**
	 * 액션 등록
	 */	
	public function register($ctrl)
	{
		$ctrl->addHandler("WRITE_UPDATE_HEAD", $this, "on_write_update_head");
	}	
	
	public function on_write_update_head($params) {
		$wr_content = $params[wr_content];
		$member = $this->member;
		$setting = $this->plugin_info->getPluginSetting();		
		$allow_level = $setting[allow_level][value];
		$allow_iframe_level = $setting[allow_iframe_level][value];
		$allow_script_level = $setting[allow_script_level][value];
		
		if($allow_level > $member[mb_level])
		{
			if(preg_match("/<html>/i", $wr_content)) {
				alert("HTML 태그를 사용할 수 없습니다.");
				exit;
			}
		}
		
		if($allow_iframe_level > $member[mb_level])
		{
			if(preg_match("/<iframe([^\>]*)/i", $wr_content)) {
				alert("IFRAME 태그를 사용할 수 없습니다.");
				exit;
			}			
		}
		if($allow_script_level > $member[mb_level])
		{
			if(preg_match("/<script([^\>]*)/i", $wr_content)) {
				alert("SCRIPT 태그를 사용할 수 없습니다.");
				exit;
			}					
		}				
	}
}


?>