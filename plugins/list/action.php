<?
/**
 * 
 * 나린위키 리스트 플러그인 액션 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 리스트 플러그인 : 액션 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinActionList extends NarinActionPlugin {
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "wiki_action_list";		
		parent::__construct();
	}	  	

	/**
	 * 
	 * @see lib/NarinActionPlugin::register()
	 */
	public function register($ctrl)
	{
		$ctrl->addHandler("PX_LIST_LIST", $this, "on_ajax_call");
	}	
	
	/**
	 * 
	 * AJAX 콜에 대한 응답
	 * 
	 * ajax로 문서 lock 갱신
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_ajax_call($params) {

		$member = $this->member;
		
		$get = $params['get'];
		$ns = $get['path'];
		$recursive = (isset($get['nosub']) ? false : true);
		$rows = (isset($get['rows']) ? $get['rows'] : 5);
		$cutstr = (isset($get['title_length']) ? $get['title_length'] : 512);
		$dateformat = (isset($get['dateformat']) ? $get['dateformat'] : "Y-m-d h:i:s");
		$order = (isset($get['order']) ? $get['order'] : 'date');
		$reverse = (isset($get['reverse']) ? true : false);
		$with_content = ($get['type'] == 'webzine' ? true : false);
		
		$wild = '';
		foreach($get as $k => $v) {
			if(strpos($k, '*') !== false) {
				$wild = $k;				
				break;				
			}
		}				
		
		define("_LIST_PLUGIN_", 1);
		include_once dirname(__FILE__).'/list.lib.php';
		
		$list = wiki_list_docs($this->wiki, $this->g4, stripslashes($ns), $order, $wild, $recursive, $dateformat, $rows, $cutstr, $reverse, $with_content);

		echo wiki_json_encode(array('code'=>1, 'current_time'=>$this->g4['time_ymdhis'], 'list'=>$list));
		exit;
	}

	
}


?>
