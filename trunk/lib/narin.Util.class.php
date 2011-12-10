<?
/**
 *
 * 나린위키 유틸리티 클래스 스크립트
 * 
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 유틸리티 클래스
 * 
 * 현재 위키문서링크 수정과 nowiki 를 제거한 내용을 반환하는 기능만 있음.
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiUtil = wiki_class_load("Util");
 * 
 * // $content 에서 nowiki, pre, code 블럭을 뺀 내용 가져오기
 * $content = $wikiUtil->no_nowiki_content($content);


 * </code>
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinUtil extends NarinClass
{
	/**
	 *
	 * @var array nowiki 내용 저장
	 */
	var $nowikis;

	/**
	 *
	 * @var string 현재 패턴 id (pre, nowiki, code)
	 */
	var $current_pattern_id;

	/**
	 *
	 * @var array nowiki 패턴 배열
	 */
	protected $nowiki_patterns;

	/**
	 *
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
		$this->nowiki_patterns = array(
		array("start_regex"=>"<pre>", "end_regex"=>"<\/pre>", "id"=>"pre"),
		array("start_regex"=>"<nowiki>", "end_regex"=>"<\/nowiki>", "id"=>"nowiki"),
		array("start_regex"=>"<code(.*?)>", "end_regex"=>"<\/code>", "id"=>"code")
		);
	}

	/**
	 *
	 * '/' 로 시작하지 않는 문서 제목에 현재 폴더 경로를 덧붙임
	 *
	 * @see lib/actions/on_write_update.php
	 * @see lib/actions/on_write_comment_update.php
	 * @param string $wr_content 문서 내용
	 * @return string 문서 링크 경로가 수정된 내용
	 */
	public function wiki_fix_internal_link($wr_content) {

		$nowikis = array();
		$content = $this->nowiki_backup($wr_content, $nowikis);
		$content = preg_replace_callback('/(\[\[)(.*?)(\]\])/', array($this, 'wiki_add_folder_to_link'), $content);
		$content = $this->nowiki_restore($content, $nowikis);

		return $content;
	}

	/**
	 *
	 * '/' 로 시작하지 않는 문서 제목에 현재 폴더 경로를 덧붙임 (실제루틴)
	 *
	 * @param string $matches 패턴 매칭 결과
	 * @return string 문서 링크 경로가 수정된 내용
	 */
	protected function wiki_add_folder_to_link($matches) {

		$link = $matches[2];
		if(preg_match("/^#/", $link)) {
			return $matches[0];
		}
		$prefix = "[[";
		$subfix = "]]";

		if(!preg_match("/^\//", $link))	return $prefix.($this->folder == "/" ? "" : $this->folder)."/".$link.$subfix;
		else return $matches[0];
	}

	/**
	 *
	 * nowiki, pre, code 태그를 제외한 내용 반환
	 *
	 * @param string $wr_content 문서 내용
	 * @return string nowiki 들이 제거된 문서 내용
	 */
	public function no_nowiki_content($wr_content) {
		$nowikis = array();
		$content = $this->nowiki_backup($wr_content, $nowikis);
		return $content;
	}


	/**
	 *
	 * nowiki 들을 백업
	 *
	 * @param string $content 문서 내용
	 * @param array $nowikis nowiki 들이 저장될 버퍼
	 * @return  string nowiki 들이 제거된 문서 내용
	 */
	public function nowiki_backup($content, &$nowikis) {
		$this->nowikis = &$nowikis;
		foreach($this->nowiki_patterns as $pattern) {
			$this->current_pattern_id = $pattern[id];
			$regex = "/".$pattern[start_regex]."(.*?)".$pattern[end_regex] . "/si";
			$content = preg_replace_callback($regex, array($this,"_saveNoWikiBlock"), $content);
		}
		return $content;
	}
	
	/**
	 *
	 * nowiki 들을 백업 : 실제 루틴
	 *
	 * @param array $matches 패턴 매칭 결과
	 * @return string nowiki 태그
	 */
	protected function _saveNoWikiBlock($matches)
	{
		$id = $this->current_pattern_id;
		if(!isset($this->nowikis[$id])) $this->nowikis[$id] = array();
		array_push($this->nowikis[$id], $matches[0]);
		return "<$id></$id>";
	}

	/**
	 *
	 * nowiki 들을 복원
	 *
	 * @param sitring $content 문서 내용
	 * @param array $nowikis nowiki 들이 저장된 버퍼
	 * @return string nowiki 태그
	 */
	public function nowiki_restore($content, &$nowikis) {

		$this->nowikis = &$nowikis;
		asort($this->nowiki_patterns);
		foreach($this->nowiki_patterns as $pattern) {
			$this->current_pattern_id = $id = $pattern[id];
			$regex = "/<".$pattern[id]."><\/".$pattern[id] . ">/i";
			$content = preg_replace_callback($regex, array($this,"_restoreNoWikiBlock"), $content);
		}
		asort($this->nowiki_patterns);
		return $content;
	}

	/**
	 *
	 * nowiki 들을 복원 : 실제 루틴
	 *
	 * @param array $matches 패턴 매칭 결과
	 * @return string 복원된 내용
	 */
	protected function _restoreNoWikiBlock($matches)
	{
		$id = $this->current_pattern_id;
		$nowiki = $this->nowikis[$id][0];
		array_shift($this->nowikis[$id]);
		return $nowiki;
	}

}
?>