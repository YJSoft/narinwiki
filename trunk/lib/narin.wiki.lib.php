<?
/**
 *
 * 나린위키 공용 라이브러리 모음
 *
 * 나린위키의 이곳저곳에서 사용하는 함수들입니다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

if (!defined('_GNUBOARD_')) exit;

// 패스 설정
// fancy url 지원을 위해 절대경로와 URL경로로 구분하여 사용
define("WIKI_PATH", dirname(dirname(__FILE__)));
define("G4_PATH", realpath(WIKI_PATH.'/'.$wiki['g4_path']));

// URL 에 URL ENCODING 을 안쓰기 위함
if(wiki_is_euckr()) {
	wiki_euckr($_GET);
	wiki_euckr($_POST);
	@extract($_GET);
	@extract($_POST);
}

// 최 상위 클래스 include
include_once WIKI_PATH."/lib/narin.Class.class.php";

// wiki.extend 에서도 사용되어야 하기 때문에 common.php 가 아니라 이곳에서 설정
$wiki['write_table'] = $g4['write_prefix'] . $bo_table;
$wiki['inc_skin_path'] = WIKI_PATH."/inc/skin";

// 환경 설정
$wikiConfig =& wiki_class_load("Config");
$wiki['head_file'] = $wikiConfig->setting['head_file'];
$wiki['tail_file'] = $wikiConfig->setting['tail_file'];
$skin = ( $wikiConfig->setting['skin'] ? $wikiConfig->setting['skin'] : "basic");
$wiki['skin_path'] = WIKI_PATH."/skin/board/".$skin;
$board_skin_path = $wiki['inc_skin_path'];

// 게시판이 로드 안되어있다면, 로드
if(!$board) $board = sql_fetch(" select * from {$g4['board_table']} where bo_table = '".$wiki['bo_table']."' ");
if($is_admin || ($member['mb_id'] && $board['bo_admin'] == $member['mb_id']) ) $is_wiki_admin = true;
else unset($is_wiki_admin);

$wiki['front'] = $board['bo_subject'];

if($wiki['fancy_url']) {
	// fancy url 뒤에 ? 붙여서 파라미터를 전송하기 위함
	$args = array();
	parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $args);
	if(!empty($args)) {
		extract($args);
		foreach($args as $k => $v) $_GET[$k] = $v;
	}
}

// $doc, $loc 변수 셋팅
$loc = str_replace('+', ' ', stripcslashes($loc));
$doc = str_replace('+', ' ', stripcslashes($doc));
if($loc && $doc) $doc = $loc."/".$doc;
else if(!$doc && $wr_doc) $doc = $wr_doc;
else if(!$doc && $wr_id) {
	$wikiArticle =& wiki_class_load("Article");
	$wr = $wikiArticle->getArticleById($wr_id);
	$doc = wiki_doc($wr['ns'], $wr['doc']);
} else if(!$doc) $doc = "/".$wiki['front'];
$doc = preg_replace('/\/+/', '/', $doc);
list($ns, $docname, $doc) = wiki_validate_doc($doc);


// 이벤트 오브젝트 로드
$wikiEvent =& wiki_class_load("Event");

// 스킨 패스/URL 설정
// fancy url 지원을 위해 절대경로와 URL경로로 구분하여 사용
define(SKIN_PATH, WIKI_PATH.'/skin/board/'.$skin);
define(SKIN_URL, $wiki['url'].'/skin/board/'.$skin);

/**
 *
 * 나린위키 클래스 로더
 *
 * 사용법 :
 * <code>
 * // lib/narin.Article.class.php 를 로드하려면..
 * $wikiArticle =& wiki_class_load("Article");
 *
 * // lib/narin.Changes.class.php 를 로드하려면..
 * $wikiChanges =& wiki_class_load("Changes");
 * </code>
 *
 * @param string $className 약식 클래스명 (사용법 참고)
 * @return NarinClass 클래스 인스턴스
 */
function & wiki_class_load($className) {

	global $wiki;

	static $loadedClasses = array();

	$classFile = WIKI_PATH . "/lib/narin.".$className.".class.php";
	if(isset($loadedClasses[$classFile])) {
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
 *
 * 나린위키 페이지 URL 생성 함수
 * (fancy url 과 query url로 나눠서 생성)
 *
 * @uses 시작페이지 => wiki_url();
 * @uses '/테스트문서' 읽기 => wiki_url('read', array('doc'=>'/테스트문서'));
 * @uses '/테스트문서#첫번째문단' 읽기 => wiki_url('read', array('doc'=>'/테스트문서#첫번째문단'));
 * @uses '/테스트문서' 문서이력 => wiki_url('history', array('doc'=>'/테스트문서'));
 * @uses '/테스트문서' 의 100번 문서이력 보기 => wiki_url('read', array('doc'=>'/테스트문서', 'hid'=>100));
 * @uses 최근변경내역 => wiki_url('recent');
 * @uses 차이 => wiki_url('diff', array('hid'=>100));
 * @uses '나린위키' 검색wiki_url('search', array('stx'=>'나린위키'));
 */
function wiki_url()
{
	global $wiki;
	if($wiki['fancy_url']) return wiki_fancy_url(func_get_args());
	else return wiki_query_url(func_get_args());
}

/**
 *
 * 쿼리 URL 생성
 *
 */
function wiki_query_url($args)
{
	global $wiki;

	// 파라미터가 없는 경우... 첫 페이지로
	if(empty($args)) return $wiki['url'];

	// 경로 설정
	$file = $args[0].'.php';
	array_shift($args);

	// read 는 narin.php 로 맵핑
	if($file == 'read.php') $file = 'narin.php';

	// 쿼리 스트링 생성
	$qarr = array();
	foreach($args as $k=>$v) {
		if(is_array($v)) {
			foreach($v as $kk=>$vv) array_push($qarr, $kk .'='.urlencode($vv));
		}
	}

	$qury_string = str_replace(' ', '+', '?'.implode('&', $qarr));
	return $wiki['url'] . '/' . $file . $qury_string;
}

/**
 *
 * 팬시 URL 생성
 *
 * $args 의 두번째 파라미터로 넘어오는 연관배열을 / 로 구분하여 URL 생성
 *
 * $args = array('read', array('doc'=>'테스트문서')) 면
 * $wiki['url'].'/read/테스트문서' 반환
 *
 * $args = array('read', array('doc'=>'테스트문서', 'hid'=100)) 면
 * $wiki['url'].'/read/테스트문서?hid=100' 반환
 *
 * $args = array('search', array('stx'=>'위키', 'page'=>'')) 면
 * $wiki['url'].'/search/위키?page=' 반환 (이 경우는 pageing url 생성할 때 사용)
 *
 */
function wiki_fancy_url($args)
{
	global $wiki, $is_wiki_admin;

	// 파라미터가 없는 경우... 첫 페이지로
	if(empty($args)) return $wiki['url'] ;

	// 경로 설정
	$mode = array_shift($args);

	$rest = '';
	$params = array_shift($args);

	if(!is_array($params)) {		
		return $wiki['url'].'/'.$mode .'/';
	}

	// URL 경로 설정
	$first = array_shift($params);

	// 해쉬 분리
	list($url_path, $hash) = explode('#', $first);
	if($hash) $hash = '#'.$hash;

	// URL 경로 설정
	$rest = '/'.preg_replace('/^\//', '', $url_path);

	// 쿼리 스트링 생성
	$qarr = array();
	foreach($params as $k=>$v) {
		array_push($qarr, $k .'='.urlencode($v));
	}
	if(!empty($qarr)) $qury_string = '?'.implode('&', $qarr);
	else $qury_string = '';
		
	// 기본 URL
	return $wiki['url'].'/'.$mode. str_replace(' ', '+', $rest.$qury_string).$hash;
}


/**
 *
 * 경로를 포함한 문서명을 분석
 *
 * 사용법 :
 * <code>
 * // /폴더1/폴더2/문서 ..를 폴더경로와 문서명으로 분리하는 방법
 * list($folder, $docname, $doc) = wiki_page_name("/폴더1/폴더2/문서");
 * echo "폴더 : $folder / 문서명 : $docname / 문서경로 : $doc";
 * </code>
 *
 * @param string $pagename 경로를 포함한 문서명
 * @return array array(폴더경로, 문서명, 경로포함문서명)
 */
function wiki_page_name($pagename)
{
	$pagename = preg_replace('/\/$/', '', $pagename);
	$array = explode("/", $pagename);
	if($array[0] == "") array_shift($array);
	$doc = "/" . implode("/", $array);
	$docname = array_pop($array);
	$tmp = explode('#', $docname);
	$docname = $tmp[0];
	$ns = strtolower("/" . implode("/", $array));
	return array($ns, $docname, $doc);
}

/**
 *
 * 문서명 유효성 검사
 *
 * 폴더명에는 역슬래쉬(\)와 파이프(|)를 사용할 수 없고,
 * 문서명에는 역슬래쉬(\)와 파이프(|), 슬래쉬(/)를 사용할수 없다.
 * 유효성검사에 실패하면 에러 페이지를 보여준다.
 *
 * @param string $doc 경로를 포함한 문서명
 * @return array array(폴더경로, 문서명, 경로포함문서명)
 */
function wiki_validate_doc($doc)
{
	$wikiControl =& wiki_class_load("Control");

	list($ns, $docname, $doc) = wiki_page_name($doc);

	if(!wiki_check_folder_name($ns)) {
		$wikiControl->error("폴더명 오류", "폴더명에 다음 문자는 사용할 수 없습니다 : \\, |, #, ?, +");
	}

	if(!wiki_check_doc_name($docname)) {
		$wikiControl->error("문서명 오류", "문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /, #, ?, +");
	}
	return array($ns, $docname, $doc);
}

/**
 *
 * 폴더명 유효성 검사
 *
 * 유효성 검사 실패시 에러 페이지를 보여준다.
 *
 * @param string $ns 폴더 경로
 */
function wiki_validate_folder($ns)
{
	$wikiControl =& wiki_class_load("Control");
	if(!wiki_check_doc_name($docname)) {
		$wikiControl->error("문서명 오류", "문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /, #, +");
	}
}

/**
 *
 * 문서명 유효성 검사
 *
 * 문서명에는 역슬래쉬(\)와 파이프(|), 슬래쉬(/), 샾(#), 물음표(?), 더하기(+) 를 사용할수 없다.
 *
 * @param string $name 문서명 (경로제외)
 * @return true|false 유효성 검사 통과시 true, 실패시 false
 */
function wiki_check_doc_name($name)
{
	$pattern = "/[\|\/\\\\#\?\+]/u";
	return !preg_match($pattern, $name, $matches);
}

/**
 *
 * 폴더경로 유효성 검사
 *
 * 문서명에는 역슬래쉬(\)와 파이프(|), 샾(#), 물음표(?), 더하기(+) 를 사용할수 없다.
 *
 * @param string $name 폴더경로
 * @return true|false 유효성 검사 통과시 true, 실패시 false
 */
function wiki_check_folder_name($name)
{
	$pattern = "/[\|\\\\#\+]|[\/]{2,}/u";
	return !preg_match($pattern, $name, $matches);
}

/**
 *
 * input 태그에서 사용하기 위한 문자열 변환
 *
 * 큰따옴표(")를  &#034 로 바꿔준다.
 *
 * @param string $v 문자열
 * @retrun string 변환된 문자열
 */
function wiki_input_value($v)
{
	return str_replace("\"", "&#034;", $v);
}

/**
 *
 * 문서 제목 문자열 반환
 *
 * 'write.skin.php' 에서 문서 제목을 설정하기 위함
 */
function wiki_doc_from_write($doc, $wr_id)
{
	$wikiArticle =& wiki_class_load("Article");
	if(!$doc) {
		$write = &$wikiArticle->getArticleById($wr_id);
		$doc = ($write[ns] == "/" ? "" : $write[ns]) . "/" . $write[wr_subject];
	}

	list($ns, $doc, $full) = wiki_page_name($doc);
	return array(get_text($doc), get_text($full));
}

/**
 *
 * 네비게이션 문자열 반환
 *
 * @uses $navi = wiki_navigation("/narin/plugins/locing", false);
 *
 * @param string $doc 문서 또는 폴더 경로
 * @param boolean $isNS $doc가 폴더 경로인지 아닌지
 * @return string 네비게이션문자열 (e.g. Home > 폴더 > 문서)
 */
function wiki_navigation($doc, $isNS=false) {
	global $wiki;

	if(!$isNS) list($ns, $docname, $fullpath) = wiki_page_name($doc);
	else $ns = $doc;

	$path = explode("/", $ns);

	$wiki_navigation = "<a href='".wiki_url('folder')."'>Home</a> > ";
	$hrefPath = "";
	for($i=0; $i<count($path); $i++) {
		if($path[$i]) {
			$hrefPath .= "/".$path[$i];
			$wiki_navigation .= " <a href='".wiki_url('folder', array('loc'=>$hrefPath))."'>".$path[$i]."</a> > ";
		}
	}
	return $wiki_navigation . " <a href='".wiki_url('read', array('doc'=>$doc))."'>$docname</a>";
}

/**
 *
 * 문서 경로에서 부모 폴더 경로 추출
 *
 * @param string $path 문서경로
 * @return string 부모폴더 경로
 */
function wiki_get_parent_path($path)
{
	$arr = explode("/", $path);
	array_pop($arr);
	$p = implode("/", $arr);
	return ($p == "" ? "/" : $p);
}

/**
 *
 * DB 쿼리 결과를 배열로 반환
 *
 * @param string $sql 쿼리문자열
 * @return array 쿼리결과 배열
 */
function wiki_sql_list($sql) {
	$result = sql_query($sql);
	$list = array();
	while ($row = sql_fetch_array($result))
	{
		array_push($list, $row);
	}
	return $list;
}

/**
 *
 * 위키 옵션 설정
 *
 * @uses 옵션 설정/업데이트 : wiki_set_option("js_modified", "timestamp", time());
 * @uses 옵션 삭제 : wiki_set_option("js_modified", null, null);
 * @uses 한 옵션에 여러 필드 설정 : wiki_set_option("multi", array("field1", "field2"), array("value1", "value2"));
 * @uses 한 필드만 업데이트 : wiki_set_option("multi", "field1", "value1-2");
 *
 * @param string $name 옵션명
 * @param string $field 옵션 필드명
 * @param mixed $value 값
 */
function wiki_set_option($name, $field, $value)
{
	global $wiki;

	$eName = mysql_real_escape_string($name);

	if($field == null && $value == null) {
		sql_query("DELETE FROM ".$wiki['option_table']." WHERE name = '/".$wiki['bo_table']."/$eName'");
		return true;
	}

	$opt = wiki_get_option($name);

	if($value == null && $opt) {
		unset($opt[$field]);
		$json_string = mysql_real_escape_string(json_encode($opt));
		$sql = "UPDATE ".$wiki['option_table']." SET content = '$json_string' WHERE name = '/".$wiki['bo_table']."/$eName'";
		sql_query($sql);
		return true;
	}

	if($opt) {	// 저장된 옵션이 있다면 수정

		// 필드와 값이 모두 배열이면..
		if(is_array($field) && is_array($value)) {
				
			// 필드와 값이 갯수가 같아야 함
			if(count($field) != count($value)) return false;
				
			for($i=0; $i<count($field); $i++) {
				$opt[$field[$i]] = $value[$i];
			}
		} else if(!is_array($field) && !is_array($value)) {
			$opt[$field] = $value;
		} else return false;

		$json_string = mysql_real_escape_string(json_encode($opt));
		$sql = "UPDATE ".$wiki['option_table']." SET content = '$json_string' WHERE name = '/".$wiki['bo_table']."/$eName'";

	} else {		// 저장된 옵션이 없다면 삽입

		if(is_array($field) && is_array($value)) {
			// 필드와 값이 갯수가 같아야 함
			if(count($field) != count($value)) return false;

			$data = array();

			for($i=0; $i<count($field); $i++) {
				$data[$field[$i]] = $value[$i];
			}
		} else if(!is_array($field) && !is_array($value)) {
			$data = array("$field"=>$value);
		} else return false;

		$json = mysql_real_escape_string(json_encode($data));
		$sql = "INSERT INTO	".$wiki['option_table']." VALUES ('/".$wiki['bo_table']."/$eName', '$json')";
	}
	sql_query($sql);
	return true;
}


/**
 *
 * 위키 옵션 반환
 *
 * DB의 option_table은 'name (varchar 255)', 'content (text)' 필드로 되어있으며,
 * wiki_get_option, wiki_set_option 함수를 이용해 json 데이터로 저장하고 읽어온다.
 *
 * wiki_get_option($name) 으로 데이터 전체를 가져올 수 있고,
 * wiki_get_option($name, $field) 로 데이터 중 주어진 필드 데이터만 가져올 수 있다.
 *
 * 사용 예 :
 * <code>
 * // 값 셋팅
 * wiki_set_option("js_modified", "timestamp", time());
 *
 * // js_modified 옵션 읽어오기
 * $modified = wiki_get_option("js_modified");
 * echo $modified['timestamp'];
 *
 * // 또는..
 * $modified_time = wiki_get_option("js_modified", "timestamp");
 * </code>
 *
 * @param string $name 옵션명
 * @param string $field 필드
 * @return mixed option_table 에 설정된 값 (없을 경우 null)
 */
function wiki_get_option($name, $field="")
{
	global $wiki;
	$name = mysql_real_escape_string($name);

	$opt = sql_fetch("SELECT content FROM $wiki[option_table] WHERE name = '/".$wiki['bo_table']."/$name'");

	if($opt) {
		$json = json_decode($opt[content], $assoc=true);
		if($field) {
			if(isset($json[$field])) return $json[$field];
		}
		return $json;
	}
	return null;
}


/**
 *
 * 검색어 결과에서 검색어 폰트 처리
 *
 * @param string $stx 검색어
 * @param string $str 문자열
 * @return string 검색어에 .wiki_search_word 클래스 붙여서 반환
 */
function wiki_search_font($stx, $str)
{
	return str_ireplace($stx, '<span class="wiki_search_word">' . $stx . '</span>', $str);
}

/**
 *
 * Html to Text
 *
 * HTML 태그들을 &lt;, &gt; 등의 문자들로 변환
 *
 * @param string $content HTML 컨텐트
 * @return string text 데이터
 */
function wiki_text($content)
{
	$content = html_symbol($content);
	$content = get_text($content, 0);
	return $content;
}

/**
 *
 * Text to Html
 *
 * &lt;, &gt; 등의 문자들을 태그로 변환
 *
 * @param string $content text 컨텐트
 * @return string HTML 데이터
 */
function wiki_html($content) {
	$html = html_entity_decode($content);
	$html = str_replace("&#039;", "'", $html);
	$html = str_replace("&#038;", "&", $html);
	return $html;
}

/**
 *
 * 위키 스킨 목록 반환
 *
 * 현재 board 스킨만 있음.
 *
 * @uses $skins = wiki_get_skins("board");
 * @todo board 외에 다른 스킨이 필요할까?
 * @param string $skin 스킨 유형
 * @return array 스킨 목록 배열 (just 이름)
 */
function wiki_get_skins($skin)
{
	global $wiki;

	$result_array = array();

	$dirname = WIKI_PATH."/skin/$skin/";
	$handle = opendir($dirname);
	while ($file = readdir($handle))
	{
		if($file{0} == ".") continue;

		if (is_dir($dirname.$file)) $result_array[] = $file;
	}
	closedir($handle);
	sort($result_array);

	return $result_array;
}

/**
 *
 * 위키 플러그인 목록 반환
 *
 * @return array 플러그인 목록 배열
 *               : array("name"=>플러그인명,
 *               		 "info"=>플러그인정보클래스인스턴스,
 *               		 "plugins"=>array("type"=>syntax|action,
 *               						  "instance"=>플러그인클래스인스턴스)
 *                 )
 */
function wiki_plugin_load()
{
	global $wiki;

	include_once WIKI_PATH."/lib/narin.Plugin.class.php";
	include_once WIKI_PATH."/lib/narin.PluginInfo.class.php";
	include_once WIKI_PATH."/lib/narin.SyntaxPlugin.class.php";
	include_once WIKI_PATH."/lib/narin.ActionPlugin.class.php";

	$plugins = array();
	$plugin_dir = WIKI_PATH."/plugins";
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
 *
 * 플러그인 정보 클래스 로드
 *
 * @param string $plugin 플러그인폴더
 * @return NarinPluginInfo 플러그인정보클래스인스턴스
 */
function wiki_plugin_info($plugin)
{
	global $wiki;
	static $loadedInfo = array();

	include_once WIKI_PATH."/lib/narin.PluginInfo.class.php";

	$classFile = WIKI_PATH."/plugins/".$plugin."/info.php";
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
 *
 * 폴더명과 문서명을 합침
 *
 * @uses $doc = wiki_doc("/폴더1/폴더2", "문서");	// $doc == "/폴더1/폴더2/문서"
 * @uses $doc = wiki_doc("/", "문서");	// $doc == "/문서"
 *
 * @param string $ns 폴더경로
 * @param string $docname 문서명
 * @return string 폴더경로+문서명
 */
function wiki_doc($ns, $docname) {
	return ($ns == "/" ? "" : $ns ) . "/" . $docname;
}

/**
 *
 * 연관배열 키 기준으로 정렬 (asort)
 *
 * @param array $a 연관배열
 * @param string $subkey 정렬기준키
 * @return array 정렬된 배열
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
 *
 * 연관배열 키 기준으로 정렬 (sort)
 *
 * @param array $a 연관배열
 * @param string $subkey 정렬기준키
 * @return array 정렬된 배열
 */
function wiki_subval_sort($a,$subkey) {
	$c = wiki_subval_asort($a, $subkey);
	$c = array_reverse($c);
	return $c;
}

/**
 *
 * 파일 사이즈 변환
 *
 * @param int $size 파일크기(bytes)
 * @return string 보기좋게 변환된 파일크기
 */
function wiki_file_size($size) {
	$mod = 1024;
	$units = explode(' ','B KB MB GB TB PB');
	for ($i = 0; $size > $mod; $i++) {
		$size /= $mod;
	}
	return round($size, 2) . ' ' . $units[$i];
}

/**
 *
 * EUC-KR 버전인가?
 *
 * @return true|false euc-kr 의 그누보드면 true, 아니면 false
 */
function wiki_is_euckr() {
	global $g4;
	return $g4[charset] == 'euc-kr';
}

/**
 *
 * 폴더의 총 파일크기를 알아옴
 *
 * @param string 폴더 경로
 * @return array 파일크기와 개수를 담은 배열 array(파일크기, 파일사이즈)
 */
function wiki_dir_filesize($path){

	if(!file_exists($path)) return array(0, 0);
	if(is_file($path)) {
		return array(filesize($path), 1);
	}

	$file_count = 0;
	$file_size = 0;
	foreach(glob($path."/*") as $fn) {
		list($fsize, $fcount) = wiki_dir_filesize($fn);
		$file_size += $fsize;
		$file_count += $fcount;
	}

	return array($file_size, $file_count);
}


/**
 *
 * json_encode wrapper 함수
 *
 * euc-kr 일 경우 json_encode 하기전 배열의 모든 값을 UTF-8로 변환
 * AJAX 통신시 사용
 *
 * @param array $arr 변환할 배열
 * @return array 변환된 배열
 */
function wiki_json_encode($arr) {
	if(wiki_is_euckr()) wiki_utf8($arr);
	return json_encode($arr);
}

/**
 *
 * 배열을 UTF-8로 변환
 *
 * @param array $arr 변환할 배열
 * @return array 변환된 배열
 */
function wiki_utf8(&$arr) {
	if(!is_array($arr)) {
		if(wiki_is_euckr()) $arr = iconv("CP949", "UTF-8", $arr);
		return $arr;
	}
	foreach($arr as $k => $v) {
		wiki_utf8($arr[$k]);
	}
}

/**
 *
 * 배열을 EUC-KR로 변환
 *
 * @param array $arr 변환할 배열
 * @return array 변환된 배열
 */
function wiki_euckr(&$arr) {
	if(!is_array($arr)) {
		if(mb_detect_encoding($arr) == 'UTF-8') $arr = iconv("UTF-8", "CP949", $arr);
		return $arr;
	}
	foreach($arr as $k => $v) {
		wiki_euckr($arr[$k]);
	}
}

/**
 *
 * AJAX 통신으로 받은 데이터 변환
 *
 * euc-kr 일 경우, 인코딩 변환이 필요함
 *
 * @param string $data 변환할 데이터
 * @return string 변환된 데이터
 */
function wiki_ajax_data($data) {
	if(is_array($data)) {
		foreach($data as $k => $v) {
			$data[$k] = wiki_ajax_data($v);
		}
		return $data;
	}
	
	if(wiki_is_euckr()) {
		return iconv("UTF-8", "CP949", rawurldecode($data));
	}
	
	return $data;
}

/**
 *
 * ajax 가 아니면 페이지 없음 표시
 *
 * @todo 제대로 되는지 검증 필요
 */
function wiki_only_ajax() {
	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !$_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
		wiki_not_found_page();
	}
}

/**
 *
 * 페이지 없음 표시
 *
 */
function wiki_not_found_page() {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 *
 * 배열을 재귀적으로 stripslashes 함
 *
 * @param array $arr 변환할 배열
 * @return array 변환된 배열
 */
function wiki_unescape($arr) {
	if(!is_array($arr)) {
		return stripslashes($arr);
	}

	foreach($arr as $k=>$v) {
		if(is_array($v)) $arr[$k] = wiki_unescape($arr);
		else $arr[$k] = stripslashes($v);
	}

	return $arr;
}

/**
 *
 * 배열을 재귀적으로 addslashes 함
 *
 * @param array $arr 변환할 배열
 * @return array 변환된 배열
 */
function wiki_escape($arr) {
	if(!is_array($arr)) {
		return addslashes($arr);
	}

	foreach($arr as $k=>$v) {
		if(is_array($v)) $arr[$k] = wiki_escape($arr);
		else $arr[$k] = addslashes($v);
	}

	return $arr;
}

/**
 *
 * 디버그 용
 *
 * 배열이나 문자열을 받아서 textarea 에 출력하는 함수
 *
 * @param string|array $str 출력할 값
 * @param int $h textarea 높이
 */
function wiki_debug($str, $h=400)
{
	echo "<textarea style='width:100%;height:".$h."px'>";
	if(is_array($str)) print_r($str);
	else echo $str;
	echo "</textarea>";
}

/**
 *
 * 한줄 디버그 용
 *
 * @param string $str 출력문자열
 */
function wiki_print($str)
{
	echo "==> $str <br/>";
}

/**
 *
 * 메모리 사용 디버그용
 *
 * @param string $s 출력메시지 prefix
 */
function wiki_print_memory($s)
{
	global $is_admin;
	if($is_admin) wiki_print($s ." : " . wiki_file_size(memory_get_usage()) . " (peak=" . wiki_file_size(memory_get_peak_usage()).")");
}
?>
