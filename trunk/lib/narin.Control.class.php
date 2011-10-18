<?
if (!defined('_GNUBOARD_')) exit;

class NarinControl extends NarinClass {

	var $g;
	
	/**
	 * Constructor
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

	public function acl($doc) {
		$member = $this->member;
		
		list($ns, $docname, $doc) = wiki_page_name($doc, $strip=false);	
		
		$wikiArticle = wiki_class_load("Article");
		
		$article = $wikiArticle->getArticle($ns, $docname, __FILE__, __LINE__);										
			
		if($article && $article[access_level] > $member[mb_level] ) {
			$this->notAllowedDocument($ns, $docname, $doc);
		}
		
		$wikiNamespace = wiki_class_load("Namespace");
		$n = $wikiNamespace->get($ns);
		
		if($n[ns_access_level] > $member[mb_level]) {
			$this->notAllowedFolder($ns);
		}			
	}

	

	/**
	 * Create new page
	 */  
	function noDocument($ns, $docname, $doc) {
		$write_href = $this->g4[path]."/bbs/write.php?bo_table=".$this->wiki[bo_table]."&doc=".urlencode($doc);
		$this->includePage(
						$this->wiki[inc_skin_path] . "/nodoc.skin.php", 
						true, 
						array("folder"=>$ns, "docname"=>$docname, "doc"=>$doc, "write_href"=>$write_href)
					);
	}
	
	/**
	 * Not allowed page
	 */  
	function notAllowedDocument($ns, $docname, $doc) {
		$this->error("권한 없음", "$docname 문서에 대한 접근 권한이 없습니다.");
	}
	
	/**
	 * Not allowed folder
	 */  
	function notAllowedFolder($ns) {
		$this->error("권한 없음", "$ns 폴더에 대한 접근 권한이 없습니다.");
	}
	
	/**
	 * Error page
	 */
	function error($title, $msg)
	{
		$this->includePage(
					$this->wiki[inc_skin_path] . "/error.skin.php",
					true, 
					array("title"=>$title, "msg"=>$msg)
				);
		exit;
	}	
	
	/**
	 * View page
	 */  
	function viewDocument($doc, $wr_id) {		
		$path = $this->g4[path]."/bbs/board.php";		
		chdir($this->g4[path]."/bbs");
		$write = sql_fetch(" select * from {$this->wiki[write_table]} where wr_id = '$wr_id' ");
		$this->includePage($path, false, array("wr_id"=>$wr_id, "write"=>$write));
	}
			

	/**
	 *
	 **/
	function board($scriptFile) {
		global $wiki, $bo_table,  $wr_id, $board, $doc;
					
		// view
		if($scriptFile == "board.php" && $wr_id) {
			$wikiArticle = wiki_class_load("Article");
			$view = $wikiArticle->getArticleById($wr_id);
			$doc = ($view[ns] == "/" ? "" : $view[ns]."/") . $view[doc];
			header("location:{$this->wiki[path]}/narin.php?bo_table={$board[bo_table]}&doc=".urlencode($doc));
			exit;			
		}
		
		// list
		if($scriptFile == "board.php" && !$wr_id) {
			header("location:{$this->wiki[path]}/narin.php?bo_table={$board[bo_table]}");
			exit;
			}
						
		// 에디터에게 글 작성 권한을 주기 위해...
		if($wr_id && $this->member[mb_id] && $this->member[mb_id] != $this->write[mb_id]) {
			$wikiArticle = wiki_class_load("Article");			
			$wikiConfig = wiki_class_load("Config");

			$default_edit_level = $wikiConfig->setting[edit_level];
			$article = $wikiArticle->getArticleById($wr_id);
			$edit_level = ( $article[edit_level] ? $article[edit_level] : $default_edit_level);				
			
			$is_doc_editor = ($this->member[mb_level] >= $edit_level );
			if($scriptFile == "write.php" || $scriptFile == "write_update.php") {
				if($is_doc_editor) {					
					$this->write[mb_id] = $this->member[mb_id];			
					$this->write[is_editor] = true;		
				}
			}
		} else if($wr_id && $this->member[mb_id] && $this->member[mb_id] == $this->write[mb_id]) {
			$this->write[is_owner] = true;
		}
				
		// write
		if($scriptFile == "write.php" && !$doc && !$wr_id ) {
			header("location:{$this->wiki[path]}/narin.php?bo_table={$board[bo_table]}");
			exit;			
		}		

		
		// write_update (delete thumbnails)
		if($scriptFile == "write_update.php") {
			if($w == "u" && $wr_id) {
				$thumb = wiki_class_load("Thumb");				
				$thumb->deleteThumb($this->wiki[bo_table]."-".$wr_id . "-");
			}
		}		
		
		
		
	}
	
	/**
	 * Process after updating post
	 */
	function write_update($w, $wr_id, $wr_doc) {
		
		$article = wiki_class_load("Article");
		
		if($w == '') { // 새글 작성 시
			$article->addArticle($wr_doc, $wr_id);					
		} else if($w == 'u') {	// 업데이트 시
			$article->updateArticle($wr_doc, $wr_id);
		}			
	}
	
	/**
	 * Process after deleting post
	 */
	function delete($wr_id) {		
		$article = wiki_class_load("Article");
		$article->deleteArticleById($wr_id);
	}	

	/**
	 * Include page
	 */
	function includePage($include_path, $layout=false, $params=array()) {
		
		foreach ( $GLOBALS as $key => $value ) { $$key = $value; }	
		
		if(is_array($params)) foreach ( $params as $key => $value ) { $$key = $value; }	
		list($ns, $docname, $doc) = wiki_page_name($doc, $strip=false);
		
		if($layout) include_once $this->wiki[path] . "/head.php";		
		include $include_path;		
		if($layout) include_once $this->wiki[path] . "/tail.php";				
	}
	
	
}

?>