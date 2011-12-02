<?
/**
 *
 * 나린위키 캐시(Cache) 클래스 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 캐시(Cache) 클래스
 *
 * 문서 캐시 관리를 위한 클래스.
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiCache = wiki_class_load("Cache");
 * 
 * // wr_id = 79 인 문서의 저장된 HTML 캐시 가져오기
 * $content = $wikiCache->get(79);
 * 
 * // wr_id = 79 인 문서의 캐시 업데이트하기
 * $wikiParser = wiki_class_load("Parser");	// 파서 로드
 * $content = $wikiParser->parse($view);	// 파싱
 * $wikiCache->update(79, $content);		// 파싱된 결과 저장
 * 
 * // wr_id = 79 인 문서의 캐시 삭제
 * $wikiCache->delete(79);
 * 
 * // 모든 캐시 초기화
 * $wikiCache->clear();
 * 
 * </code>
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinCache extends NarinClass {
	
	/**
	 * 
	 * 해당 글에 대한 캐시 반환
	 * 
	 * @param int $wr_id 글 id
	 * @return string parsing 되어 저장된 cache
	 */
	public function get($wr_id) {
		$row = sql_fetch("SELECT content FROM ".$this->wiki['cache_table']." 
							WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = $wr_id");

		// @todo 동작 확인해야 함
		// cache 된 내용이 없으면 parsing 수행
		if(!$row) {			
			$write = sql_fetch(" select * from ".$this->wiki['write_table']." where wr_id = '$wr_id' ");
			$wikiParser = wiki_class_load("Parser");
			$content = mysql_real_escape_string($wikiParser->parse($write));
			sql_query("INSERT INTO ".$this->wiki['cache_table']." VALUES ('', '".$this->wiki['bo_table']."', $wr_id, '$content')");
			return $content;
		}
		
		return $row['content'];
	}
	
	/**
	 * 
	 * 캐시 생성/업데이트
	 * 
	 * @param int $wr_id 글 id
	 * @param string $content 내용 (parsing 된 html)
	 */
	public function update($wr_id, $content) {		
		$content = mysql_real_escape_string($content);
		$ex = $this->get($wr_id);
		if(!$ex) sql_query("INSERT INTO ".$this->wiki['cache_table']." VALUES ('', '".$this->wiki['bo_table']."', $wr_id, '$content')");
		else {
			
			sql_query("UPDATE ".$this->wiki['cache_table']." 
						SET content = '$content' 
						WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = $wr_id");
			
			sql_query("UPDATE ".$this->wiki['nsboard_table']." 
						SET should_update_cache = 0 
						WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = $wr_id");
		}
	}
	
	/**
	 * 
	 * 캐시 삭제
	 * 
	 * @param int $wr_id 글 아이디
	 */
	public function delete($wr_id) {
		sql_query("DELETE FROM ".$this->wiki['cache_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."' AND wr_id = $wr_id");
	}
	
	/**
	 * 
	 * 모든 캐시 초기화
	 * 
	 */
	public function clear() {
		sql_query("DELETE FROM ".$this->wiki['cache_table']." 
					WHERE bo_table = '".$this->wiki['bo_table']."'");
		sql_query("UPDATE ".$this->wiki['nsboard_table']." 
					SET should_update_cache = 1 
					WHERE bo_table = '".$this->wiki['bo_table']."'");		
	}

}

?>