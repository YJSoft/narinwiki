<?
/**
 * 
 * 나린위키 미디어 클래스
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 미디어 클래스
 *
 * 미디어 관리자에 필요한 기능을 수행하는 클래스.
 * 
 * <b>특징</b>
 * - 미디어 관리자의 폴더는 위키 문서의 폴더와는 별개로 구성된다. 
 * - 한 폴더에 같은 이름의 파일이 존재할 수 없다.
 *
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiMedia =& wiki_class_load("Media");
 * 
 * // 미디어 폴더 추가하기
 * // "/images/example/plugin" 폴더를 만드는데
 * // 기존에 있던 "/images" 폴더의 권한과 동일하도록 만든다.
 * // 이때, "/images/example" 폴더도 같이 만들어진다.
 * $wikiMedia->addNamespace("/images/example/plugin", "/images");
 * 
 * // 파일 등록하기
 * // data 폴더에 "p0291248dsa.jpg" 로 저장되어있는 "bag.jpg" 파일을
 * // "/images/example" 폴더에 등록하는 방법
 * $wikiMedia->addFile("/images/example", "bag.jpg", "p0291248dsa.jpg");
 * 
 * // "/images/example/bag.jpg" 파일 삭제하기
 * $wikiMedia->deleteFile("/images/example", "bag.jpg"); 
 * 
 * // "/images/example" 폴더 삭제하기
 * // 폴더에 하위 폴더가 있거나, 등록된 파일이 있으면 삭제되지 않음
 * $wikiMedia->deleteFolder("/images/example"); 
 *  
 * // DB에 등록되어 있지 않은 파일을 data 폴더에서 삭제하기
 * // (uploading 중 취소된 파일은 data 폴더에 남아있고, db에는 등록되지 않을 수 있음)
 * $wikiMedia->deleteUnusedFile("p0291248dsa.jpg");
 * 
 * // DB에 등록된 파일 정보 읽어오기
 * $wikiMedia->getFile("/images/example", "bag.jpg");
 * 
 * // 폴더내 파일 목록 가져오기 ({@link getList()} 참조)
 * $files = $wikiMedia->getList("/images/example");
 * 
 * // 폴더 정보 가져오기
 * $folder_info = $wikiMedia->getNS("/images/example");
 * 
 * // 폴더 권한 설정
 * // 접근 : 2, 업로드 : 5, 폴더생성/삭제 : 9
 * $wikiMedia->updateLevel("/images", 2, 5, 9);
 * 
 * // 폴더 트리구조 HTML 가져오기
 * // 루트폴더를 "/" 로 하고 현재 폴더를 "/images/example" 로 설정해서
 * // tree html 구성
 * $tree_html = $wikiMedia->get_tree("/", "/images/example");
 * 
 * // 하위 폴더가 있는지 검사
 * $wikiMedia->hasSubFolder("/images");
 * 
 * // 다운로드 카운트 업데이트 +1
 * $wikiMedia->updateDownloadCount(파일아이디);
 * </code>
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinMedia extends NarinClass {

	/**
	 *
	 * @var string 데이터가 저장될 경로
	 */
	var $path;

	/**
	 *
	 * 생성자
	 */
	function __construct() {
		parent::__construct();
		$this->path = $this->wiki['path']."/data/".$this->wiki['bo_table']."/files/";
	}

	/**
	 *
	 * 파일 등록
	 *
	 * @param string $ns 폴더경로
	 * @param string $source 파일원본이름
	 * @param string $filename 저장된파일이름
	 * @return 0|-1 성공시 0, 실패시 -1
	 */
	function addFile($ns, $source, $filename)
	{
		if(!$this->member['mb_id']) return -1;
		$file_path = $this->path.$filename;
		if(!file_exists($file_path)) return -1;

		$image = @getimagesize($file_path);
		$file_size = filesize($file_path);

		$this->addNamespace($ns);

		$ns = mysql_real_escape_string($ns);
		$source = mysql_real_escape_string($source);
		$filename = mysql_real_escape_string($filename);

		sql_query("INSERT INTO ".$this->wiki['media_table']." SET
							bo_table = '".$this->wiki['bo_table']."', 
							ns = '$ns', 
							source = '$source', 
							file = '$filename',
							filesize = '$file_size',
							img_width = '$image[0]',
							img_height = '$image[1]',
							img_type = '$image[2]',
							mb_id = '".$this->member['mb_id']."'"); 
		if(mysql_error()) return -1;

		return 0;
	}

	/**
	 *
	 * 파일 직접 삭제
	 *
	 * 업로딩중 중단되었거나 DB에 등록안되어있는 파일을 지울 때 사용
	 *
	 * @param string $filename 삭제할 파일명
	 * @return string 삭제된 파일명
	 */
	function deleteUnusedFile($filename) {
		@unlink($this->path.$filename);
		return $this->path.$filename;
	}

	/**
	 *
	 * 파일 삭제
	 *
	 * DB에 등록된 정보를 삭제하고
	 * 디스크에서 실제 파일 삭제
	 *
	 * @param string $ns 폴더경로
	 * @param string $file 파일명
	 */
	function deleteFile($ns, $file)
	{
		$file_info = $this->getFile($ns, $file);
		$ns = mysql_real_escape_string($ns);
		$file = mysql_real_escape_string($file);
		sql_query("DELETE FROM ".$this->wiki['media_table']." WHERE ns = '$ns' AND source = '$file'");
		@unlink($this->path.$file_info['file']);
	}

	/**
	 *
	 * 폴더 삭제
	 *
	 * DB에서 폴더 삭제
	 *
	 * @param string $ns 폴더 경로
	 * @return true|false 폴더가 삭제되었으면 true, 실패면 false
	 */
	function deleteFolder($ns)
	{
		$list = $this->getList($ns);
		if(count($list) > 0) return false;	// 빈폴더가 아님
		if($this->hasSubFolder($ns)) return false; // 서브폴더가 있음
		$ns = mysql_real_escape_string($ns);
		sql_query("DELETE FROM ".$this->wiki['media_ns_table']." WHERE ns = '$ns'");
		return true;
	}

	/**
	 *
	 * 폴더 정보 반환
	 *
	 * @param string $ns 폴더 경로
	 * @return array 폴더정보배열
	 */
	function getNS($ns) {
		$ns = mysql_real_escape_string($ns);
		$row = sql_fetch("SELECT * FROM ".$this->wiki['media_ns_table']." 
							WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$ns'");
		return $row;
	}

	/**
	 *
	 * 하위폴더가 존재하는지 검사
	 *
	 * @param string $ns 폴더 경로
	 * @return true|false 하위폴더가 존재하면 true, 없으면 false
	 */
	function hasSubFolder($ns) {
		$escapedNS = mysql_real_escape_string($ns);
		// if there's no child, delete
		$child = sql_fetch("SELECT * FROM ".$this->wiki['media_ns_table']." 
							WHERE ns LIKE '{$escapedNS}/%'");
		if(!$child['ns']) return false;
		else return true;
	}

	/**
	 *
	 * 파일 정보 반환
	 *
	 * @param string $ns 폴더 경로
	 * @param string $file 파일명
	 * @return array 파일정보배열 : array("id"=>파일id,
	 * 									 "ns"=>폴더경로,
	 * 									 "source"=>파일원본이름,
	 * 									 "file"=>저장된파일이름,
	 * 									 "path"=>파일저장경로(파일명포함),
	 * 									 "href"=>다운로드URL,
	 * 									 "imgsrc"=>이미지SRC (href와 같음),
	 * 									 "filesize"=>파일크기(bytes),
	 * 									 "downloads"=>다운로드카운트,
	 * 									 "reg_date"=>파일등록시간,
	 * 									 "img_width"=>이미지너비(px),
	 * 									 "img_height"=>이미지높이(px),
	 * 									 "img_type"=>이미지유형,
	 * 									 "mb_id"=>파일등록자 아이디,
	 * 									 "mb_name"=>파일등록자 이름,
	 * 									 "mb_nick"=>파일등록자 닉네임)
	 */
	function getFile($ns, $file = '') {

		if($ns && !$file) {
			list($ns, $file, $full) = wiki_page_name($ns);
		}
		$ns = mysql_real_escape_string($ns);
		$file = mysql_real_escape_string($file);

		$sql = "SELECT m.id, m.ns, m.source, m.file, m.filesize, m.downloads, m.reg_date, m.img_width, m.img_height, m.img_type, m.mb_id, mb.mb_name, mb.mb_nick
					  FROM ".$this->wiki['media_table']." AS m
					  LEFT JOIN {$this->g4['member_table']} AS mb 
					  	ON m.mb_id = mb.mb_id 
					  WHERE m.ns = '$ns' AND m.source = '$file' AND m.bo_table = '".$this->wiki['bo_table']."' 
					  ";		


		$row = sql_fetch($sql);
		if(!$row) return null;
		$row['path'] = $this->wiki['path'].'/data/'.$this->wiki['bo_table'].'/files/'.$row['file'];
		$row['href'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));
		$row['imgsrc'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));
		return $row;
	}

	/**
	 *
	 * 다운로드 카운트 업데이트
	 *
	 * @param int $id 파일아이디
	 */
	function updateDownloadCount($id) {
		sql_query("UPDATE ".$this->wiki['media_table']." 
					SET downloads = downloads + 1 WHERE id = '$id'");
	}

	/**
	 *
	 * 폴더 추가
	 *
	 * $parent 폴더의 권한과 같은 폴더 $ns 를 만든다.
	 * $ns가 "/폴더1/폴더2/폴더3/폴더4" 이고,
	 * $parent가 "/폴더1" 이라면
	 * "/폴더1/폴더2", "/폴더1/폴더2/폴더3", "/폴더1/폴더2/폴더3/폴더4"
	 * 의 세 폴더가 $parent 와 같은 권한으로 폴더 생성됨.
	 *
	 * @param string $ns 폴더경로
	 * @param string $parent 권한설정을 따를 부모폴더경로
	 */
	function addNamespace($ns, $parent = null) {
			
		$nslists = $this->_getWikiNamespaceArray($ns);
		$bo_table = $this->wiki['bo_table'];
		if($parent) {
			$al = $parent['ns_access_level'];
			$ul = $parent['ns_upload_level'];
			$cl = $parent['ns_mkdir_level'];
		} else {
			$al = 1; $ul = 2; $cl = 9;
		}
		foreach($nslists as $k => $v) {
			if($v) $v = mysql_real_escape_string($v);
			if($v == "") $v = "/";
			if($k < count($nslists)-1) $has_child = 1;
			else $has_child = 0;
				
			$ns = sql_fetch("SELECT * FROM ".$this->wiki['media_ns_table']." 
								WHERE bo_table = '$bo_table' AND ns = '$v'");
			if($ns) {
				if(!$ns['has_child'] && $has_child) {
					sql_query("UPDATE ".$this->wiki['media_ns_table']." SET has_child = 1 
								WHERE  bo_table = '$bo_table' AND ns = '$v'");
				} else if($ns['has_child'] && !$has_child) {
					sql_query("UPDATE ".$this->wiki['media_ns_table']." SET has_child = 0 
								WHERE  bo_table = '$bo_table' AND ns = '$v'");
				}
			} else sql_query("INSERT INTO ".$this->wiki['media_ns_table']." (ns, bo_table, ns_access_level, ns_upload_level, ns_mkdir_level, has_child) 
								VALUES ('$v', '".$this->wiki['bo_table']."', $al, $ul, $cl, $has_child)");
		}
		return true;
	}

	/**
	 *
	 * 폴더 권한 설정
	 *
	 * @param string $ns 폴더경로
	 * @param int $access_level 접근 권한
	 * @param int $upload_level 업로드 권한
	 * @param int $mkdir_level 폴더생성/삭제 권한
	 */
	function updateLevel($ns, $access_level, $upload_level, $mkdir_level)
	{
		if($ns) $ns = mysql_real_escape_string($ns);
		if($access_level) $access_level = mysql_real_escape_string($access_level);
		if($upload_level) $upload_level = mysql_real_escape_string($upload_level);
		if($mkdir_level) $mkdir_level = mysql_real_escape_string($mkdir_level);
		sql_query("UPDATE ".$this->wiki['media_ns_table']."
							 SET ns_access_level = '$access_level', ns_upload_level = '$upload_level', ns_mkdir_level = '$mkdir_level' 
							 WHERE bo_table = '".$this->wiki['bo_table']."' AND ns = '$ns'");
	}

	/**
	 *
	 * 폴더내 파일 목록 반환
	 *
	 * @param string $parent 폴더경로
	 * @return array 파일정보배열 : array(array("id"=>파일id,
	 * 									 "ns"=>폴더경로,
	 * 									 "source"=>파일원본이름,
	 * 									 "file"=>저장된파일이름,
	 * 									 "path"=>파일저장경로(파일명포함),
	 * 									 "href"=>다운로드URL,
	 * 									 "imgsrc"=>이미지SRC (href와 같음),
	 * 									 "filesize"=>파일크기(bytes),
	 * 									 "downloads"=>다운로드카운트,
	 * 									 "reg_date"=>파일등록시간,
	 * 									 "img_width"=>이미지너비(px),
	 * 									 "img_height"=>이미지높이(px),
	 * 									 "img_type"=>이미지유형,
	 * 									 "mb_id"=>파일등록자 아이디,
	 * 									 "mb_name"=>파일등록자 이름,
	 * 									 "mb_nick"=>파일등록자 닉네임))
	 */
	function getList($parent = "/") {
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($parent == "/" ? "/" : $escapedParent."/");

		$sql = "SELECT m.id, nt.ns, m.source, m.file, m.filesize, m.downloads, m.reg_date, m.img_width, m.img_height, m.img_type, m.downloads, m.mb_id, mb.mb_name, mb.mb_nick, m.reg_date
					  FROM ".$this->wiki['media_ns_table']." AS nt 
					  LEFT JOIN ".$this->wiki['media_table']." AS m 
					  	ON nt.ns = m.ns AND nt.bo_table = m.bo_table 
					  LEFT JOIN {$this->g4['member_table']} AS mb 
					  	ON m.mb_id = mb.mb_id 
					  WHERE nt.ns = '$escapedParent' AND nt.bo_table = '".$this->wiki['bo_table']."' 
					  ORDER BY m.reg_date DESC";

		$files = array();
		$result = sql_query($sql);
		while ($row = sql_fetch_array($result))
		{
			if($row['ns'] == $parent) {
				if(!$row['source']) continue;
				//if(is_callable($filter) && !$filter($row)) continue;
				$row['path'] = $this->wiki['path'].'/data/'.$this->wiki['bo_table'].'/files/'.$row['file'];
				$row['href'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));
				$row['imgsrc'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&w=img&file='.urlencode(wiki_doc($row['ns'], $row['source']));
				array_push($files, $row);
			}
		}
		return $files;
	}
	

	/**
	 *
	 * 폴더 내 모든 파일 삭제 (DB & 파일)
	 *
	 *
	 */	
	function clear($loc) {
		if(!$this->is_wiki_admin) return false;
		
		$files = $this->getList($loc);
		foreach($files as $k=>$file) {
			@unlink($this->path.$file['file']);
		}
		
		$loc = mysql_real_escape_string($loc);
		sql_query("DELETE FROM ".$this->wiki['media_table']." WHERE ns = '$loc'");
		
		return true;
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
		$res = sql_query("SELECT ns, ns_access_level FROM ".$this->wiki['media_ns_table']." 
							WHERE ns LIKE '$escapedParent%' AND bo_table = '".$this->wiki['bo_table']."' 
							ORDER BY ns");
		$list = array();
		$found = false;
		while($row = sql_fetch_array($res)) {
			if($row['ns'] == '/' || $this->member['mb_level'] < $row['ns_access_level']) {
				continue;
			}
			if($current == $row['ns']) {
				$found = true;
			}
			array_push($list, $row);
		}
		if(trim($current) != '' && $current != '/' && !$found) {
			echo "";
			exit;
		}
		if(!preg_match("/\/$/", $parent)) $parent .= "/";
		$tree = $this->_build_tree($list);
		$tree_html = $this->_build_list($tree, "/", $current);

		if($parent == "/") return '<ul class="narin_tree filetree"><li class="open"><span class="root folder"><a href="media.php?bo_table='.$this->wiki['bo_table'].'&loc='.urlencode($parent).'" code="'.wiki_input_value($parent).'">'.$pname.'</a></span>'.$tree_html.'</li></ul>';
		else return $tree_html;
	}

	/**
	 *
	 * 폴더 목록으로 트리 배열 생성
	 *
	 * @param array $path_list 폴더 목록 배열
	 * @return array 트리만들 배열
	 */
	function _build_tree($path_list) {
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
		$url = $this->wiki['path'].'/media.php?bo_table='.$this->wiki['bo_table'].'&loc=';
		$ul = '';
		foreach ($tree as $key => $value) {
			$li = '';
			$folder = $prefix.$key;
			if(preg_match("/^".preg_quote($folder, "/")."/", $current)) $class = ' class="open"';
			else $class = '';
			$link = $url . '' . urlencode($folder);
			if (is_array($value)) {
				$li .= '<li'.$class.'><span class="folder"><a href="'.$link.'" code="'.wiki_input_value($folder).'">'.$key.'</a></span>';
				$sub = $this->_build_list($value, "$prefix$key/", $current);
				if($sub) $li .= $sub;
				$ul .= $li.'</li>';
			} else {
				if($class != '') $closed = " leaf";
				else $closed = " leaf leaf_folder";
				$ul .= '<li'.$class.'><span class="folder '.$closed.'"><a href="'.$link.'" code="'.wiki_input_value($folder).'">'.$key.'</a></span></li>';
			}
		}
		return strlen($ul) ? sprintf('<ul>%s</ul>', $ul) : '';
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
