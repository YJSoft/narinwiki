<?
/**
 *
 * 나린위키 문서 클래스 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 문서 클래스
 *
 * 저장,삭제, 업데이트, 백링크 등 문서에 관한 처리를 담당하는 클래스.
 * 
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiArticle = wiki_class_load("Article");
 * 
 * // "/narin/나린위키 매뉴얼" 문서가 존재하는 지 확인
 * $is_exists = $wikiArticle->exists("/narin", "나린위키 매뉴얼");
 *  
 * // "/narin/나린위키 매뉴얼" 문서를 읽어오기
 * $write = $wikiArticle->getArticle("/narin", "나린위키 매뉴얼");
 * 
 * // wr_id = 79 인 문서를 읽어오기
 * $write = $wikiArticle->getArticleById(79);
 *
 * // "/narin/나린위키 매뉴얼" 문서를 링크하고 있는 다른 문서 목록(backlinks) 가져오기
 * $back_links = $wikiArticle->getBackLinks("/narin/나린위키 매뉴얼", $includeSelf=false);
 * 
 * // 위키 시작 페이지 가져오기
 * $write_startpage = $wikiArticle->getFrontPage();
 * 
 * // $content 에 "/narin/나린위키 매뉴얼" 문서에 대한 링크가 있는지 검사
 * $has_link = $wikiArticle->hasInternalLink($content, "/narin/나린위키 매뉴얼");
 * 
 * // "/narin/나린위키 매뉴얼" 문서를 "/narinwiki/매뉴얼" 문서로 변경하기
 * $wikiArticle->moveDoc("/narin/나린위키 매뉴얼", "/narinwiki/매뉴얼");
 * 
 * // 문서 보기시 cache 를 업데이트 하도록 설정 
 * $wikiArticle->shouldUpdateCache ($wr_id, 1);
 * 
 * // wr_id = 79 인 문서의 문서/경로명을 "/narinwiki/매뉴얼"로 변경하기 
 * $wikiArticle->updateArticle("/narinwiki/매뉴얼", 79);
 * 
 * // "/narinwiki/매뉴얼" 문서의 접근권한 5, 편집 권한 9로 변경 
 * $wikiArticle->updateLevel("/narinwiki/매뉴얼", 5, 9);
 * </code>
 * 
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com) 
 */
class NarinArticle extends NarinClass {
	
	/**
	 * 
	 * 문서 이름 변경시 사용할 변수  
	 * 
	 * 이전이름 (다른 클래스에서 사용함)
	 * @var string
	 */
	public $fromDoc;
	
	/**
	 * 
	 * 문서 이름 변경시 사용할 변수 
	 * 
	 * 바꿀 이름 (다른 클래스에서 사용함)
	 * @var string
	 */
	public $toDoc;
	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
		$this->namespace = wiki_class_load("Namespace");
		$this->history = wiki_class_load("History");
	}

	/**
	 * 
	 * 문서 반환 by 문서명
	 * 
	 * @param string $ns 네임스페이스(폴더)
	 * @param string $docname 문서명
	 * @return array 문서 데이터
	 */
	public function getArticle($ns, $docname)
	{
		$e_ns = mysql_real_escape_string($ns);
		$e_docname = mysql_real_escape_string($docname);
		$sql = "SELECT *, wb.wr_subject AS doc
						FROM ".$this->wiki['write_table']." AS wb 
						LEFT JOIN ".$this->wiki['nsboard_table']." AS nt ON wb.wr_id = nt.wr_id 
						WHERE nt.bo_table = '".$this->wiki['bo_table']."' AND nt.ns = '$e_ns' AND wb.wr_subject = '$e_docname'";

		$row = sql_fetch($sql);
		return $row;
	}
	
	/**
	 * 
	 * 문서 반환 by wr_id
	 * 
	 * @param int $wr_id 문서id (그누보드 게시판의 wr_id)
	 * @return array 문서 데이터
	 */
	public function getArticleById($wr_id)
	{
		$wr_id = mysql_real_escape_string($wr_id);
		$sql = "SELECT *, wb.wr_subject AS doc FROM ".$this->wiki['write_table']." AS wb 
				LEFT JOIN ".$this->wiki['nsboard_table']." AS nt ON wb.wr_id = nt.wr_id 
				WHERE nt.bo_table = '".$this->wiki['bo_table']."' AND wb.wr_id = '$wr_id'";

		$row = sql_fetch($sql);
		return $row;
	}
	

	/**
	 * 
	 * 문서가 존재하는지 확인
	 * 
	 * @param string $ns 네임스페이스(폴더)
	 * @param string $docname 문서명
	 * @return true|false 문서가 존재하면 true 아니면 false
	 */
	public function exists($ns, $docname) {
		$ns = mysql_real_escape_string($ns);
		$docname = mysql_real_escape_string($docname);
		return sql_fetch("SELECT id FROM ".$this->wiki['nsboard_table']." AS nb 
						  LEFT JOIN {$this->wiki['write_table']} AS wt ON nb.wr_id = wt.wr_id 
						  WHERE nb.bo_table = '".$this->wiki['bo_table']."' AND nb.ns = '$ns' AND wt.wr_subject = '$docname'");
	}

	/**
	 * 
	 * 문서를 업데이트 해야할지 말아야할지를 셋팅함.
	 * 
	 * 문서가 업데이트되었을 경우, 업데이트하는 부분에서 문서의 should_update_cache 필드를 1로 셋팅,
	 * 캐시가 업데이트 되었을 경우 0으로 셋팅
	 * 
	 * @param int $wr_id
	 * @param int $value 0 또는 1
	 */
	public function shouldUpdateCache($wr_id, $value) {
		$sql = "UPDATE ".$this->wiki['nsboard_table']." SET should_update_cache = '$value' 
				WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '$wr_id'";
		sql_query($sql);
	}

	/**
	 *
	 * '시작페이지' 반환
	 * 
	 * @return array 시작페이지의 문서 데이터
	 */
	public function getFrontPage() {
		return $this->getArticle("/", $this->board['bo_subject']);
	}

	/**
	 * 
	 * 백링크 반환
	 * (백링크 : 문서를 링크하고 있는 다른 문서들)
	 * 
	 * @param string $doc 문서경로를 포함한 문서명
	 * @param boolean $includeSelf $doc 자신도 백링크에 포함할지 안할지
	 * @return array 문서목록 배열
	 */
	public function getBackLinks($doc, $includeSelf = false)
	{
		$escapedDoc = mysql_real_escape_string($doc);
		$list = array();
		$sql = "SELECT *, wb.wr_subject AS doc FROM ".$this->wiki['write_table']." AS wb 
				LEFT JOIN ".$this->wiki['nsboard_table']." AS nt ON wb.wr_id = nt.wr_id 
				WHERE nt.bo_table= '".$this->wiki['bo_table']."' AND ( wb.wr_content LIKE '%[[".$escapedDoc."]]%' OR wb.wr_content LIKE '%[[".$escapedDoc."|%') AND wb.wr_content NOT LIKE '%[[".$escapedDoc."/%'";
			
		$result = sql_query($sql);
		while($row = sql_fetch_array($result))
		{
			if(!$this->hasInternalLink($row['wr_content'], $doc)) {
				continue;
			}
			$bdoc = ($row['ns'] == "/" ? "/" : $row['ns'] . "/") . $row['doc'];
			if(!$includeSelf && $bdoc == $doc) continue;
				
			$row['href'] = $this->wiki['path']."/narin.php?bo_table=".$this->wiki['bo_table']."&doc=".urlencode($bdoc);
			array_push($list, $row);
		}
		return $list;
	}

	/**
	 * 
	 * 문서 삭제 by id
	 * 
	 * 문서를 삭제하고 문서가 있던 폴더가 비었을 경우 폴더도 삭제
	 * (상위로 경로로 검사하며 빈 폴더를 모두 삭제)
	 * 
	 * @param int $wr_id 문서 id
	 */
	public function deleteArticleById($wr_id)
	{
		$wr_id = mysql_real_escape_string($wr_id);
		$write = $this->getArticleById($wr_id);
		if(!$write) return;

		$sql = "DELETE FROM ".$this->wiki['nsboard_table']." WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '$wr_id'";
		sql_query($sql);

		$this->namespace->checkAndRemove($write['ns']);
	}
		
	/**
	 * 
	 * 그누보드 게시물을  위키에 등록
	 * 
	 * 존재하지 않는 폴더일 경우 폴더 생성 후 문서 등록
	 * 
	 * @param string $doc 문서 경로를 포함한 문서명
	 * @param int $wr_id 문서 id
	 */
	public function addArticle($doc, $wr_id)
	{
		list($ns, $docname, $fullname) = wiki_page_name($doc);
		$this->namespace->addNamespace($ns);
		if($ns) $ns = mysql_real_escape_string($ns);
		if($docname) $docname = mysql_real_escape_string($docname);
		if($fullname) $fullname = mysql_real_escape_string($fullname);
		if($wr_id) $wr_id = mysql_real_escape_string($wr_id);
		$sql = "INSERT INTO ".$this->wiki['nsboard_table']." VALUES ('', '".$this->wiki['bo_table']."', $wr_id, '$ns', '1', '', '')";
		sql_query($sql);
		$sql = "UPDATE ".$this->wiki['write_table']." SET wr_subject = '$docname' WHERE wr_id = $wr_id";
		sql_query($sql);
	}

	/**
	 *
	 * 문서명 변경
	 * 
	 * 주어진 $wr_id 의 문서를 $toDoc 문서로 이름/경로 변경하고,
	 * write_table 의 wr_subject 변경
	 * 
	 * @param string $toDoc 변경될 문서명 (문서 경로를 포함한 문서명)
	 * @param int $wr_id 문서 id
	 */
	public function updateArticle($toDoc, $wr_id)
	{
		list($ns, $docname, $fullname) = wiki_page_name($toDoc);
		$this->namespace->addNamespace($ns);
		if($ns) $ns = mysql_real_escape_string($ns);
		if($docname) $docname = mysql_real_escape_string($docname);
		if($fullname) $fullname = mysql_real_escape_string($fullname);
		if($wr_id) $wr_id = mysql_real_escape_string($wr_id);
		//$sql = "UPDATE ".$this->wiki['nsboard_table']." SET ns='$ns', doc='$docname' WHERE bo_table='".$this->wiki['bo_table']."' AND wr_id='$wr_id'";
		//sql_query($sql);
		$sql = "UPDATE ".$this->wiki['write_table']." SET wr_subject = '$docname' WHERE wr_id = $wr_id";
		sql_query($sql);
	}

	/**
	 * 
	 * 문서 이동
	 * 
	 * @param string $fromDoc 변경전 문서명 (문서경로 포함)
	 * @param string $toDoc 변경후 문서명 (문서경로 포함)
	 * @param int $wr_id 문서 id
	 * @return true|false 이동 성공시 true, 그렇지 않을경우 false
	 */
	public function moveDoc($fromDoc, $toDoc, $wr_id)
	{
		list($toNS, $toDocName, $toFullName) = wiki_page_name($toDoc);
		list($fromNS, $fromDocName, $fromFullName) = wiki_page_name($fromDoc);
		$this->fromDoc = $fromFullName;
		$this->toDoc = $toFullName;

		// 이미 존재한다면 이동하지 않음
		$ex = $this->exists($toNS, $toDocName);
		if($ex) return false;

		$this->updateArticle($toDoc, $wr_id);

		// 백링크 업데이트
		$backLinks = $this->getBackLinks($fromDoc, $includeSelf=true);
		for($i=0; $i<count($backLinks); $i++) {
			$content = $backLinks[$i]['wr_content'];
			$content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$this, 'wikiLinkReplace'), $content));
			
			/* 
			 FIXME : <pre></pre>, <nowiki></nowiki> 에 있는거는 제외하고자 했으나 something wrong
			 $content = preg_replace_callback('/(<pre>)([\s\S]*)(<\/pre>)/i',array($this,"_saveNoWiki"),$content);
			 $content = preg_replace_callback('/(<nowiki>)(.*?)(<\/nowiki>)/i',array($this,"_saveNoWiki"),$content);
			 $content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$this, 'wikiLinkReplace'), $content));
			 $content = preg_replace_callback('/<nowiki><\/nowiki>/i', array($this,"_restoreNoWiki"),$content);
			 */
				
			// 문서 이력에 백업
			$this->history->update($backLinks[$i]['wr_id'], stripcslashes($content), $this->member['mb_id'], "문서명 업데이트로 인한 백링크 자동 업데이트");
			$this->shouldUpdateCache($backLinks[$i]['wr_id'], 1);
				
			sql_query("UPDATE {$this->wiki['write_table']} SET wr_content = '$content' WHERE wr_id = {$backLinks[$i]['wr_id']}");
		}

		$wikiEvent = wiki_class_load("Event");
		$wikiEvent->trigger("MOVE_DOC", array("from"=>$fromFullName, "to"=>$toFullName));

		$this->namespace->checkAndRemove($fromNS);
		
		return true;
	}

	/**
	 * 위키 문서 링크 변경
	 *
	 * $this->moveDoc() 와 {@link NarinNamespace} 에서 호출됨.
	 * 
	 * @param array $matches 위키 링크 매치 결과 ($matches[2] = [[ ]] 안의 내용)
	 * @return string 새로운 링크 
	 */
	public function wikiLinkReplace($matches) {
		$sp = explode("|", $matches[2]);
		if(count($sp) > 1) {
			$doc = $sp[0];
			$opt = "|".$sp[1];
		} else {
			$doc = $sp[0];
			$opt = "";
		}
		if($doc != $this->fromDoc) return "[[".$matches[2]."]]";
		return "[[".$this->toDoc.$opt."]]";
	}

	/**
	 * 
	 * 문서 권한 변경
	 * 
	 * @param string $doc 변경할 문서 (문서 경로를 포함한 문서명)
	 * @param int $access_level 문서 접근 권한
	 * @param int $edit_level 문서 편집 권한
	 */
	function updateLevel($doc, $access_level, $edit_level)
	{
		list($ns, $docname, $fullname) = wiki_page_name($doc);
		$wr = $this->getArticle($ns, $docname, __FILE__, __LINE__);
		if(!$wr) die("No Article");
		$access_level = mysql_real_escape_string($access_level);
		$edit_level = mysql_real_escape_string($edit_level);
		sql_query("UPDATE ".$this->wiki['nsboard_table']." SET access_level = '$access_level', edit_level = '$edit_level' 
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = '".$wr['wr_id']."'");
	}

	/**
	 * 
	 * write_table 에 존재하지 않는 모든 문서 삭제 (오류시 사용)
	 * (write_table : 그누보드 게시판 테이블)
	 * 
	 */
	function removeAllNotExistsDoc()
	{
		sql_query("DELETE FROM ".$this->wiki['nsboard_table']." 
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id NOT IN (  
				   		SELECT wr_id FROM ".$this->wiki['write_table'].")");
	}

	/**
	 * 
	 * content 에 $doc에 대한 문서 링크가 존재하는지 검사
	 * 
	 * @param string $content 문서 내용
	 * @param string $doc 검사할 문서 경로
	 * @return true|false 링크가 존재하면 true, 그렇지 않으면 false
	 */
	function hasInternalLink($content, $doc)
	{
		$text = preg_replace('/<nowiki_block>([\s\S]*)<\/nowiki_block>/i', "",$content);
		$text = preg_replace('/<nowiki>(.*?)<\/nowiki>/i', "",$text);
		$regx = '/\[\['.preg_quote($doc, '/').'(.*?)\]\]/';
		return preg_match($regx, $text);
	}
}

?>