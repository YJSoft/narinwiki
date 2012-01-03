<?
/**
 * 
 * 나린위키 EasyLevel 플러그인  어드민 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 EasyLevel 플러그인 : 어드민 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinAdminEasyLevel extends NarinAdminPlugin {
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "admin_easy_level";		
		parent::__construct();
	}	  	
	
	/**
	 *
	 * 플러그인 메인 인터페이스
	 *
	 * @param $params /adm/admin.plugin.php 에서 넘겨주는 파라미터로 array('get'=>$_GET, 'post'=>$_POST) 임
	 */
	public function view($params) {
		$loc = ($params['get']['loc'] ? $params['get']['loc'] : '/');
		$wikiNS =& wiki_class_load("Namespace");
		$n = $wikiNS->get($loc);		
		if(!$n) {
			$wikiControl =& wiki_class_load("Control");
			$wikiControl->error("폴더 에러", "존재하지 않는 폴더입니다");
		}		
		$folder_list = $wikiNS->getList($n['ns'], $withArticle=true);		
		$tree = $wikiNS->get_tree("/", $n['ns']);		
		$wiki = $this->wiki;
		include_once dirname(__FILE__)."/skin.php";
	}
	
	/**
	 *
	 * AJAX 모듈 : 폴더 트리를 JSON 으로 출력
	 *
	 * @param $params /adm/admin.plugin.php 에서 넘겨주는 파라미터로 array('get'=>$_GET, 'post'=>$_POST) 임
	 */	
	public function get_tree($params) {
		$loc = wiki_ajax_data($params['get']['loc']);		
		$wikiNS =& wiki_class_load("Namespace");
		$ns = $wikiNS->get($loc);
		if(!$ns && $loc == '/') $wikiNS->addNamespace('/');
		else if(!$ns) {
			echo wiki_json_encode(array('code'=>-1, 'msg'=>'존재하지 않는 폴더입니다.'));
			exit;
		}		
		echo wiki_json_encode(array('code'=>1, 'tree'=>$wikiNS->get_tree("/", $loc)));			
	}
	
	/**
	 *
	 * AJAX 모듈 : 폴더내 폴더/파일 목록을 JSON 으로 출력
	 *
	 * @param $params /adm/admin.plugin.php 에서 넘겨주는 파라미터로 array('get'=>$_GET, 'post'=>$_POST) 임
	 */		
	public function get_list($params) {
		$loc = wiki_ajax_data($params['get']['loc']);		
		$wikiNS =& wiki_class_load("Namespace");		
		$ns = $wikiNS->get($loc);		
		if(!$ns) {
			echo wiki_json_encode(array('code'=>-101, 'msg'=>'존재하지 않는 폴더입니다 : $loc'));
			exit;
		}		
		$list = $wikiNS->getList($loc);		
		echo wiki_json_encode(array('code'=>1, 'parent'=>$loc, 'list'=>$list, 'access_level'=>$ns['ns_access_level']));			
	}
	
	/**
	 *
	 * AJAX 모듈 : 권한 설정 실행
	 *
	 * @param $params /adm/admin.plugin.php 에서 넘겨주는 파라미터로 array('get'=>$_GET, 'post'=>$_POST) 임
	 */		
	public function update_level($params) {
		$update_list = wiki_ajax_data($params['get']['update_list']);
		$recursive = (wiki_ajax_data($params['get']['recursive']) == 'true');
		
		if(!$update_list) {
			echo wiki_json_encode(array('code'=>-1, 'msg'=>'잘못된 파라미터'));
			exit;
		}
		
		$wikiArticle =& wiki_class_load('Article');
		$wikiNS =& wiki_class_load('Namespace');
		foreach($update_list as $k => $item) {
			if($item['type'] == 'doc') $wikiArticle->updateLevel(stripcslashes($item['path']), $item['access_level'], $item['edit_level']);
			else $wikiNS->updateAccessLevel(stripcslashes($item['path']), $item['access_level'], $recursive);
		}
		
		echo wiki_json_encode(array('code'=>1));
	}

	/**
	 *
	 * 플러그인 이름
	 *
	 * @return string 플러그인 이름 (관리 페이지에 보여질 이름)
	 */	
	public function getName() {
		return "쉬운 권한 관리";
	}
	
	/**
	 *
	 * 플러그인 설명
	 *
	 * @return string 플러그인 설명 (관리 페이지에 보여질 설명)
	 */		
	public function getDescription() {
		return "폴더/문서 권한 관리를 쉽게 하도록 도와줍니다.";
	}
	
	/**
	 *
	 * 플러그인 순서
	 *
	 * @return string 플러그인 순서 (관리 페이지에 보여질 순서)
	 */			
	public function getOrder() {
		return 100;
	}

		
}


?>
