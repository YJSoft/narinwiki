<?
/**
 *
 * 나린위키JS/CSS 관리 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 JS/CSS 관리 클래스
 *
 * 플러그인, 스킨, 시스템 js/css 파일을 생성/관리 하는 클래스
 *
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinJsCss extends NarinClass {

	/**
	 *
	 * @var string ./data/bo_table 경로
	 */
	public $data_path;

	/**
	 *
	 * @var array 통합/minify 할 js 파일 목록
	 */	
	public $js_files;

	/**
	 *
	 * @var array 통합/minify 할 css 파일 목록
	 */	
	public $css_files;

	/**
	 *
	 * @var string minify 한 js 내용을 저장할 파일 경로
	 */	
	public $js;

	/**
	 *
	 * @var string minify 한 css 내용을 저장할 파일 경로
	 */	
	public $css;
	
	/**
	 *
	 * 주의 : 현재 사용되지 않음
	 * @var string minify 한 js 내용을 저장할 파일 경로 (print 용)	 
	 */		
	public $css_print;

  /**
   *
   * @var long $this->js 파일이 업데이트된 타임스탬프
   */
	public $js_modified = 0;

  /**
   *
   * @var long $this->css 파일이 업데이트된 타임스탬프
   */
	public $css_modified = 0;

  /**
   *
   * @var string css 파일의 url, src 경로를 수정하기 위한 임시 변수
   */
	protected $css_cur_path;

	/**
	 * 생성자
	 */
	public function __construct() {

		parent::__construct();
		$this->data_path = WIKI_PATH.'/data/'.$this->wiki['bo_table'];
		$this->js = $this->data_path .'/_js.txt';
		$this->css = $this->data_path .'/_css.txt';
		$this->css_print = $this->data_path .'/_css_print.txt';
	}

	/**
	 *
	 * js 파일들 목록 생성 / modified time 설정
	 */
	function loadJsFiles() {

		if($this->js_modified > 0) return;
			
		// js 폴더 로딩
		$this->js_files = array(WIKI_PATH."/js/jquery.fancybox-1.3.4.js",
		WIKI_PATH."/js/jquery.markitup.js",
		WIKI_PATH."/js/jquery.treeview.js",
		WIKI_PATH."/js/jquery.cookie.js"
		);


		// just check modified time
		list($modified, $list) = $this->get_files(WIKI_PATH."/js", "js");
		$this->js_modified = $modified;
			
		// load plugins's js files
		if(file_exists($this->data_path."/js")) {
			list($modified, $list) = $this->get_files($this->data_path."/js", "js");
			if($modified > $this->js_modified) $this->js_modified = $modified;
			$this->js_files = array_merge($this->js_files, $list);
		}

		// load skin's js files
		list($modified, $list) = $this->get_files($this->wiki['skin_path'], "js");
		if($modified > $this->js_modified) $this->js_modified = $modified;
		$this->js_files = array_merge($this->js_files, $list);

		// narin.editor.js AND narin.wiki.js 파일을 제일 나중에 실행				
		array_push($this->js_files, WIKI_PATH."/js/narin.editor.js");				
		array_push($this->js_files, WIKI_PATH."/js/narin.wiki.js");
	}

	/**
	 *
	 * css 파일들 목록 생성 / modified time 설정
	 */
	function loadCssFiles() {

		if($this->css_modified > 0) return;
			
		// css 폴더 로딩
		$this->css_files = array(WIKI_PATH."/css/jquery.fancybox-1.3.4.css",
		WIKI_PATH."/css/jquery.treeview.css",
		WIKI_PATH."/css/narin.tool.set.css",
		WIKI_PATH."/css/narin.tool.skin.css",
		WIKI_PATH."/css/narin.wiki.adm.css",
		WIKI_PATH."/css/narin.wiki.style.css"
		);


		// just check modified time
		list($modified, $list) = $this->get_files(WIKI_PATH."/css", "css");
		$this->css_modified = $modified;
			
		// load plugins's css files
		if(file_exists($this->data_path."/css")) {
			list($modified, $list) = $this->get_files($this->data_path."/css", "css");
			if($modified > $this->css_modified) $this->css_modified = $modified;
			$this->css_files = array_merge($this->css_files, $list);
		}

		// load skin's css files
		list($modified, $list) = $this->get_files($this->wiki['skin_path'], "css");
		if($modified > $this->css_modified) $this->css_modified = $modified;
		$this->css_files = array_merge($this->css_files, $list);

	}

	/**
	 *
	 * js 파일을 읽고, minify 해서 하나의 파일로 저장
	 */
	function updateJs() {
		if($this->js_modified == 0) $this->loadJsFiles();

		include_once WIKI_PATH."/lib/Minifier/jsmin.php";

		$contents = $this->get_js_contents_array($this->js_files);

		try {
			$contents = JSMin::minify($contents);
		} catch(Exception $ex) {}

		$fp = fopen($this->js, "w");
		fwrite($fp, $contents);
		fclose($fp);
	}

	/**
	 *
	 * css 파일을 읽고, minify 해서 하나의 파일로 저장
	 */
	function updateCss() {
		if($this->css_modified == 0) $this->loadCssFiles();

		include_once WIKI_PATH."/lib/Minifier/cssmin.php";

		$contents = $this->get_css_contents_array($this->css_files);
		$contents_print = $this->get_css_contents_array($this->css_files, $print=true);
		
		try {
			$contents = CssMin::minify($contents);
			if($contents_print) $contents_print = CssMin::minify($contents_print);
		} catch(Exception $ex) {}

		$fp = fopen($this->css, "w");
		fwrite($fp, $contents);
		fclose($fp);
		
		if($contents_print)  {
			$fp = fopen($this->css_print, "w");
			fwrite($fp, $contents_print);
			fclose($fp);		
		}
	}

	/**
	 *
	 * 주어진 파일 경로 배열에서 파일 읽어오기
	 *
	 * @param array $files 추가할 파일 경로 배열
	 * @return string 병합된 파일 내용
	 */
	function get_js_contents_array($files) {
		$str = "";
		foreach($files as $k=>$file) {
			$str .= file_get_contents($file);
		}
		return $str;
	}

	/**
	 *
	 * CSS 파일 읽어오기
	 *
	 * @param string $path 폴더 경로
	 * @param string $extension 확장자
	 * @return string 병합된 파일 내용
	 */
	function get_css_contents_array($files, $print_version = false) {
		$str = "";
		foreach($files as $k=>$file) {
			if(is_dir($files)) continue;
			if($print_version && strpos($file, "print") <= 0) continue;
			if(!$print_version && strpos($file, "print") > 0) continue;
			$this->css_cur_path = dirname($file);
			$contents = file_get_contents($file);
			$contents = preg_replace_callback("/url\s?\([\'|\"]?(.*?)[\'|\"]?\)/is", array(&$this, "replace_css_url_path"), $contents);
			$contents = preg_replace_callback("/src=\s?[\'|\"](.*?)[\'|\"]/is", array(&$this, "replace_css_src_path"), $contents);
			$str .= '/* '. $file . ' */'. $contents;
		}
		return $str;
	}

	/**
	 *
	 * CSS 내용중 경로(background:url(....))를 URL 경로로 변경
	 *
	 * @param array $matches 패턴 매칭 결과
	 * @return string 경로가 변경된 url
	 */
	function replace_css_url_path($matches) {
		if(preg_match("/^(http[s]?:\/\/|ftp:\/\/|\/)/i", $matches[1])) return $matches[0];
		return "url(".$this->wiki['url'].str_replace(WIKI_PATH, '', $this->css_cur_path).'/'.$matches[1].")";
	}

	/**
	 *
	 * CSS 내용중 경로(src=(....))를 URL 경로로 변경
	 *
	 * @param array $matches 패턴 매칭 결과
	 * @return string 경로가 변경된 url
	 */
	function replace_css_src_path($matches) {
		if(preg_match("/^(http[s]?:\/\/|ftp:\/\/|\/)/i", $matches[1])) return $matches[0];
		return "src='".$this->wiki['url'].str_replace(WIKI_PATH, '', $this->css_cur_path).'/'.$matches[1]."'";
	}

	/**
	 *
	 * 주어진 파일 경로에서 파일 목록 읽어오기
	 *
	 * @param string $path 폴더 경로
	 * @param string $extension 확장자
	 * @return string 파일 목록 배열
	 */
	function get_files($path, $extension) {
		$modified = 0;
		$str = "";
		$files = scandir($path);
		$extlen = -1 * (strlen($extension)+1);
		$list = array();
		foreach($files as $k=>$file) {
			if(is_dir($path."/".$file)) continue;
			if(substr($file, $extlen) != '.'.$extension) continue;
			if(!$is_ie6 && strpos($file, "ie6") > 0) continue;
			$age = filemtime($path."/".$file);
			if($age > $modified) {
				$modified = $age;
			}
			array_push($list, $path."/".$file);
		}
		return array($modified, $list);
	}

}
?>
