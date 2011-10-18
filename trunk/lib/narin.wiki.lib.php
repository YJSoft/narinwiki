<?
if (!defined('_GNUBOARD_')) exit;

include_once $wiki[path]."/lib/narin.Class.class.php";

// wiki.extend 에서도 사용되어야 하기 때문에..
$wikiConfig = wiki_class_load("Config");
$skin = ( $wikiConfig->setting[skin] ? $wikiConfig->setting[skin] : "basic");
$wiki[skin_path] = $wiki[path]."/skin/board/".$skin;
$wiki[inc_skin_path] = $wiki[path]."/inc/skin";
$wiki[head_file] = $wikiConfig->setting[head_file];
$wiki[tail_file] = $wikiConfig->setting[tail_file];

if(!$board) $board = sql_fetch(" select * from {$g4['board_table']} where bo_table = '{$wiki[bo_table]}' ");
if($is_admin || ($member[mb_id] && $board[bo_admin] == $member[mb_id]) ) $is_wiki_admin = true;
else unset($is_wiki_admin);

$wikiEvent = wiki_class_load("Event");

/**
 * Load given class and return instance
 */
function wiki_class_load($className) {
	
	global $wiki;
	
	static $loadedClasses = array();
	
	$classFile = $wiki[path] . "/lib/narin.".$className.".class.php";
	if($loadedClasses[$classFile]) {
		return $loadedClasses[$classFile];
	}
	
	if(!file_exists($classFile)) return null;
	
	include_once $classFile;
	$realClassName = "Narin".$className;
	if(class_exists($realClassName)) {
		$instance = new $realClassName();	
		$loadedClasses[$classFile] = $instance;		
		return $instance;
	}
	
	return null;
}


/**
 * Parse page name as namespace and doc
 */
function wiki_page_name($pagename, $strip = true)
{
	if($strip) $pagename = stripcslashes($pagename);
	
	$array = explode("/", $pagename);	
	if($array[0] == "") array_shift($array);
	$doc = "/" . implode("/", $array);
	$docname = array_pop($array);
	$ns = strtolower("/" . implode("/", $array));
	return array($ns, $docname, $doc);
}

/**
 * 경로 포함 문서 제목 형식 검사 
 */
function wiki_validate_doc($doc)
{
	$wikiControl = wiki_class_load("Control");
	
	list($ns, $docname, $doc) = wiki_page_name($doc, $strip=true);	
	
	if(!wiki_check_folder_name($ns)) {
		$wikiControl->error("폴더명 오류", "폴더명에 다음 문자는 사용할 수 없습니다 : \\, |");
	}
	
	if(!wiki_check_doc_name($docname)) {
		$wikiControl->error("문서명 오류", "문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /");
	}	
	return array($ns, $docname, $doc);
}

/**
 * 폴더 제목 형식 검사
 */
function wiki_validate_folder($ns)
{
	$wikiControl = wiki_class_load("Control");
	if(!wiki_check_doc_name($docname)) {
		$wikiControl->error("문서명 오류", "문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /");
	}		
}

/**
 * 문서명 검사
 * 문서명에 /, \, | 안됨
 */
function wiki_check_doc_name($name)
{
	$pattern = "/[\|\/\\\\]/u";
	return !preg_match($pattern, $name, $matches);
}

/**
 * 폴더명 검사
 * 폴더명에 \, | 안됨
 */
function wiki_check_folder_name($name)
{
	$pattern = "/[\|\\\\]|[\/]{2,}/u";
	return !preg_match($pattern, $name, $matches);
}

/**
 * 큰 따옴표 처리 : " => &#034
 */
function wiki_input_value($v)
{
	return str_replace("\"", "&#034;", $v);	
}


/**
 * 'view.skin.php' 과 'view_comment.skin.php' 에서
 * 스크립트 경로 수정하기 위함
 */
function wiki_adjust_path($path) {
	global $g4;
	return str_replace("./", $g4[bbs_path]."/", $path);
}

/**
 * 'write.skin.php' 에서
 * 문서 제목을 설정하기 위함
 */
function wiki_doc_from_write($doc, $wr_id)
{
	$wikiArticle = wiki_class_load("Article");
	if(!$doc) {
		$write = $wikiArticle->getArticleById($wr_id);
		$doc = ($write[ns] == "/" ? "" : $write[ns]) . "/" . $write[wr_subject];
	}
	
	list($ns, $doc, $full) = wiki_page_name($doc, $strip=false);	
	return array(get_text($doc), get_text($full));
}

/**
 * 폴더 네비게이션 리턴
 */
function wiki_navigation($doc, $isNS=false) {
	global $wiki;
	
	if(!$isNS) list($ns, $docname, $fullpath) = wiki_page_name($doc, $strip=true);
	else $ns = $doc;

	$path = explode("/", $ns);
	
	$wiki_navigation = "<a href='{$wiki[path]}/folder.php?bo_table={$wiki[bo_table]}'>Home</a> > ";
	$hrefPath = "";
	for($i=0; $i<count($path); $i++) {
		if($path[$i]) {
			$hrefPath .= "/".$path[$i];
			$wiki_navigation .= " <a href='{$wiki[path]}/folder.php?bo_table={$wiki[bo_table]}&loc=".urlencode($hrefPath)."'>".$path[$i]."</a> > ";
		}
	}	
	return $wiki_navigation . " <a href='{$wiki[path]}/narin.php?bo_table={$wiki[bo_table]}&doc=".urlencode($doc)."'>$docname</a>";
}

/**
 * 문서 경로에서 부모 폴더 경로 추출
 */
function wiki_get_parent_path($path)
{
	$arr = explode("/", $path);
	array_pop($arr);
	$p = implode("/", $arr);
	return ($p == "" ? "/" : $p);
}

/**
 * sql_query 에 대한 결과를 배열로 리턴 (귀차니즘)
 */
function sql_list($sql) {	
	$result = sql_query($sql);
	$list = array();	
	while ($row = sql_fetch_array($result))
	{	
		array_push($list, $row);
	}
	return $list;		
}

/**
 * 위키 옵션을 얻음
 */
function wiki_get_option($name, $field="")
{
	global $wiki;	
	$name = mysql_real_escape_string($name);
	
	$opt = sql_fetch("SELECT content FROM $wiki[option_table] WHERE name = '/{$wiki[bo_table]}/$name'");	
	
	if($opt) {		
		$json = json_decode($opt[content], $assoc=true);
		if($field) {
			if(isset($json[$field])) return $json[$field];
		}
		return $json;		
	}
	
}

/**
 * 위키 옵션을 셋 함
 */
function wiki_set_option($name, $field, $value)
{
	global $wiki;
	
	$eName = mysql_real_escape_string($name);
	
	$opt = wiki_get_option($name);
		
	if($opt) {	// 저장된 옵션이 있다면 수정
		
		// 필드와 값이 모두 배열이면..
		if(is_array($field) && is_array($value)) {
			
			// 필드와 값이 갯수가 같아야 함
			if(count($field) != count($value)) return;
			
			for($i=0; $i<count($field); $i++) {
				$opt[$field[$i]] = $value[$i];
			}
		} else if(!is_array($field) && !is_array($value)) {
			$opt[$field] = $value;
		} else return;
				
		$json_string = mysql_real_escape_string(json_encode($opt));
		$sql = "UPDATE {$wiki[option_table]} SET content = '$json_string' WHERE name = '/{$wiki[bo_table]}/$eName'";
	
	} else {		// 저장된 옵션이 없다면 삽입				
		
		if(is_array($field) && is_array($value)) {
			// 필드와 값이 갯수가 같아야 함
			if(count($field) != count($value)) return;

			$data = array();

			for($i=0; $i<count($field); $i++) {
				$data[$field[$i]] = $value[$i];
			}
 		} else if(!is_array($field) && !is_array($value)) {
 			$data = array("$field"=>$value);
		} else return;
		
		$json = mysql_real_escape_string(json_encode($data));			
		$sql = "INSERT INTO	{$wiki[option_table]} VALUES ('/{$wiki[bo_table]}/$eName', '$json')";
	}
	sql_query($sql);
}


/**
 * 검색어 결과에서 검색어 폰트 처리
 */
function wiki_search_font($stx, $str)
{
	return str_ireplace($stx, '<span class="wiki_search_word">' . $stx . '</span>', $str);
}

/**
 * Html to Text
 */
function wiki_text($content)
{
	$content = html_symbol($content);		
	$content = get_text($content, 0);		
	return $content;
}

/**
 * Text to Html
 */
function wiki_html($content) {
  $html = html_entity_decode($content);
  $html = str_replace("&#039;", "'", $html);
  $html = str_replace("&#038;", "&", $html);   	
  return $html;
}

/**
 * 위키 스킨 목록
 */
function wiki_get_skins($skin, $len='')
{
    global $wiki;

    $result_array = array();

    $dirname = "$wiki[path]/skin/$skin/";
    $handle = opendir($dirname);
    while ($file = readdir($handle)) 
    {
        if($file == "."||$file == "..") continue;

        if (is_dir($dirname.$file)) $result_array[] = $file;
    }
    closedir($handle);
    sort($result_array);

    return $result_array;
}

/**
 * 위키 플러그인 목록 로드
 */
function wiki_plugin_load()
{
	global $wiki;
	
	include_once $wiki[path]."/lib/narin.Plugin.class.php";
	include_once $wiki[path]."/lib/narin.PluginInfo.class.php";
	include_once $wiki[path]."/lib/narin.SyntaxPlugin.class.php";
	include_once $wiki[path]."/lib/narin.ActionPlugin.class.php";
	
	$plugins = array();	
	$plugin_dir = "{$wiki[path]}/plugins";
	$d = dir($plugin_dir);
	while ($entry = $d->read()) {
		$pluginPath = $plugin_dir ."/". $entry;
		if(is_dir($pluginPath) && substr($entry, 0, 1) != ".") {

			$syntaxFile = $pluginPath ."/syntax.php";
			$actionFile = $pluginPath ."/action.php";
			
			$p = array();

			// syntax plugin
			if(file_exists($syntaxFile)) {
				$realClassName = "NarinSyntax".ucfirst($entry);			
				include_once $syntaxFile;	
				if(class_exists($realClassName)) {
					$plugin = new $realClassName();
					array_push($p, array("type"=>"syntax", "instance"=>$plugin));
				}
			}			

			// action plugin
			if(file_exists($actionFile)) {
				$realClassName = "NarinAction".ucfirst($entry);					
				include_once $actionFile;	
				if(class_exists($realClassName)) {
					$plugin = new $realClassName();
					array_push($p, array("type"=>"action", "instance"=>$plugin));
				}
			}			

			array_push($plugins, array("name"=>$entry, "info"=>wiki_plugin_info($entry), "plugins"=>$p));						
		}
	}	
	return $plugins;
}

/**
 * 플러그인 정보 클래스 로드
 */
function wiki_plugin_info($plugin)
{
	global $wiki;
	static $loadedInfo = array();
		
	include_once $wiki[path]."/lib/narin.PluginInfo.class.php";
	
	$classFile = $wiki[path] . "/plugins/".$plugin."/info.php";
	if($loadedInfo[$classFile]) {
		return $loadedInfo[$classFile];
	}
	
	if(!file_exists($classFile)) return null;

	include_once $classFile;
	$realClassName = "NarinPluginInfo".ucfirst($plugin);

	if(class_exists($realClassName)) {
		$instance = new $realClassName();	
		$loadedInfo[$classFile] = $instance;	
		return $instance;		
	}
	
	return null;
}

/**
 * 폴더명과 문서명을 합침
 */
function wiki_doc($ns, $docname) {
	return ($ns == "/" ? "" : $ns ) . "/" . $docname;
}

/**
 * 연관배열 키 기준으로 정렬 (asort)
 */
function wiki_subval_asort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	asort($b);
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}

/**
 * 연관배열 키 기준으로 정렬 (sort)
 */
function wiki_subval_sort($a,$subkey) {
	$c = subval_asort($a, $subkey);
	$c = array_reverse($c);
	return $c;
}

/**
 * EUC-KR 버전인가?
 */
function wiki_is_euckr() {
	global $g4;
	return $g4[charset] == 'euc-kr';
}

/**
 * URL 로 넘어온 데이터를 euc-kr 인코딩으로 변환
 */
function wiki_url_data($data) {
	if(wiki_is_euckr() && mb_detect_encoding($data) == "UTF-8") {
		return iconv("UTF-8", "CP949", rawurldecode($data)); 
	}
	return $data;
}

/**
 * ajax 가 아니면 페이지 없음 표시
 */
function wiki_only_ajax() {
	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !$_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
		wiki_not_found_page();
	}
}

/**
 * 페이지 없음
 */
function wiki_not_found_page() {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * 디버그 용
 */
function wiki_debug($str, $h=400)
{
	echo "<textarea style='width:100%;height:".$h."px'>";
	if(is_array($str)) print_r($str);
	else echo $str;
	echo "</textarea>";
}

/**
 * 한줄 디버그 용
 */
function wiki_print($str)
{
	echo "==> $str <br/>";
}
?>