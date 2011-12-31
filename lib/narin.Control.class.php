<?
/**
 *
 * 나린위키 흐름 제어(control) 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 흐름 제어(control) 클래스
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiControl =& wiki_class_load("Control");
 * 
 * // 에러 메시지 보여주기
 * $wikiControl->error("문서없음", "존재하지 않는 문서입니다. <a href='javascript:history.go(-1);'>뒤로</a>");
 *  
 * </code>
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinControl extends NarinClass {
	
	/**
	 * 
	 * @var array 전역 변수 모음
	 */
	var $g;
	
	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();	
		
		$this->g = array(
			"member"=>$this->member, 
			"is_admin"=>$this->is_admin, 
			"is_wiki_admin"=>$this->is_wiki_admin,
			"urlencode"=>$this->urlencode,
			"is_member"=>$this->is_member,
			"is_guest"=>$this->is_guest,
			"config"=>$this->config);				
	}

	/**
	 * 
	 * 문서에 대한 접근 제어
	 * 
	 * @param string $doc 문서 경로
	 */
	public function acl($doc) {
		$member = $this->member;
		
		list($ns, $docname, $doc) = wiki_page_name($doc);	
		
		$wikiArticle =& wiki_class_load("Article");
		
		$article = & $wikiArticle->getArticle($ns, $docname);										
	
		if($article && $article['access_level'] > $member['mb_level'] ) {
			$this->notAllowedDocument($ns, $docname, $doc);
		}
		
		$wikiNamespace =& wiki_class_load("Namespace");
		$n = $wikiNamespace->get($ns);
		
		if($n['ns_access_level'] > $member['mb_level']) {
			$this->notAllowedFolder($ns);
		}			
	}

	/**
	 * 
	 * 존재하지 않는 문서에 대한 접근 처리
	 * 
	 * @param string $ns 폴더경로
	 * @param string $docname 문서명
	 * @param string $doc 경로를 포함한 문서명
	 */  
	function noDocument($ns, $docname, $doc) {
		$write_href = $this->wiki['g4_url']."/bbs/write.php?bo_table=".$this->wiki['bo_table']."&doc=".urlencode($doc);
		$this->includePage(
						$this->wiki['inc_skin_path'] . "/nodoc.skin.php", 
						true, 
						array("folder"=>$ns, "docname"=>$docname, "doc"=>$doc, "write_href"=>$write_href)
					);
	}
	
	/**
	 * 
	 * 권한 없는 문서에 대한 접근 처리
	 * 
	 * @param string $ns 폴더경로
	 * @param string $docname 문서명
	 * @param string $doc 경로를 포함한 문서명
	 */
	function notAllowedDocument($ns, $docname, $doc) {
		$this->error("권한 없음", "$docname 문서에 대한 접근 권한이 없습니다.");
	}
	
	/**
	 * 
	 * 권한 없는 폴더에 대한 접근 처리
	 * 
	 * @param string $ns 폴더경로
	 */
	function notAllowedFolder($ns) {
		$this->error("권한 없음", "$ns 폴더에 대한 접근 권한이 없습니다.");
	}
	
	/**
	 * 
	 * 에러 페이지 보여주기
	 * 
	 * <wiki>/skin/board/error.skin.php 로 메시지 출력
	 * 
	 * @param string $title 에러 제목
	 * @param string $msg 에러 내용
	 */
	function error($title, $msg)
	{
		$this->includePage(
					$this->wiki['inc_skin_path'] . "/error.skin.php",
					true, 
					array("title"=>$title, "msg"=>$msg)
				);
		exit;
	}	
	
	/**
	 * 
	 * 문서 보기
	 * 
	 * @param string $doc 경로를 포함한 문서명
	 * @param int $wr_id 문서 id
	 */ 
	function viewDocument($doc, $wr_id) {		
		$path = $this->wiki['g4_path']."/bbs/board.php";		
		chdir($this->g4['g4_path']."/bbs");
		$write = sql_fetch(" select * from ".$this->wiki['write_table']." where wr_id = '$wr_id' ");
		$this->includePage($path, false, array("wr_id"=>$wr_id, "write"=>$write));
	}
			
	/**
	 * 
	 * 그누보드 extend 처리
	 * 
	 * <g4>/extends/narin.wiki.extend.php 에서 호출하며,
	 * 요청되는 스크립트 파일에 따라 위키에서 필요한 처리를 수행
	 * 
	 * @param string $scriptFile 스크립트 파일 (write.php, board.php, write_update.php ...)
	 */
	function board($scriptFile) {
		
		global $wiki, $bo_table,  $wr_id, $board, $doc;
					
		// view
		if($scriptFile == "board.php" && $wr_id) {
			$wikiArticle =& wiki_class_load("Article");
			$view = & $wikiArticle->getArticleById($wr_id);
			$doc = ($view[ns] == "/" ? "" : $view[ns]."/") . $view[doc];
			header("location:".wiki_url('read', array('doc'=>$doc)));
			exit;			
		}
		
		// list
		if($scriptFile == "board.php" && !$wr_id) {
			header("location:".wiki_url());
			exit;
		}	
						
		// 에디터에게 글 작성 권한을 주기 위해...
		if($wr_id && $this->member['mb_id'] && $this->member['mb_id'] != $this->write['mb_id']) {
			
			$wikiArticle =& wiki_class_load("Article");			
			$wikiConfig =& wiki_class_load("Config");

			$default_edit_level = $wikiConfig->setting['edit_level'];
			$article = & $wikiArticle->getArticleById($wr_id);
			$edit_level = ( $article['edit_level'] ? $article['edit_level'] : $default_edit_level);				
			
			$is_doc_editor = ($this->member['mb_level'] >= $edit_level );
			if($scriptFile == "write.php" || $scriptFile == "write_update.php") {
				if($is_doc_editor) {					
					$this->write['mb_id'] = $this->member['mb_id'];			
					$this->write['is_editor'] = true;		
				}
			}
		} else if($wr_id && $this->member['mb_id'] && $this->member['mb_id'] == $this->write['mb_id']) {
			$this->write['is_owner'] = true;
		}
				
		// write
		if($scriptFile == "write.php" && !$doc && !$wr_id ) {
			header("location:".wiki_url());
			exit;			
		}						
	}
	
	/**
	 * 
	 * 페이지 include 매소드
	 * 
	 * @param string $include_path include 할 파일 경로
	 * @param string $layout layout 을 사용할지 말지
	 * @param string $params
	 */
	function includePage($include_path, $layout=false, $params=array()) {
		
		foreach ( $GLOBALS as $key => $value ) { $$key = $value; }	
		
		if(is_array($params)) foreach ( $params as $key => $value ) { $$key = $value; }	
		list($ns, $docname, $doc) = wiki_page_name($doc);
		
		if($layout) include_once WIKI_PATH . "/head.php";		
		include $include_path;		
		if($layout) include_once WIKI_PATH . "/tail.php";				
		
	}
	
	
}

?>
