<?
/**
 * 
 * 나린위키 메인 문법 플러그인 스크립트
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 메인 문법 플러그인 클래스
 *
 * 나린위키의 기본 문법 클래스이다.
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxDefault extends NarinSyntaxPlugin {

	/**
	 *
	 * @var string 플러그인 아이디
	 */
	var $id;

	/**
	 *
	 * @var array 섹션 함수에서 사용
	 */
	var $sections = array();

	/**
	 *
	 * @var array TOC 만들기 위해 사용
	 */
	var $toc = array();

	/**
	 *
	 * @var array footnotes 만들기 위해 사용
	 */
	var $footnotes = array();

	/**
	 *
	 * @var int 파싱 중 현재 섹션 레벨
	 */
	var $section_level = -1;

	/**
	 *
	 * @var array 목록 만들때 목록 타입(ul, ol) 저장하기 위한 스택
	 */
	var $list_level_types = array();

	/**
	 *
	 * @var int 파싱 중 현재 목록 레벨
	 */
	var $list_level = 0;

	/**
	 *
	 * @var int 파싱 중 현재 왼쪽 공백 개수
	 */
	var $list_space = 0;

	/**
	 *
	 * @var array 테이블 분석 중 rowspan 정보 저장
	 */
	var $table_rowspan = array();

	/**
	 *
	 * @var boolean 테이블 태그가 열린 상태인가
	 */
	var $table_opened = false;

	/**
	 *
	 * @var boolean 박스 태그가 열린 상태인가
	 */
	var $boxformat = false;

	/**
	 *
	 * @var boolean pre 태그가 열린 상태인가
	 */
	var $preformat = false;

	/**
	 *
	 * @var boolean 문단이 열린 상태인가
	 */
	var $pformat = false;

	/**
	 *
	 * @var boolean TOC를 사용하지 않을 것인가
	 */
	var $no_toc = false;

	/**
	 *
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
		$this->id = "wiki_default_parser";		
	}

	/**
	 *
	 * 파싱 시작되기 전에 변수 초기화
	 */
	public function init()
	{
		$this->sections = array();
		$this->toc = array();
		$this->footnotes = array();
		$this->section_level = -1;
			
		$this->list_level_types = array();
		$this->list_level = 0;

		$this->table_rowspan = array();
		$this->table_opened = false;

		$this->boxformat = false;
		$this->preformat = false;
		$this->pformat = false;
		$this->no_toc = false;
	}


	/**
	 *
	 * 파서 등록
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{

		$line_regs = array('wiki_table'=>'^(\^|\|)(.*?)(\^|\|)$',
						   'wiki_ul'=>'^(\s{2,}[\*])(.*?)$',
						   'wiki_ol'=>'^(\s{2,}[-])(.*?)$',
						   'wiki_box'=>'^\s{2,}(.*?)$',
						   'wiki_quoting'=>'^((&gt;)+)(.*?)$',
						   'wiki_sections'=>'^(={1,6})(.*?)(={1,6})$',
						   'wiki_hr'=>'^----$');

		$word_regs = array(
      'wiki_internal_link'=>'('.
        '\[\['. 
          '(^\/([^\]]*?)\/)?'. 
          '([^\]]*?)'. 
          '(\|([^\]]*?))?'. 
        '\]\]'. 
        '([a-z]+)?'. 
        ')',
      'wiki_external_link'=>'('.
        '\['.
          '([^\]]*?)'.
          '(\s+[^\]]*?)?'.
        '\]'.
        ')',
      'wiki_br'=>'(.*?)\\\\\\\\(?=\s|$)',
      'wiki_bold'=>'\*{2}(.*?)\*{2}',
      'wiki_italic'=>'\/{2}\s(.*?)\s\/{2}',
      'wiki_underline'=>'__\s(.*?)\s__',
      'wiki_strike'=>'&lt;del&gt;(.*?)&lt;\/del&gt;',
      'wiki_sub'=>'&lt;sub&gt;(.*?)&lt;\/sub&gt;',
      'wiki_sup'=>'&lt;sup&gt;(.*?)&lt;\/sup&gt;',
      'wiki_color'=>'&lt;color(.*?)&gt;(.*?)&lt;\/color&gt;',
      'wiki_font_size'=>'&lt;size(.*?)&gt;(.*?)&lt;\/size&gt;',
      'wiki_footnotes'=>'\(\((.*?)\)\)',
      'wiki_no_toc'=>'~~NOTOC~~',
      'wiki_comment'=>'~~COMMENT~~'
      );

      $variable_regs = array(
    	"wiki_folder"=>array("start_regx"=>"folder=", "end_regx"=>""),   				
    	"wiki_newpage"=>array("start_regx"=>"NEWPAGE", "end_regx"=>"(:(.*?))?((\?)(.*?))?"),
    	"wiki_search"=>array("start_regx"=>"SEARCH", "end_regx"=>""),
    	"wiki_image"=>array("start_regx"=>"image=", "end_regx"=>"((\?)(.*?))?"),
    	"wiki_file"=>array("start_regx"=>"file=(\d)(\s+", "end_regx"=>")?"),
    	"wiki_media"=>array("start_regx"=>"media=", "end_regx"=>"((\?)(.*?))?(\|(.*?))?")
      );

      foreach($line_regs as $func => $regx) {
      	$parser->addLineParser(
      	$id = $this->id."_".$func,
      	$klass = $this,
      	$regx,
      	$method = $func);
      }
       
      foreach($word_regs as $func => $regx) {
      	$parser->addWordParser(
    			$id = $this->id."_".$func,
    			$klass = $this,
    			$regx,
    			$method = $func);
      }

      foreach($variable_regs as $func => $v) {
      	$parser->addVariableParser(
      	$id = $this->id."_".$func,
      	$klass = $this,
      	$startRegx = $v['start_regx'],
      	$endRegx = $v['end_regx'],
      	$method = $func);
      }

      $parser->addEvent(EVENT_AFTER_PARSING_ALL, $this, "wiki_after_all");
      $parser->addEvent(EVENT_AFTER_PARSING_LINE, $this, "wiki_after_parsing_line");
	}

	/**
	 *
	 * 라인 파서의 실행이 모두 끝난 후 처리
	 *
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 */
	public function wiki_after_all($params)
	{
		// box format 이 남아있다면, tag close
		if ($this->preformat) $params['output'] .= "</pre></div><!--// box-pre -->";
		if ($this->boxformat) $params['output'] .= "</div><!--// box -->";

		// 열린 table 이 있다면, 닫아줌
		if($this->table_opened) {
			$params['output'] .= "</table>";
		}

		// 열린 paragraph 있으면 닫아줌
		if($this->pformat) {
			$params['output'] .= "</p>";
		}
			
		// 열린 section 이 있다면, 닫아줌
		while($pSection = array_pop($this->sections)) {
			$params['output'] .= $pSection['close_tag'];
		}
			
		//if($params[view][wr_comment]) $this->no_toc = true;
			
		// 목차 추가
		if(!$this->no_toc && count($this->toc) > 2) {
			$toc = "";
			foreach($this->toc as $head) {
				$toc .= "<div class='toc toc_".$head['level']."'><a href=\"#".str_replace("\"", "", $head['title'])."\">{$head['title']}</a></div>\n";
			}

			$toc =<<<END
			<div id='wiki_toc'>
				<div id='wiki_toc_head'><a href='#toc' id='toc_fold' class='fold_up'>목 &nbsp;차</a></div>
				<div id='wiki_toc_content'>
					<div><!-- ie hack --></div>
					$toc
				</div> <!--// wiki_toc_content -->
			</div> <!--// wiki_toc -->
END;
					$params['output'] = $toc . $params['output'];
		}

		if(count($this->footnotes)) {
			$fn = "<div id=\"wiki_footnotes\">\n";
			foreach($this->footnotes as $idx => $v) {
				$idx++;
				$fn .= "<div class=\"wiki_footnote_content\"><sup class=\"fn\"><a href=\"#footnote_top_$idx\" name=\"footnote_$idx\">$idx)</a></sup> $v</div>\n";
			}
			$fn .= "</div>\n";
			$params['output'] .= $fn;
		}
	}

	/**
	 *
	 * 한 라인 파싱이 끝난 후
	 *
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 */
	public function wiki_after_parsing_line($params)
	{
		// 이전 라인에서 목록을 열었으면.. 닫음
		if (($this->list_level>0) && (!$params['called'][$this->id.'_wiki_ul'] && !$params['called'][$this->id.'_wiki_ol']))
		{
			$params['line'] = $this->wiki_list(false, array(), '', '', true) . $params['line'];
		}
		if ($this->boxformat && !$params['called'][$this->id.'_wiki_box'])
		{
			$params['line'] = $this->wiki_box(false, array(), true) . $params['line'];
		}
		if ($this->pformat && !$params['called'][$this->id.'_wiki_par'])
		{
			$params['line'] = $this->wiki_par(false, array(), true) . $params['line'];
		}
		if ($this->table_opened && !$params['called'][$this->id.'_wiki_table'])
		{
			$params['line'] = $this->wiki_table(false, array(), true) . $params['line'];
		}

		// internal 또는 external 문서 링크에 이미지 사용
		$params['line'] = preg_replace_callback("/(<a[^>]*>)<a[^>]*>(<img[^>]*>)<\/a>(<\/a>)/i", create_function('$matches', 'return str_replace("wiki_external_link", "", $matches[1]) . $matches[2].$matches[3];'), $params['line']);
	}


	/**
	 *
	 * 테이블 분석
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string 테이블
	 */
	public function wiki_table($matches, $params, $close = false)
	{
		if($close) {
			$this->table_opened = false;
			return "</table> <!--// wiki_table -->\n";
		}

		$parser = &$params['parser'];
		$lines = &$params['lines'];


		$line = $matches[0];
		$arr = preg_split("/(\^|\|)/", $line, -1, PREG_SPLIT_DELIM_CAPTURE  );

		// 불필요한 앞뒤 배열 제거
		array_shift($arr); array_pop($arr);array_pop($arr);

		$size = count($arr);

		// tr 태그 열기
		if(!$this->table_opened) {
			$out = "<table class=\"wiki_table\" cellspacing=\"1\" cellpadding=\"0\">\n<tr>";
		} else $out = "<tr>";

		$col = 0;
		for($i=0; $i<$size; $i++) {
			$value = $arr[$i];
			$open_tag = $this->get_table_tag($arr[$i], $close=false);

			if(!$open_tag) {	// 태그가 아닐 경우 값 과 닫는 태그 입력

				$close_tag = $this->get_table_tag($arr[$i-1], $close=true);
					
				if(trim($value) == ":::") {	// rowspan

					if(!isset($this->table_rowspan[$col])) {
						$prev_row = $parser->current_row - 1;
					} else {
						$prev_row = $this->table_rowspan[$col]['row'];
					}

					$tmp = preg_split("/(<td|<th)(.*?>)([^<]*?)(<\/td>|<\/th>)/i", $lines[$prev_row], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
					for($jj=0; $jj<count($tmp); $jj++) {
						if(strpos($tmp[$jj], "class=\"col$col\">")) {
							if(isset($this->table_rowspan[$col])) {
								$tmp[$jj] = preg_replace("(rowspan=\"\d\")", "rowspan=\"".(++$this->table_rowspan[$col]['rowspan'])."\"", $tmp[$jj]);
							} else {
								$tmp[$jj] = " rowspan=\"2\"" . $tmp[$jj];
								$this->table_rowspan[$col]['row'] = $prev_row;
								$this->table_rowspan[$col]['rowspan'] = 2;
							}
						}
					}
					$lines[$prev_row] = implode("", $tmp);

				} else {	// rowspan 아닐 경우

					$out .= $value;	// 값 입력
					$out .= $close_tag;	// 태그 닫음

				}

			} else {

				// 셀 병합이 이미 이루어진 셀
				// 태그를 열지 않고 하나 건너 뜀
				if(!$arr[$i+1]) {
					$i++;
					$col++;
					continue;
				}

				// 다음에 rowspan 일 경우
				if(trim($arr[$i+1]) == ":::") {
					$col++;
					continue;
				}

					
				// 정렬
				preg_match("/^([\s]*)(.*?)([\s]*)$/", $arr[$i+1], $m);
				$tag = $this->get_table_tag($arr[$i], $close=false, $withBracket=false);
				$before_space = strlen($m[1]);
				$after_space = strlen($m[3]);
				$align = "";

				if( (!$before_space && !$after_space) || $before_space * $after_space > 0) $align = " align=\"center\"";
				else if($before_space && !$after_space) $align = " align=\"right\"";
				else $align = " align=\"left\"";

				// 행 병합
				$colspan = 0;
				for($k=$i+3; $k<$size; $k+=2) {
					if(!$arr[$k]) {
						$colspan++;
					} else break;
				}

				// 칼럼
				$col = ($i/2+1);
				unset($this->table_rowspan[$col]);

				$class = " class=\"col".$col."\"";

				$colspan = ($colspan ? " colspan=\"".($colspan+1)."\"" : "");
				$out .= "<$tag$align$colspan$class>";
			}
		}

		$out .= "</tr>\n";
		$this->table_opened = true;
		$parser->stop = true;
		return $out;
	}

	/**
	 *
	 * 테이블 태그 반환 (여는태그? 닫는태그?, th? td?)
	 *
	 * @param string $delim 테이블 구분자
	 * @param boolean $close 닫는 태그인가
	 * @param boolean $withBracket 괄호도 포함해서 반환할 것인가
	 * @return string 테이블 태그
	 */
	protected function get_table_tag($delim, $close=false, $withBracket=true)
	{
		if($withBracket) {
			$ot = "<";
			$ct = ">";
			$close = ($close ? "/" : "");
		} else $close = "";

		$delim = trim($delim);

		if($delim == "^") {
			return "$ot{$close}th$ct";
		}
		else if($delim == "|") {
			return "$ot{$close}td$ct";
		}
	}

	/**
	 *
	 * 해딩 (섹션)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 섹션 열기 태그
	 */
	public function wiki_sections($matches, $params)
	{
		$parser = &$params['parser'];

		$level = strlen($matches[1]);
		$content = trim($matches[2]);

		$parser->stop = true;

		$opening = "<div class='wiki_section wiki_section_$level'>";
		array_push($this->toc, array("level"=>$level, "title"=>$content));
		$before = "";
		$after = "";

		if($level > $this->section_level) {							// 작은 레벨로..
			$after = $opening;
		} else if($level == $this->section_level) {			// 같은 레벨로..
			$closing = array_pop($this->sections);
			$before = $closing['close_tag'];
			$after = $opening;
		} else {		// 큰 레벨로
			for($i=count($this->sections)-1; $i>=0; $i--) {
				$pSection = $this->sections[$i];
				if($pSection['level'] >= $level) {
					$before .= $pSection['close_tag'];
					array_pop($this->sections);
				}
			}
			$after .= $opening;
		}

		array_push($this->sections, array("level"=>$level, "close_tag"=>"</div> <!--// section $level -->\n"));
		$this->section_level = $level;

		return "\n\n$before\n<a name=\"".str_replace("\"", "", $content)."\"></a>\n<h{$level}>{$content}</h{$level}>\n$after\n";
	}

	/**
	 *
	 * 줄바꿈 (br)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 원문+ br 태그
	 */
	public function wiki_br($matches, $params)
	{
		return $matches[1]. "<br/>";
	}

	/**
	 *
	 * 굵게 (bold)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string strong 태그로 감싼 원문
	 */
	public function wiki_bold($matches, $params)
	{
		return "<strong>".trim($matches[1]). "</strong>";
	}

	/**
	 *
	 * 기울게 (italic)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string i 태그로 감싼 원문
	 */
	public function wiki_italic($matches, $params)
	{
		return "<i>".trim($matches[1]). "</i>";
	}

	/**
	 *
	 * 밑줄 (underline)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string .u 로 설정된 span 태그로 감싼 원문
	 */
	public function wiki_underline($matches, $params)
	{
		return "<span class='u'>".trim($matches[1]). "</span>";
	}

	/**
	 *
	 * 가운데줄 (strike)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string del 태그로 감싼 원문
	 */
	public function wiki_strike($matches, $params)
	{
		return "<del>".trim($matches[1]). "</del>";
	}

	/**
	 *
	 * 윗첨자 (sup)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string sup 태그로 감싼 원문
	 */
	public function wiki_sup($matches, $params)
	{
		return "<sup>".trim($matches[1]). "</sup>";
	}

	/**
	 *
	 * 아래첨자 (sub)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string sub 태그로 감싼 원문
	 */
	public function wiki_sub($matches, $params)
	{
		return "<sub>".trim($matches[1]). "</sub>";
	}

	/**
	 *
	 * 글자색 (color)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string color 스타일이 설정된 span 태그로 감싼 원문
	 */
	public function wiki_color($matches, $params)
	{
		return "<span style=\"color:".trim($matches[1])."\">".trim($matches[2]). "</span>";
	}

	/**
	 *
	 * 글자크기 (font-size)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string font-size 스타일이 설정된 span 태그로 감싼 원문
	 */
	public function wiki_font_size($matches, $params)
	{
		return "<span style=\"font-size:".trim($matches[1])."\">".trim($matches[2]). "</span>";
	}

	/**
	 *
	 * 주석 (footnotes)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 주석 링크가 설정된 원문
	 */
	public function wiki_footnotes($matches, $params)
	{
		$footnote = trim($matches[1]);

		$key = array_search($footnote, $this->footnotes);
		if($key === false) {
			$idx = count($this->footnotes)+1;
			array_push($this->footnotes, $footnote);
		} else $idx = ($key+1);
		return "<sup class=\"fn\"><a href=\"#footnote_$idx\" name=\"footnote_top_$idx\" id=\"footnote_top_$idx\">$idx)</a></sup>";
	}

	/**
	 *
	 * 인용 (quote)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 인용 태그로 감싼 원문
	 */
	public function wiki_quoting($matches, $params)
	{
		$m = str_replace("&gt;", ">", $matches[1]);
		$level = strlen(trim($m));
		$output = $matches[3];
		for($i=0; $i<$level; $i++) {
			$output = "<blockquote><div class='wiki_quot'>".$output."</div></blockquote>";
		}
		return $output;
	}

	/**
	 *
	 * 차례 없는 목록 (ul)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string 목록 태그
	 */
	public function wiki_ul($matches, $params, $close = false)
	{
		return $this->wiki_list($matches, $params, $listtype="ul", $listchar="*", $close);
	}

	/**
	 *
	 * 차례 있는 목록 (ol)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string 목록 태그
	 */
	public function wiki_ol($matches, $params, $close = false)
	{
		return $this->wiki_list($matches, $params, $listtype="ol", $listchar="-", $close);
	}


	/**
	 *
	 * 목록 (ul 또는 ol)
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param string $listtype OL 또는 UL
	 * @param string $listchar '-' 또는 '*'
	 * @param array $close 태그 닫을지 말지
	 * @return string 테이블
	 */
	public function wiki_list($matches, $params, $listtype, $listchar, $close = false)
	{

		$output_string = "";
		$closed_list = false;

		// $matches[1] : *, **, *** ...
		$space = strlen($matches[1])-1;
		if($space % 2 && !$close) return;

		if($params) {
			$params['parser']->stop = true;;
		}

		$space = $space / 2;

		if($this->list_space > $space) $newlevel = $space;
		else if($this->list_space < $space) $newlevel = $this->list_level +1;
		else $newlevel = $this->list_level;

		$this->list_space = $space;
		$newlevel = max(0, $newlevel);
		if($close) $newlevel = 0;

		$m_listtype = $listtype;
			

		while ($this->list_level != $newlevel)
		{

			// 리스트 레벨이 이전 레벨보다 작을 경우.. : close
			if ($this->list_level > $newlevel)
			{
				$m_listtype = '/'.array_pop($this->list_level_types);
				$this->list_level--;
			}
			else	// 리스트 레벨이 이전 레벨보다 클 경우.. : open new tag
			{
				$this->list_level++;
				$m_listtype = $listtype;
				array_push($this->list_level_types, $listtype);
			}

			$tab = "";
			for($i=0; $i<$this->list_level-1; $i++) $tab .= "\t";

			// 리스트를 닫음
			if ($m_listtype[0]=='/')
			{
				$output_string .= "$tab</li>\n$tab\t<{$m_listtype}>\n";
				$closed_list = true;
			}
			else	// 리스트를 새로 열음
			{
				$output_string .= "\n$tab<{$m_listtype} class='wiki_list_".$this->list_level."'>\n$tab\t<li>";
			}

		}

		if($this->list_level>0 && $listtype != $this->list_level_types[count($this->list_level_types)-1]) {
			$output_string .= "</li>\n<".'/'.array_pop($this->list_level_types)."><$listtype class='wiki_list_".$this->list_level."'><li>";
			array_push($this->list_level_types, $listtype);
		}


		// $close > 0 이라면, while 문에서 모든 리스트가 닫힘
		if ($close)
		{
			return $output_string;
		}

		if (empty($output_string) OR ($closed_list && $this->list_level > 0))
		{
			$tab = "";
			for($i=0; $i<$this->list_level; $i++) $tab .= "\t";
			$output_string .= "$tab</li>\n$tab<li>";
		}

		// $matches[2] => 목록 내용
		$output_string .= $matches[2];

		return $output_string;
	}

	/**
	 *
	 * 박스
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string 박스
	 */
	public function wiki_box($matches, $params, $close = false)
	{
		// 태그 닫기
		if ($close)
		{
			$this->boxformat = false;
			return "</div>\n";
		}

		$parser = &$params['parser'];

		// <div> 다음부터 모든 파싱 중지
		$parser->stop = true;

		// <div> 태그가 아직 열리지 않았으면 태그 열기
		$output = "";
		if (!$this->boxformat)
		{
			$output .= "<div class='wiki_box'>";
		}
		$this->boxformat = true;

		// 내용 추가
		$output .= $matches[1];

		return $output."\n";
	}

	/**
	 *
	 * pre
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string pre 태그로 감싼 원문
	 */
	public function wiki_pre($matches, $params, $close = false)
	{
		// 태그 닫기
		if ($close)
		{
			$this->preformat = false;
			return "</pre></div><!--// box-pre -->\n";
		}
			
		$parser = &$params['parser'];
			
		// <div> 다음부터 모든 파싱 중지
		$parser->stop_all = true;

		// <div> 태그가 아직 열리지 않았으면 태그 열기
		$output = "";
		if (!$this->preformat)
		{
			$output .= "<div class='wiki_box'><pre>";
		}
		$this->preformat = true;

		// 내용 추가
		$output .= $matches[1];

		return $output;
	}

	/**
	 *
	 * hr
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string hr 태그
	 */
	public function wiki_hr($matches, $params)
	{
		$parser = &$params['parser'];
		$parser->stop = true;
		return "<hr/>";
	}

	/**
	 *
	 * 문단
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string p 태그 + 원문
	 */
	public function wiki_par($matches, $params, $close = false) {

		// 태그 닫기
		if ($close || ( $this->pformat && !trim($matches[0]) ) )
		{
			$this->pformat = false;
			return "</p><!--// paragraph -->\n";
		}
			
		$parser = &$params['parser'];
		$parser->stop = true;
		$p = $this->pformat ? "" : "<p>";

		$this->pformat = true;
			
		return $p.$matches[0];
	}

	/**
	 *
	 * 위키 문서 링크
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 문서 링크
	 */
	public function wiki_internal_link($matches, $params)
	{
		$doc = $matches[2].$matches[4];
		$title = $matches[6];
		$path = explode("/", $matches[4]);

		// 문서내 링크
		if(preg_match("/^#/", $matches[4])) {
			if(!$title) $title = preg_replace("/^#/", "", $matches[4]);
			return sprintf(
	      '<a href="%s" class="%s">%s</a>',
			$matches[4],
	      "wiki_active_link",
			$title
			);
		}

		if(!$title) $title = array_pop($path);

		// 문서 존재 여부 확인
		list($ns, $docname, $full) = wiki_page_name($doc);

		$article = wiki_class_load("Article");
		$is_exists = $article->exists($ns, $docname);
		if($is_exists) $class = "wiki_active_link";
		else $class = "wiki_inactive_link";

		return sprintf(
      '<a href="%s" class="%s">%s</a>',
		$this->wiki['path']."/narin.php?bo_table=".$this->bo_table."&doc=".urlencode($doc),
		$class,
		$title
		);
	}

	/**
	 *
	 * 외부 문서 링크
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 외부문서 링크
	 */
	public function wiki_external_link($matches, $params)
	{
		$href = $matches[2];
		$title = trim($matches[3]);
		if (!$title)
		{
			$title = $matches[2];
		}

		// 외부 링크는 새 창으로
		return sprintf(
      '<a href="%s" class="wiki_external_link">%s</a>',
		$href,
		$title
		);
	}

	/**
	 *
	 * 폴더/파일 목록
	 *
	 * FORMAT : {{folder=/folder1/folder2}}
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 폴더/파일 목록
	 */
	public function wiki_folder($matches, $params)
	{
		$tmp = explode("?", $matches[1]);
		$loc = $tmp[0];
		parse_str(str_replace("&amp;", "&", $tmp[1]));

		$wikiNS = wiki_class_load("Namespace");

		// Check level
		$n = $wikiNS->get($loc);
		if($this->member['mb_level'] < $n['ns_access_level']) return "";

		$files = $wikiNS->getList($loc, $withArticle=true);
		if(!count($files)) return "{{".$matches[0]."}}";

		$up = wiki_get_parent_path($loc);
		$str = "<ul class='folder_list' style='$style'>";
		//if($loc != "/") $str .= '<li class="folder_up"><a href="'.$this->wiki['path'].'/folder.php?bo_table='.$this->wiki['bo_table'].'&loc='.$up.'">..</a></li>';
		for($i=0; $i<count($files); $i++) {
			$str .= '<li class="'.$files[$i]['type'].'"><a href="'.$files[$i]['href'].'">'.$files[$i]['name'].'</a></li>';
		}
		$str .= '</ul>';
		return $str;
	}

	/**
	 *
	 * 새문서 만들기 폼 출력
	 *
	 * FORMAT : {{NEWPAGE}}
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 새문서 만들기 폼
	 */
	public function wiki_newpage($matches, $params)
	{
		if($matches[3]) {
			$loc = wiki_input_value($matches[3]);
		} else $loc = wiki_input_value($this->folder);

		if($matches[6]) parse_str(str_replace("&amp;", "&", $matches[6]));
		$btn_txt = ($title ? $title : "문서만들기");
		$path = $this->wiki['path'];
		return <<<EOF
		
				<div class="wiki_newpage clear" style="$style">				
				<form action="$path/narin.php" method="get" class="wiki_form">				
				<input type="hidden" name="bo_table" value="{$this->wiki['bo_table']}"/>
				<input type="hidden" name="loc" value="$loc"/>				
				<span class="form_label">$label</span>
				<input type="text" name="doc" class="txt" size="20"/>
				<span class="button"><input type="submit" value="$btn_txt"></span>				
				</form>    	
				</div>
				
EOF;
	}

	/**
	 *
	 * 검색 폼
	 *
	 * FORMAT : {{SEARCH}}
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 검색폼
	 */
	public function wiki_search($matches, $params)
	{
		$path = $this->wiki['path'];
		return <<<EOF
		
		<div class="wiki_search clear">
		<form action="$path/search.php" onsubmit="return wiki_search(this);"  class="wiki_form" method="get">
		<input type="hidden" name="bo_table" value="{$this->wiki['bo_table']}"/>
		<input type="text" class="search_text txt" name="stx" size="20"/>
		<span class="button"><input type="submit" value="검색"></span>
		</form>			
		</div>
		
EOF;
	}

	/**
	 *
	 * 첨부 이미지
	 *
	 * FORMAT : {{image=0,1,2-5}}
	 * FORMAT : {{image=0,1,2-5?style=float:left;margin-right:5px}} : style for 'a' wrapping image
	 * FORMAT : {{image=0,1,2-5?imgstyle=padding:2px;border:1px solid #ccc}} : style for image
	 *
	 * @deprecated
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 이미지 태그
	 */
	public function wiki_image($matches, $params)
	{
		$view = &$params[view];

		// [1] : id list
		// [4] : parameters after '?'
		$imgs = explode(",", str_replace(" ", "", $matches[1]));
		if($matches[4]) parse_str(str_replace("&amp;", "&", $matches[4]));


		$use_thumb = false;
		if($width && $height) {
			$width = intval($width);
			$height= intval($height);
			$use_thumb = true;
		}
		if(preg_match("/^(http)/", $matches[1])) {
			if($use_thumb) $add = "width='$width' height='$height'";
			else $add = "";
			return "<a href='$matches[1]' class='wiki_image wiki_modal' style='$style' rel='$rn'><img src='$matches[1]' class='$align' style='$imgstyle' $add border='0'/></a>";
		}


		$rn = rand(1, 999999);
		$idxList = array();

		foreach($imgs as $k => $idx) {
			if(strpos($idx, "-")) {
				$ss = explode("-", $idx);
				for($i=$ss[0]; $i<=$ss[1]; $i++) {
					array_push($idxList, $i);
				}
			} else array_push($idxList, $idx);
		}
			
		if($use_thumb) $thumb = wiki_class_load("Thumb");

		$str = "";
		$view_list = array();
		if(is_array($view['file'])) {
			foreach($view['file'] as $k => $v) {
				if($v[view]) array_push($view_list, array("file_index"=>$k, "file"=>$v));
			}
		}

		foreach($idxList as $idx) {
			$v = $view_list[$idx]['file'];
			$fidx = $view_list[$idx][file_index];
			if(!$v) continue;
			$origin = $v['path'] . '/' .$v['file'];
			if($use_thumb) {
				$img = $thumb->getBoardThumb($wr_id=$view['wr_id'], $thumb_width=$width, $thumb_height=$height, $img_idx=$fidx, $quality=100, $use_crop=-1);
			} else {
				$img = $origin;
			}
			$str .= "<a href='$origin' class='wiki_image wiki_modal' style='$style' rel='$rn'><img src='$img' class='$align' style='$imgstyle' border='0'/></a>";

		}

		// 삭제
		if(!$str) return ""; //"{{".$matches[0]."}}";

		return $str;
	}

	/**
	 *
	 * 첨부 파일
	 *
	 * FORMAT : {{file=0 보여질 이름}}
	 *
	 * @deprecated
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 파일링크
	 */
	public function wiki_file($matches, $params) {

		$view = &$params[view];

		// $matches[1] => file index
		// $matches[3] => display name if there it is

		$find_file_index = $matches[1];
		$str = "";
		$fIdx = 0;

		if(is_array($view['file'])) {
			foreach($view['file'] as $k=>$v) {
				if(!$v[view] && $v['source']) {	// not count image
					if($fIdx == $find_file_index) {
						$title = ( $matches[3] ? $matches[3] : $v['source'] );
						$href = "javascript:file_download('".$this->g4['bbs_path']."/download.php?bo_table=".$this->wiki['bo_table']."&wr_id=".$view['wr_id']."&no=".$k."', '".$title."')";
						return "<a href=\"$href\" class=\"wikiFile\" style=\"$style\">$title</a>";
					}
					$fIdx++;
				}
			}
		}
		return "{{".$matches[0]."}}";
	}

	/**
	 *
	 * 미디어 파일/이미지
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @param array $close 태그 닫을지 말지
	 * @return string 이미지 또는 파일 링크
	 */
	public function wiki_media($matches, $params) {
		$media = wiki_class_load("Media");

		// [0] : all string
		// [1] : path
		// [4] : parameters
		// [6] : title

		if(preg_match("/^(http)/", $matches[1])) {
			if($matches[4]) parse_str(str_replace("&amp;", "&", $matches[4]));
			$use_thumb = false;
			if($width && $height) {
				$width = intval($width);
				$height= intval($height);
				$use_thumb = true;
			}
			if($use_thumb) $add = "width='$width' height='$height'";
			else $add = "";
			return "<a href='$matches[1]' class='wiki_image wiki_modal' style='$style' rel='$rn'><img src='$matches[1]' class='$align' style='$imgstyle' $add border='0'/></a>";
		}

		list($ns, $filename, $filepath) = wiki_page_name($matches[1]);
		$fileinfo = $media->getFile($ns, $filename);

		if(!$fileinfo) return '<span class="no_media">[파일없음 : '.$matches[1].']</span>';
		if($fileinfo['img_width'] > 0) {
			return $this->_wiki_media_image($fileinfo, $matches[4], $matches[6], &$params);
		} else {
			return $this->_wiki_media_file($fileinfo, $matches[4], $matches[6], &$params);
		}
	}

	/**
	 *
	 * 미디어 이미지
	 *
	 * FORMAT : {{media=/폴더1/폴더2/이미지}}
	 * FORMAT : {{media=/폴더1/폴더2/이미지?style=float:left;margin-right:5px}} : style for 'a' wrapping image
	 * FORMAT : {{media=/폴더1/폴더2/이미지?imgstyle=padding:2px;border:1px solid #ccc}} : style for image
	 *
	 * @param array $fileinfo 파일 정보 배열
	 * @param string $args ? 다음의 문자열
	 * @param string $title 제목
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 이미지 링크
	 */
	private function _wiki_media_image($fileinfo, $args, $title, $params) {

		if($args) parse_str(str_replace("&amp;", "&", $args));

		if(!$title) $title = $fileinfo['source'];

		$use_thumb = false;
		if($width && $height) {
			$width = intval($width);
			$height= intval($height);
			$use_thumb = true;
		}
			
		$rn = rand(1, 999999);
		$origin = $fileinfo['imgsrc'];
		if($use_thumb) {
			$thumb = wiki_class_load("Thumb");
			$img = $thumb->getMediaThumb($ns=$fileinfo['ns'], $filename=$fileinfo['source'], $width, $height, $quality=90);
		} else {
			$img = $fileinfo['imgsrc'];
		}

		return "<a href='$origin' class='wiki_image wiki_modal' style='$style' rel='$rn'><img src='$img' class='$align' style='$imgstyle' border='0' title='$title'/></a>";

	}

	/**
	 *
	 * 미디어 파일
	 *
	 * FORMAT : {{media=/폴더1/폴더2/파일|보여질파일명}}
	 *
	 * @param array $fileinfo 파일 정보 배열
	 * @param string $args ? 다음의 문자열
	 * @param string $title 제목
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string 파일 링크
	 */
	private function _wiki_media_file($fileinfo, $args, $title, $params) {
		if(!$title) $title = $fileinfo['source'];
		return "<a href=\"{$fileinfo['href']}\" class=\"wikiFile\" style=\"$style\">$title</a>";
	}

	/**
	 *
	 * 목차 출력 안함
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string ""
	 */
	public function wiki_no_toc($matches, $params) {
		$this->no_toc = true;
		return "";
	}

	/**
	 *
	 * 댓글 사용
	 *
	 * @param array $matches 매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string ""
	 */
	public function wiki_comment($matches, $params) {
		$params['view']['use_comment'] = true;
		return "";
	}

	/**
	 *
	 * 플러그인 설명
	 *
	 * @return string 플러그인 설명
	 */
	public function description()
	{
		return "byfun 위키 기본 문법 해석기 (저자 : byfun, byfun@byfun.com)";
	}

}



?>
