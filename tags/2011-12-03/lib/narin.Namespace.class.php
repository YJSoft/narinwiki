<?
/**
 *
 * 나린위키 네임스페이스(폴더) 클래스 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 네임스페이스(폴더) 클래스
 *
 * 폴더 추가, 삭제, 이동 등 폴더에 관한 처리를 담당하는 클래스.
 *
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiNS= wiki_class_load("Namespace");
 * 
 * // "/narin/plugin" 폴더 추가하기
 * // 이때 "/narin" 폴더가 존재하지 않으면 "/narin" 도 생성됨
 * $wikiNS->addNamespace("/narin/plugin");
 * 
 * // "/narin/plugin" 폴더 삭제하기
 * // 이때 "/narin/plugin" 의 하위 폴더가 있거나, 문서가 있으면 삭제되지 않음
 * // 또는 
 * // 폴더가 비어서 삭제되었으면 상위 폴더도 검사하여 삭제한다.
 * // 재귀적으로 상위폴더들을 검사하면서 빈 폴더를 삭제한다.
 * $wikiNS->checkAndRemove("/narin/plugin");
 *  
 * // "/narin/plugin" 폴더 정보 얻기 (access_level, tpl 등)
 * $ns = $wikiNS->get("/narin/plugin");
 * 
 * // "/narin/plugin" 폴더 내의 폴더/문서 목록 얻기
 * $list = $wikiNS->getList("/narin/plugin");
 * 
 * // "/" 를 최상위 폴더로 하고 "/narin/plugin" 을 현재 폴더로 하는
 * // 트리 HTML 얻기
 * $tree_html = $wikiNS->get_tree("/", "/narin/plugin");
 * 
 * // "/narin/plugin" 폴더의 템플릿 설정하기
 * $wikiNS->setTemplate("/narin/plugin", "== @DOCNAME@ ==");
 * 
 * // "/narin/plugin" 폴더의 접근 권한 설정하기
 * $wikiNS->updateAccessLevel("/narin/plugin", 4);
 * 
 * // "/narin" 폴더를 "/narinwiki" 폴더로 변경하기
 * $wikiNS->updateNamespace("/narin", "/narinwiki");
 * 
 * </code>
 * 
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinNamespace extends NarinClass {

	/**
	 *
	 * 폴더 이름 변경시 사용할 변수
	 *
	 * 이전이름
	 * @var string
	 */
	public $fromDoc;

	/**
	 *
	 * 폴더 이름 변경시 사용할 변수
	 *
	 * 바꿀 이름
	 * @var string
	 */
	public $toDoc;

	/**
	 *
	 * 폴더 추가
	 *
	 * @param string $ns 폴더 e.g. /narin/plugins
	 */
	function addNamespace($ns) {
		$nslists = $this->_getWikiNamespaceArray($ns);
		$bo_table = $this->wiki['bo_table'];
		foreach($nslists as $k => $v) {
			if($v) $v = mysql_real_escape_string($v);
			if($v == "") $v = "/";
			if($k < count($nslists)-1) $has_child = 1;
			else $has_child = 0;
				
			$ns = sql_fetch("SELECT * FROM ".$this->wiki['ns_table']." WHERE bo_table = '$bo_table' AND ns = '$v'");
			if($ns) {
				if(!$ns['has_child'] && $has_child) {
					sql_query("UPDATE ".$this->wiki['ns_table']." SET has_child = 1 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				} else if($ns['has_child'] && !$has_child) {
					sql_query("UPDATE ".$this->wiki['ns_table']." SET has_child = 0 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				}
			} else sql_query("INSERT INTO ".$this->wiki['ns_table']." VALUES ('$v', '".$this->wiki['bo_table']."', '1', $has_child, '')");
		}
	}

	/**
	 *
	 * 폴더 이름 변경
	 *
	 * @param string $srcNS 변경전 이름
	 * @param string $dstNS 변경후 이름
	 */
	function updateNamespace($srcNS, $dstNS)
	{
		if($srcNS == "/") return;
		$wikiArticle = wiki_class_load("Article");

		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedDstNS = mysql_real_escape_string($dstNS);

		// $srcNS 의 하위 ns 를 읽어온다
		$list = wiki_sql_list("SELECT * FROM ".$this->wiki['ns_table']."
						  WHERE ns like '$escapedSrcNS/%' AND bo_table='".$this->wiki['bo_table']."'");		

		foreach($list as $k=>$v) {
			// $srcNS 의 하위 ns 를 업데이트한다.
			$to = preg_replace("/^(".preg_quote($srcNS, "/").")(.*?)/", $dstNS, $v['ns']);
			$this->updateNamespace($v['ns'], $to);
		}

		// $srcNS 를 업데이트한다.
		$this->_updateNamespace($wikiArticle, $srcNS, $dstNS);
	}

	/**
	 *
	 * 폴더 이름 변경
	 *
	 * 실제 이름 변경 매소드이다.
	 * 폴더 이름을 변경하고 폴더이름 변경의 영향을 받는 백링크들을 모두 업데이트한다.
	 * 백링크 업데이트 후 문서 이력으로 남긴다.
	 *
	 * @param WikiArticle $wikiArticle {@link NarinArticle} 객체
	 * @param string $srcNS 변경전 이름
	 * @param string $toNS 변경후 이름
	 */
	function _updateNamespace($wikiArticle, $srcNS, $toNS)
	{
		$wikiHistory = wiki_class_load("History");

		// $srcNS 에 포함된 documents 목록을 읽어온다.
		$list = $this->getList($srcNS, $withArticle = true);

		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedToNS = mysql_real_escape_string($toNS);

		// $srcNS / $document[] 에 대한 백 링크들을 업데이트한다.
		for($i=0; $i<count($list); $i++) {
			if($list[$i][type] == 'folder')	continue;
			$wikiArticle->fromDoc = $fromDoc = $list[$i][path];
			$wikiArticle->toDoc = preg_replace("/^(".preg_quote($srcNS, "/").")(.*?)/", $toNS, $fromDoc);
				
			// 백링크 업데이트
			$backLinks = $wikiArticle->getBackLinks($fromDoc, $includeSelf=true);
				
			for($k=0; $k<count($backLinks); $k++) {

				$content = mysql_real_escape_string(preg_replace_callback('/(\[\[)(.*?)(\]\])/', array(&$wikiArticle, 'wikiLinkReplace'), $backLinks[$k][wr_content]));

				// 문서 이력에 백업
				$wikiHistory->update($backLinks[$i][wr_id], stripcslashes($content), $this->member[mb_id], "폴더명 변경에 따른 자동 업데이트");
				$wikiArticle->shouldUpdateCache($backLinks[$i][wr_id], 1);

				sql_query("UPDATE ".$this->wiki['write_table']." SET wr_content = '$content' WHERE wr_id = ".$backLinks[$k]['wr_id']."");
			}
		}

		sql_query("UPDATE ".$this->wiki['ns_table']." SET ns = '$escapedToNS'
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$escapedSrcNS'", false);				
		sql_query("UPDATE ".$this->wiki['nsboard_table']." SET ns = '$escapedToNS'
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$escapedSrcNS'", false);

		$this->addNamespace($toNS);
		$this->checkAndRemove($srcNS);

		$wikiChanges = wiki_class_load("Changes");
		$wikiChanges->update("FOLDER", $srcNS, "폴더명변경", $this->member[mb_id]);
	}

	/**
	 *
	 * 템플릿 셋팅
	 *
	 * @param string $ns 폴더 경로
	 * @param string $tpl 템플릿 내용
	 */
	public function setTemplate($ns, $tpl) {
		$ns = mysql_real_escape_string($ns);
		$tpl = mysql_real_escape_string($tpl);

		sql_query("UPDATE ".$this->wiki['ns_table']." SET tpl = '$tpl'
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$ns'");				
	}

	/**
	 *
	 * 접근 권한 변경
	 * @param string $ns 폴더 경로
	 * @param int $level 접근 권한
	 */
	function updateAccessLevel($ns, $level)
	{
		if($ns) $ns = mysql_real_escape_string($ns);
		if($level) $level = mysql_real_escape_string($level);
		sql_query("UPDATE ".$this->wiki['ns_table']." SET ns_access_level = '$level'
				   WHERE bo_table = '".$this->wiki['bo_table']."' AND ns LIKE '$ns%'");						
	}


	/**
	 *
	 * 폴더 삭제
	 *
	 * 하위 폴더 또는 폴더내에 문서가 없을 경우 삭제하고,
	 * 폴더가 삭제되었을 경우 상위 폴더를 재귀적으로 호출하여 불필요한 폴더들을 삭제한다.
	 *
	 * @param string $ns 폴더 경로
	 */
	function checkAndRemove($ns) {

		if($ns == "/") return;

		$escapedNS = mysql_real_escape_string($ns);

		// load ns
		$namespace = sql_fetch("SELECT * FROM ".$this->wiki['ns_table']." AS ns
								LEFT JOIN ".$this->wiki['nsboard_table']." AS nb ON ns.bo_table = nb.bo_table AND ns.ns = nb.ns 
								WHERE ns.bo_table = '".$this->wiki['bo_table']."' AND ns.ns = '$escapedNS'");

		// if there's no document
		if(!$namespace[wr_id]) {
				
			// if there's no child, delete
			$child = sql_fetch("SELECT * FROM ".$this->wiki['ns_table']." WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child['ns']) {
				sql_query("DELETE FROM ".$this->wiki['ns_table']."
							WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$escapedNS'");

				// call recursivly about parent
				$this->checkAndRemove(wiki_get_parent_path($ns));
			}
		} else {
			// if there's no child, set has_child false
			$child = sql_fetch("SELECT * FROM ".$this->wiki['ns_table']." WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child['ns']) {
				sql_query("UPDATE ".$this->wiki['ns_table']." SET has_child = 0
							WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$escapedNS'");
			}
		}
	}

	/**
	 *
	 * 폴더내의 폴더/문서들 목록 반환
	 *
	 * @param string $parent 목록을 만들 폴더경로
	 * @param boolean $withArticle true 일 경우 문서도 목록을 만들고, 그렇지 않으면 폴더 목록만 반환
	 * @return array 폴더/문서 목록
	 */
	function namespaces($parent = "/", $withArticle=true) {
		$escapedParent = mysql_real_escape_string($parent);

		$sql = "SELECT ns FROM ".$this->wiki['ns_table']."
				WHERE bo_table = '".$this->wiki['bo_table']."' AND ns LIKE '{$escapedParent}%' 
				ORDER BY ns";		
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.wr_subject AS doc FROM ".$this->wiki['ns_table']." AS nt
					LEFT JOIN ".$this->wiki['nsboard_table']." AS nb ON nt.ns = nb.ns 
					LEFT JOIN ".$this->wiki['write_table']." AS wb ON nb.wr_id = wb.wr_id 
					WHERE nt.bo_table = '".$this->wiki['bo_table']."' AND nt.ns LIKE '{$escapedParent}%' 
					ORDER BY nt.ns";
		}
		$result = sql_query($sql);
		$list = array();
		while ($row = sql_fetch_array($result))
		{
			$full = ($row['ns'] == "/" ? "/" :  $row['ns']);
			if($withArticle) $full = ($row['ns'] == "/" ? "/" :  $row['ns'] . "/") . $row['doc'];
			$real_path = $full;
			if($parent == "/") {
				$full = "/".$this->wiki['tree_top']. $full;
			}
			$list[$full] = $real_path;
		}
		return $list;
	}

	/**
	 *
	 * 폴더 정보 반환
	 *
	 * @param string $ns 폴더 경로
	 * @return array 폴더 정보 배열
	 */
	function get($ns) {
		$ns = mysql_real_escape_string($ns);
		$row = sql_fetch("SELECT * FROM ".$this->wiki['ns_table']." WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$ns'");
		return $row;
	}

	/**
	 *
	 * 폴더내 폴더/문서 목록 반환
	 *
	 * @todo $this->namespaces() 와 겹치는 듯?
	 * @param string $parent 타겟 폴더 경로
	 * @param boolean $withArticle 문서도 목록으로 만들지 여부
	 * @return array 폴더/파일 목록
	 */
	function getList($parent = "/", $withArticle=true) {
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($parent == "/" ? "/" : $escapedParent."/");
		if($parent != "/") {
			$add =	"nt.ns = '$escapedParent' OR ";
			$addSlash = "/";
		}

		$sql = "SELECT *  FROM ".$this->wiki['ns_table']."
				WHERE $add ns LIKE '$escapedParent%' 
						AND ns NOT REGEXP '^$regp(.*)/' 
						AND bo_table ='".$this->wiki['bo_table']."'";
		if($withArticle) {
			$sql = "SELECT nt.ns, nt.bo_table, wb.mb_id, wb.wr_name, mb.mb_nick, wb.wr_subject AS doc, wb.wr_id, wb.wr_datetime, ht.editor_mb_id AS editor, ht.reg_date AS update_date, wb.wr_good, wb.wr_nogood, wb.wr_comment, wb.wr_hit
					FROM ".$this->wiki['ns_table']." AS nt 
					LEFT JOIN ".$this->wiki['nsboard_table']." AS nb ON nt.ns = nb.ns AND nt.bo_table = nb.bo_table 
					LEFT JOIN ".$this->wiki['write_table']." AS wb ON nb.wr_id = wb.wr_id 
					LEFT JOIN ".$this->g4['member_table']." AS mb ON mb.mb_id = wb.mb_id 
					LEFT JOIN ".$this->wiki['history_table']." AS ht ON nb.wr_id = ht.wr_id 					
					WHERE $add nt.ns LIKE '$escapedParent$addSlash%' 
						AND nt.ns NOT REGEXP '^$regp(.*)/' 
						AND nt.bo_table = '".$this->wiki['bo_table']."' 
					GROUP BY wb.wr_id 
					ORDER BY wb.wr_subject";
		}

		$folders = array();
		$files = array();
		$already = array();
		$result = sql_query($sql);
		while ($row = sql_fetch_array($result))
		{
			if(!$row['update_date']) $row['update_date'] = $row['wr_datetime'];
			if(!$row['editor']) $row['editor'] = ($row['mb_id'] ? $row['mb_id'] : $row['mb_name']);
			if($row['ns'] == $parent) {
				if(!$row['doc']) continue;
				$row['name'] = $row['doc'];
				$row['path'] = ($row['ns'] == "/" ? "/" : $row['ns']."/").$row['doc'];
				$row['href'] = $this->wiki[path].'/narin.php?bo_table='.$this->wiki['bo_table'].'&doc='.urlencode($row['path']);
				$row['internal_link'] = "[[".$row['path']."]]";
				$row['type'] = 'doc';				
				array_push($files, $row);
			} else {
				$row['href'] = $this->wiki[path].'/folder.php?bo_table='.$this->wiki['bo_table'].'&loc='.urlencode($row['ns']);
				$name = ereg_replace($parent."/", "", $row['ns']);
				$row['name'] = ereg_replace($parent, "", $name);
				$row['path'] = $row['ns'];
				if($already[$name]) continue;
				$already[$name] = $name;
				$row['internal_link'] = "[[folder=".$row['ns']."]]";
				$row['type'] = 'folder';
				unset($row['editor']);
				unset($row['wr_hit']);
				unset($row['wr_name']);
				unset($row['mb_id']);
				unset($row['wr_name']);
				unset($row['wr_comment']);
				unset($row['wr_nogood']);
				unset($row['wr_good']);				
				array_push($folders, $row);
			}
			//if($this->is_admin) print_r2($row);
		}
		if(count($folders)) $folders = wiki_subval_asort($folders, "name");
		$list = array_merge($folders, $files);

		return $list;
	}


	/**
	 *
	 * 폴더 트리 반환
	 *
	 * 폴더 구조를  HTML 로 반환.
	 *
	 * @see folder.php
	 * @see media_get_tree.php
	 * @param string $parent 타겟 폴더 경로
	 * @param string $current 현재 폴더 (optional)
	 * @return string 트리 구조 HTML
	 */
	public function get_tree($parent = "/", $current="") {
		$pname = basename($parent);
		if(!$pname) $pname = $this->wiki['tree_top'];
		$escapedParent = mysql_real_escape_string($parent);
		$res = sql_query("SELECT ns, ns_access_level FROM ".$this->wiki['ns_table']."
						  WHERE ns LIKE '$escapedParent%' AND bo_table = '".$this->wiki['bo_table']."' ORDER BY ns");
		$list = array();
		while($row = sql_fetch_array($res)) {
			if($row['ns'] == '/' || $this->member['mb_level'] < $row['ns_access_level']) {
				continue;
			}
			array_push($list, $row);
		}
		if(!preg_match("/\/$/", $parent)) $parent .= "/";
		$tree = $this->_build_tree($list);
		$tree_html = $this->_build_list($tree, "/", $current);

		if($parent == "/") return '<ul class="narin_tree filetree">
									<li class="open">
										<span class="root folder">
											<a href="folder.php?bo_table='.$this->wiki['bo_table'].'&loc='.urlencode($parent).'">'
											.$pname.
											'</a>
										</span>'
										.$tree_html.
									'</li>
								</ul>';
										else return $tree_html;
	}

	/**
	 *
	 * 폴더 목록으로 트리 배열 생성
	 *
	 * @param array $path_list 폴더 목록 배열
	 * @return array 트리만들 배열
	 */
	protected function _build_tree($path_list) {
		$path_tree = array();
		foreach ($path_list as $idx=>$row) {
			$list = explode('/', trim($row['ns'], '/'));
			$last_dir = &$path_tree;
			foreach ($list as $dir) {
				$last_dir =& $last_dir[$dir];
			}
		}
		return $path_tree;
	}

	/**
	 *
	 * 트리 배열로 트리 HTML 생성
	 *
	 * @param array $tree _build_tree 에서 만들어지 트리 배열
	 * @param string $prefix prefix 로 사용할 부모 폴더 경로
	 * @param string $current 현재 폴더 경로
	 * @return string 트리 HTML
	 */
	function _build_list($tree, $prefix = '', $current = '') {
		$url = $this->wiki['path'].'/folder.php?bo_table='.$this->wiki['bo_table'].'&loc=';
		$ul = '';
		foreach ($tree as $key => $value) {
			$li = '';
			$folder = $prefix.$key;
			if(preg_match("/^".preg_quote($folder, "/")."/", $current)) $class = ' class="open"';
			else $class = '';
			$link = $url . '' . urlencode($folder);
			if (is_array($value)) {
				$li .= '<li'.$class.'><span class="folder"><a href="'.$link.'">'.$key.'</a></span>';
				$sub = $this->_build_list($value, "$prefix$key/", $current);
				if($sub) $li .= $sub;
				$ul .= $li.'</li>';
			} else {
				if($class != '') $closed = "";
				else $closed = " leaf_folder";
				$ul .= '<li'.$class.'><span class="folder '.$closed.'"><a href="'.$link.'">'.$key.'</a></span></li>';
			}
		}
		return strlen($ul) ? sprintf('<ul>%s</ul>', $ul) : '';
	}

		

	/**
	 *
	 * 모든 빈 폴더 삭제
	 *
	 * 하위 폴더나 문서가 없는 모든 폴더 삭제
	 */
	function removeAllEmptyNS()
	{
		$res = sql_query("SELECT * FROM ".$this->wiki['ns_table']."
							WHERE has_child = 0 AND (ns, bo_table) NOT IN 
								( 
									SELECT ns, bo_table FROM ".$this->wiki['nsboard_table']." 
										WHERE bo_table = '".$this->wiki['bo_table']."'
								)
						");
		while($row = sql_fetch_array($res)) {
			$this->checkAndRemove($row['ns']);
		}
	}

	/**
	 *
	 * 폴더 경로로 부터 폴더 목록을 만들어 반환
	 *
	 * e.g.> if ( $ns = "park/chongmyung" )
	 *       return array("park", "park/chongmyung")
	 *
	 * @param string $ns 폴더 경로
	 * @return array 폴더 목록
	 */
	function _getWikiNamespaceArray($ns) {
		$array = explode("/", $ns);
		$list = array();
		$node = "";
		for($i=0; $i<count($array); $i++) {
			if(!$i) $node = $array[$i];
			else $node = $node . "/" . $array[$i];
			array_push($list, $node);
		}
		return $list;
	}

}

?>