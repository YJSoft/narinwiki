<?
/**
 * 나린위키 미디어 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */


class NarinMedia extends NarinClass {
	
	var $fromDoc;
	var $toDoc;
	var $path;
	
	protected $cache = array();
	
	function __construct() {
		parent::__construct();
		$this->path = $this->wiki['path']."/data/".$this->wiki['bo_table']."/files/";
	}

	/**
	 * Add file
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
		
		sql_query("INSERT INTO {$this->wiki['media_table']} SET
							bo_table = '{$this->wiki['bo_table']}', 
							ns = '$ns', 
							source = '$source', 
							file = '$filename',
							filesize = '$file_size',
							img_width = '{$image[0]}',
							img_height = '{$image[1]}',
							img_type = '{$image[2]}',
							mb_id = '{$this->member['mb_id']}'"); 
		if(mysql_error()) return -1;
		
		return 0;
	}
	
	/**
	 * delete file from disk
	 */
	function deleteUnusedFile($filename) {
		@unlink($this->path.$filename);
		return $this->path.$filename;
	}
	
	/**
	 * delete media
	 */
	function deleteFile($ns, $file)
	{
		$file_info = $this->getFile($ns, $file);
		$ns = mysql_real_escape_string($ns);
		$file = mysql_real_escape_string($file);
		sql_query("DELETE FROM {$this->wiki['media_table']} WHERE ns = '$ns' AND source = '$file'");
		@unlink($this->path.$file_info['file']);
	}

	function deleteFolder($ns)
	{
		$list = $this->getList($ns);
		if(count($list) > 0) return false;	// 빈폴더가 아님
		if($this->hasSubFolder($ns)) return false; // 서브폴더가 있음		
		$ns = mysql_real_escape_string($ns);
		sql_query("DELETE FROM {$this->wiki['media_ns_table']} WHERE ns = '$ns'");		
		return true;
	}

	/**
	 * Return namespace
	 */
	function getNS($ns) {
		$ns = mysql_real_escape_string($ns);		
		$row = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$ns'");
		return $row;
	}			

	function hasSubFolder($ns) {
		$escapedNS = mysql_real_escape_string($ns);
		// if there's no child, delete
		$child = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} WHERE ns LIKE '{$escapedNS}/%'");
		if(!$child['ns']) return false;
		else return true;
	}
	
	/**
	 * Return file info
	 */
	function getFile($ns, $file = '') {
				
		if($ns && !$file) {
			list($ns, $file, $full) = wiki_page_name($ns, $strip=false);
		}
		$ns = mysql_real_escape_string($ns);
		$file = mysql_real_escape_string($file);		
		
		$sql = "SELECT m.id, m.ns, m.source, m.file, m.filesize, m.downloads, m.reg_date, m.img_width, m.img_height, m.img_type, m.downloads, m.mb_id, mb.mb_name, mb.mb_nick, m.reg_date
					  FROM {$this->wiki['media_table']} AS m
					  LEFT JOIN {$this->g4['member_table']} AS mb 
					  	ON m.mb_id = mb.mb_id 
					  WHERE m.ns = '$ns' AND m.source = '$file' AND m.bo_table = '{$this->wiki['bo_table']}' 
					  ";		
		
		
		$row = sql_fetch($sql);
		if(!$row) return null;
		$row['path'] = $this->wiki['path'].'/data/'.$this->wiki['bo_table'].'/files/'.$row['file'];
		$row['href'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));
		$row['imgsrc'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));		
		return $row;
	}
	
	/**
	 * Update download count
	 */
	function updateDownloadCount($id) {
		sql_query("UPDATE {$this->wiki['media_table']} SET downloads = downloads + 1 WHERE id = '$id'");
	}

	/**
	 * Add namespace
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
			
			$ns = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} WHERE bo_table = '$bo_table' AND ns = '$v'");
			if($ns) {
				if(!$ns['has_child'] && $has_child) {
					sql_query("UPDATE {$this->wiki['media_ns_table']} SET has_child = 1 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				} else if($ns['has_child'] && !$has_child) {
					sql_query("UPDATE {$this->wiki['media_ns_table']} SET has_child = 0 WHERE  bo_table = '$bo_table' AND ns = '$v'");
				}
			} else sql_query("INSERT INTO {$this->wiki['media_ns_table']} (ns, bo_table, ns_access_level, ns_upload_level, ns_mkdir_level, has_child) VALUES ('$v', '{$this->wiki['bo_table']}', $al, $ul, $cl, $has_child)");
		}
		return true;
	}
	
	/**
	 * Update namespace
	 */
	function updateNamespace($srcNS, $dstNS)
	{
		if($srcNS == "/") return;

		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedDstNS = mysql_real_escape_string($dstNS);
		
		// $srcNS 의 하위 ns 를 읽어온다
		$list = sql_list("SELECT * FROM {$this->wiki['media_ns_table']} WHERE ns like '$escapedSrcNS/%' AND bo_table='{$this->wiki['bo_table']}'");		
		
		foreach($list as $k=>$v) {
			// $srcNS 의 하위 ns 를 업데이트한다.
			$to = preg_replace("/^(".preg_quote($srcNS, "/").")(.*?)/", $dstNS, $v['ns']);
			$this->updateNamespace($v['ns'], $to);
		}
		
		// $srcNS 를 업데이트한다.
		$this->_updateNamespace($srcNS, $dstNS);		
	}
	
	/**
	 * Update namespace (real routine)
	 */
	function _updateNamespace($srcNS, $toNS)
	{		
		$escapedSrcNS = mysql_real_escape_string($srcNS);
		$escapedToNS = mysql_real_escape_string($toNS);
		
		sql_query("UPDATE {$this->wiki['media_ns_table']} SET ns = '$escapedToNS' WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$escapedSrcNS'", false);				
		sql_query("UPDATE {$this->wiki['media_table']} SET ns = '$escapedToNS' WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$escapedSrcNS'", false);
		
		$this->addNamespace($toNS);
		$this->checkAndRemove($srcNS);
	}		
		
	/**
	 * Update access level
	 */
	function updateLevel($ns, $access_level, $upload_level, $mkdir_level)
	{
		if($ns) $ns = mysql_real_escape_string($ns);			
		if($access_level) $access_level = mysql_real_escape_string($access_level);			
		if($upload_level) $upload_level = mysql_real_escape_string($upload_level);			
		if($mkdir_level) $mkdir_level = mysql_real_escape_string($mkdir_level);			
		sql_query("UPDATE {$this->wiki['media_ns_table']} 
							 SET ns_access_level = '$access_level', ns_upload_level = '$upload_level', ns_mkdir_level = '$mkdir_level' 
							 WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$ns'");
	}
	
	
	/**
	 * Add namespace
	 */
	function checkAndRemove($ns) {		

		if($ns == "/") return;
	
		$escapedNS = mysql_real_escape_string($ns);
		
		// load ns
		$namespace = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} AS ns LEFT JOIN {$this->wiki['media_table']} AS m ON ns.bo_table = m.bo_table AND ns.ns = m.ns WHERE ns.bo_table = '{$this->wiki['bo_table']}' AND ns.ns = '$escapedNS'");
	
		// if there's no media
		if(!$namespace['id']) {
			
			// if there's no child, delete
			$child = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child['ns']) {				
				sql_query("DELETE FROM {$this->wiki['media_ns_table']} WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$escapedNS'");
				
				// call recursivly about parent
				$this->checkAndRemove(wiki_get_parent_path($ns));
			}
		} else {
			// if there's no child, set has_child false
			$child = sql_fetch("SELECT * FROM {$this->wiki['media_ns_table']} WHERE ns LIKE '{$escapedNS}/%'");
			if(!$child['ns']) {				
				sql_query("UPDATE {$this->wiki['media_ns_table']} SET has_child = 0 WHERE bo_table = '{$this->wiki['bo_table']}' AND ns = '$escapedNS'");
			}			
		}
	}	


	
	/**
	 * Return namespaces and files array
	 */
	function getList($parent = "/") {		
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($parent == "/" ? "/" : $escapedParent."/");	

		$sql = "SELECT m.id, nt.ns, m.source, m.file, m.filesize, m.downloads, m.reg_date, m.img_width, m.img_height, m.img_type, m.downloads, m.mb_id, mb.mb_name, mb.mb_nick, m.reg_date
					  FROM {$this->wiki['media_ns_table']} AS nt 
					  LEFT JOIN {$this->wiki['media_table']} AS m 
					  	ON nt.ns = m.ns AND nt.bo_table = m.bo_table 
					  LEFT JOIN {$this->g4['member_table']} AS mb 
					  	ON m.mb_id = mb.mb_id 
					  WHERE nt.ns = '$escapedParent' AND nt.bo_table = '{$this->wiki['bo_table']}' 
					  ORDER BY m.reg_date DESC";
		
		$files = array();
		$result = sql_query($sql);
		while ($row = sql_fetch_array($result))	
		{
			if($row['ns'] == $parent) {
				if(!$row['source']) continue;		
				$row['path'] = $this->wiki['path'].'/data/'.$this->wiki['bo_table'].'/files/'.$row['file'];
				$row['href'] = $this->wiki['path'].'/media_download.php?bo_table='.$this->wiki['bo_table'].'&file='.urlencode(wiki_doc($row['ns'], $row['source']));
				$row['imgsrc'] = $this->wiki['path'].'/exe/media_download.php?bo_table='.$this->wiki['bo_table'].'&w=img&file='.urlencode(wiki_doc($row['ns'], $row['source']));		
				array_push($files, $row);
			}
		}
		return $files;
	}
	
	/**
	 * Return tree structure (in HTML)
	 */
	public function get_tree($parent = "/", $current="") {
		$pname = basename($parent);
		if(!$pname) $pname = $this->wiki['tree_top'];		
		$escapedParent = mysql_real_escape_string($parent);
		$res = sql_query("SELECT ns, ns_access_level FROM {$this->wiki['media_ns_table']} WHERE ns LIKE '$escapedParent%' AND bo_table = '{$this->wiki['bo_table']}' ORDER BY ns");
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
	 * Build tree as array
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
	 * Build tree HTML with tree array
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
	 * Remove all empty namespaces
	 */
	function removeAllEmptyNS()
	{
		$res = sql_query("SELECT * FROM {$this->wiki['media_ns_table']} WHERE has_child = 0 AND (ns, bo_table) NOT IN ( SELECT ns, bo_table FROM {$this->wiki['media_table']} WHERE bo_table = '{$this->wiki['bo_table']}')");
		while($row = sql_fetch_array($res)) {
			$this->checkAndRemove($row['ns']);
		}
	}
	
	
	/**
	 * Return namespace list from string
	 * e.g.> if ( $ns = "park/chongmyung" )
	 *       return array("park", "park/chongmyung")
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