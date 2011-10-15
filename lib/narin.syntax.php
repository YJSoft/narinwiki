<?
class NarinSyntax extends NarinSyntaxPlugin {
	
	var $id; 
  var $sections = array();
  var $toc = array();
  var $footnotes = array();
  var $section_level = -1;
  
  var $list_level_types = array();    
  var $list_level = 0;

	var $table_rowspan = array();	
	var $table_opened = false;

  var $boxformat = false;
  var $preformat = false;
  var $pformat = false;
	var $no_toc = false;
	
	public function __construct() {
		$this->id = "wiki_default_parser";		
		parent::__construct();
	}	  	
	
	/**
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
	 * 파서 등록
	 */	
	function register($parser)
	{
        		
    $line_regs = array(
    	'wiki_table'=>'^(\^|\|)(.*?)(\^|\|)$',
      'wiki_ul'=>'^(\s{2,}[\*])(.*?)$',
      'wiki_ol'=>'^(\s{2,}[-])(.*?)$',    
      'wiki_box'=>'^\s{2,}(.*?)$',
      'wiki_quoting'=>'^((&gt;)+)(.*?)$',
      'wiki_sections'=>'^(={1,6})(.*?)(={1,6})$',
      'wiki_hr'=>'^----$',
      'wiki_par'=>'^(.*?)$'
    );    
    
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
      'wiki_underline'=>'__(.*?)__',
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
    	"wiki_newpage"=>array("start_regx"=>"NEWPAGE", "end_regx"=>""),
    	"wiki_search"=>array("start_regx"=>"SEARCH", "end_regx"=>""),
    	"wiki_image"=>array("start_regx"=>"image=", "end_regx"=>"((\?)(.*?))?"),
    	"wiki_file"=>array("start_regx"=>"file=(\d)(\s+", "end_regx"=>")?")
    );
        
    /*    
    foreach($block_regs as $func => $v) {
    	$parser->addBlockParser(
					$id = $this->id."_".$func, 
					$klass = $this, 
					$startRegx = $v["start_regx"], 
					$endRegx = $v["end_regx"],
					$method = $func);		
    }        
		*/        
		
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
					$startRegx = $v["start_regx"], 
					$endRegx = $v["end_regx"],
					$method = $func);	    	
    }    
    
    $parser->addEvent(EVENT_AFTER_PARSING_ALL, $this, "wiki_after_all");
    $parser->addEvent(EVENT_AFTER_PARSING_LINE, $this, "wiki_after_parsing_line");
	}
	
	/**
	 * 라인 파싱을 모두 마치고
	 */
	public function wiki_after_all($params)
	{		
		// box format 이 남아있다면, tag close
		if ($this->preformat) $params[output] .= "</pre></div><!--// box-pre -->";
		if ($this->boxformat) $params[output] .= "</div><!--// box -->";

		// 열린 table 이 있다면, 닫아줌
		if($this->table_opened) {
			$params[output] .= "</table>";	
		}

  	// 열린 paragraph 있으면 닫아줌
  	if($this->pformat) {
  		$params[output] .= "</p>";	
  	}
  	    
    // 열린 section 이 있다면, 닫아줌
  	while($pSection = array_pop($this->sections)) {
  		$params[output] .= $pSection[close_tag];
  	} 
  	
		//if($params[view][wr_comment]) $this->no_toc = true;
			
		// 목차 추가
		if(!$this->no_toc && count($this->toc) > 2) {
			$toc = "";
			foreach($this->toc as $head) {
				$toc .= "<div class='toc toc_{$head[level]}'><a href=\"#".str_replace("\"", "", $head[title])."\">{$head[title]}</a></div>\n";
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
			$params[output] = $toc . $params[output];
		}
		
		if(count($this->footnotes)) {
			$fn = "<div id=\"wiki_footnotes\">\n";
			foreach($this->footnotes as $idx => $v) {
				$idx++;
				$fn .= "<div class=\"wiki_footnote_content\"><sup class=\"fn\"><a href=\"#footnote_top_{$idx}\" name=\"footnote_{$idx}\">{$idx})</a></sup> {$v}</div>\n";
			}
			$fn .= "</div>\n";
			$params[output] .= $fn;
		}
	}

  /**
   * 한 라인 파싱을 마치고
   */		
	public function wiki_after_parsing_line($params)
	{								
    // 이전 라인에서 목록을 열었으면.. 닫음
    if (($this->list_level>0) && (!$params[called][$this->id.'_wiki_ul'] && !$params[called][$this->id.'_wiki_ol']))
    {
      $params[line] = $this->wiki_list(false, array(), '', '', true) . $params[line];
    }
    if ($this->boxformat && !$params[called][$this->id.'_wiki_box'])
    {
      $params[line] = $this->wiki_box(false, array(), true) . $params[line];
    }    
    if ($this->pformat && !$params[called][$this->id.'_wiki_par'])
    {
      $params[line] = $this->wiki_par(false, array(), true) . $params[line];
    }    
    if ($this->table_opened && !$params[called][$this->id.'_wiki_table'])
    {
      $params[line] = $this->wiki_table(false, array(), true) . $params[line];
    }    		
    
    // internal 또눈 external 문서 링크에 이미지 사용
    $params[line] = preg_replace("/(<a[^>]*>)<a[^>]*>(<img[^>]*>)<\/a>(<\/a>)/i", "\\1\\2\\3", $params[line]);
	}		
		
		
	/**
	 * 테이블 분석
	 */
	public function wiki_table($matches, $params, $close = false)
	{	
		if($close) {
			$this->table_opened = false;
			return "</table> <!--// wiki_table -->\n";
		}
		
		$parser = &$params[parser];
		$lines = &$params[lines];

		
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
						$prev_row = $this->table_rowspan[$col][row];
					}
					
					$tmp = preg_split("/(<td|<th)(.*?>)([^<]*?)(<\/td>|<\/th>)/i", $lines[$prev_row], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
					for($jj=0; $jj<count($tmp); $jj++) {
						if(strpos($tmp[$jj], "class=\"col$col\">")) {
							if(isset($this->table_rowspan[$col])) {
								$tmp[$jj] = preg_replace("(rowspan=\"\d\")", "rowspan=\"".(++$this->table_rowspan[$col][rowspan])."\"", $tmp[$jj]);
							} else {
								$tmp[$jj] = " rowspan=\"2\"" . $tmp[$jj];
								$this->table_rowspan[$col][row] = $prev_row;
								$this->table_rowspan[$col][rowspan] = 2;
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
				preg_match("/^([\s]*)([^\s]*?)([\s]*)$/", $arr[$i+1], $m);
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
	 * Returns table tag (wiki_table 에서 사용)
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
	 * 해딩 (섹션)
	 */
  public function wiki_sections($matches, $params)
  {  	
		$parser = &$params[parser];
  	  	
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
    	$before = $closing[close_tag];
    	$after = $opening;    	
    } else {		// 큰 레벨로
    	for($i=count($this->sections)-1; $i>=0; $i--) {
    		$pSection = $this->sections[$i];
    		if($pSection[level] >= $level) {
    			$before .= $pSection[close_tag];
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
	 * 줄바꿈 (br)
	 */
  public function wiki_br($matches, $params)
  {
    return $matches[1]. "<br/>";
  }  
  
	/**
	 * 굵게 (b)
	 */
  public function wiki_bold($matches, $params)
  {
    return "<strong>".trim($matches[1]). "</strong>";
  }    
 
 	/**
	 * 기울게 (i)
	 */
  public function wiki_italic($matches, $params)
  {
    return "<i>".trim($matches[1]). "</i>";
  }  
 
 	/**
	 * 밑줄 (underline)
	 */
  public function wiki_underline($matches, $params)
  {
    return "<span class='u'>".trim($matches[1]). "</span>";
  }  
  
 	/**
	 * 가운데줄 (strike)
	 */
  public function wiki_strike($matches, $params)
  {
    return "<del>".trim($matches[1]). "</del>";
  }    
  
 	/**
	 * 윗첨자 (sup)
	 */
  public function wiki_sup($matches, $params)
  {
    return "<sup>".trim($matches[1]). "</sup>";
  }    
  
 	/**
	 * 아래첨자 (sub)
	 */
  public function wiki_sub($matches, $params)
  {
    return "<sub>".trim($matches[1]). "</sub>";
  }  
  
 	/**
	 * 글자색 (color)
	 */
  public function wiki_color($matches, $params)
  {  	  	
    return "<span style=\"color:".trim($matches[1])."\">".trim($matches[2]). "</span>";
  }    
  
 	/**
	 * 글자 크기 (font-size)
	 */
  public function wiki_font_size($matches, $params)
  {  	  	
    return "<span style=\"font-size:".trim($matches[1])."\">".trim($matches[2]). "</span>";
  }       
  
  public function wiki_footnotes($matches, $params)
  {
  	$idx = count($this->footnotes)+1;
  	array_push($this->footnotes, trim($matches[1]));
  	return "<sup class=\"fn\"><a href=\"#footnote_{$idx}\" name=\"footnote_top_{$idx}\" id=\"footnote_top_{$idx}\">{$idx})</a></sup>";
  }      

	/**
	 * quoting
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
	 * 불릿 리스트
	 */
	public function wiki_ul($matches, $params, $close = false)
	{
		return $this->wiki_list($matches, $params, $listtype="ul", $listchar="*", $close);
	}

  /**
   * 숫자 리스트
   */
	public function wiki_ol($matches, $params, $close = false)
	{
		return $this->wiki_list($matches, $params, $listtype="ol", $listchar="-", $close);
	}	
		
	
	/**
	 * 리스트
	 */
  public function wiki_list($matches, $params, $listtype, $listchar, $close = false)
  {
  
    $output_string = "";
    $closed_list = false;

		// $matches[1] : *, **, *** ...
		$space = strlen($matches[1])-1;
		if($space % 2 && !$close) return;
		
		if($params) {
			$params[parser]->stop = true;;
		}
		
		$space = $space / 2;
    $newlevel = ($close ? 0 : $space);
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
	 * box
	 */
  public function wiki_box($matches, $params, $close = false)
  {  	  	  	
  	// 태그 닫기
    if ($close)
    {
      $this->boxformat = false;
      return "</div>\n";
    }
    
		$parser = &$params[parser];	
		
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
	 * pre
	 */
  public function wiki_pre($matches, $params, $close = false)
  {  	
  	// 태그 닫기
    if ($close)
    {
      $this->preformat = false;
      return "</pre></div><!--// box-pre -->\n";
    }
  	
  	$parser = &$params[parser];		
  	
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
	 * <hr/> 처리
	 */
  public function wiki_hr($matches, $params)
  {
  	$parser = &$params[parser];
  	$parser->stop = true;
    return "<hr/>";
  }
      
  public function wiki_par($matches, $params, $close = false) {
  	  	
  	// 태그 닫기
    if ($close || ( $this->pformat && !trim($matches[0]) ) )
    {
      $this->pformat = false;
      return "</p><!--// paragraph -->\n";
    }
  	
  	$parser = &$params[parser];    		
  	$parser->stop = true;
  	$p = $this->pformat ? "" : "<p>";

  	$this->pformat = true;
  	
  	return $p.$matches[0];
  }

	/**
	 * 위키 링크 생성
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
    list($ns, $docname, $full) = wiki_page_name($doc, $strip=false);	
        
		$article = wiki_class_load("Article");
		$is_exists = $article->exists($ns, $docname);
		if($is_exists) $class = "wiki_active_link";
		else $class = "wiki_inactive_link";
        		
    return sprintf(
      '<a href="%s" class="%s">%s</a>',
      $this->wiki[path]."/narin.php?bo_table=".$this->bo_table."&doc=".urlencode($doc),
      $class,
      $title
    );
  }

	/**
	 * 외부 링크 생성
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
	 * 폴더 안에 있는 폴더와 파일들을 출력함
	 * @format {{folder=/folder1/folder2}}
	 */	
	public function wiki_folder($matches, $params)
	{
		$tmp = explode("?", $matches[1]);
		$loc = $tmp[0];
		parse_str(str_replace("&amp;", "&", $tmp[1]));
				
		$wikiNS = wiki_class_load("Namespace");
		
		// Check level
		$n = $wikiNS->get($loc);		
		if($this->member[mb_level] < $n[ns_access_level]) return "";
		
		$files = $wikiNS->getList($loc, $withArticle=true);
		if(!count($files)) return "{{".$matches[0]."}}";
		
		$up = wiki_get_parent_path($loc);
		$str = "<ul class='folder_list' style='$style'>";
		//if($loc != "/") $str .= '<li class="folder_up"><a href="'.$this->wiki[path].'/folder.php?bo_table='.$this->wiki[bo_table].'&loc='.$up.'">..</a></li>';	
		for($i=0; $i<count($files); $i++) {	
			$str .= '<li class="'.$files[$i][type].'"><a href="'.$files[$i][href].'">'.$files[$i][name].'</a></li>';
		}
		$str .= '</ul>';
		return $str;
	}
	
	/**
	 * 새문서 만들기 폼 출력
	 * @format {{NEWPAGE}}
	 */	
	public function wiki_newpage($matches, $params)
	{	
		$loc = wiki_input_value($this->folder);
		$path = $this->wiki[path];		
		return <<<EOF
		
				<div class="wiki_newpage clear">
				<form action="$path/narin.php" method="get" class="wiki_form">
				<input type="hidden" name="bo_table" value="{$this->wiki[bo_table]}"/>
				<input type="hidden" name="loc" value="$loc"/>
				<input type="text" name="doc" class="txt" size="20"/>
				<span class="button"><input type="submit" value="문서만들기"></span>
				</form>    	
				</div>
				
EOF;
	}
	
	/**
	 * 검색 폼 출력
	 * @format {{SEARCH}}
	 */	
	public function wiki_search($matches, $params)
	{	
		$path = $this->wiki[path];		
		return <<<EOF
		
		<div class="wiki_search clear">
		<form action="$path/search.php" onsubmit="return wiki_search(this);"  class="wiki_form" method="get">
		<input type="hidden" name="bo_table" value="{$this->wiki[bo_table]}"/>
		<input type="text" class="search_text txt" name="stx" size="20"/>
		<span class="button"><input type="submit" value="검색"></span>
		</form>			
		</div>
		
EOF;
	}	
	
	
	/**
	 * 위키 이미지 출력
	 * @format {{image=0,1,2-5}}
	 * @format {{image=0,1,2-5?style=float:left;margin-right:5px}} : style for 'a' wrapping image
	 * @format {{image=0,1,2-5?imgstyle=padding:2px;border:1px solid #ccc}} : style for image
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
		foreach($view[file] as $k => $v) {
			if($v[view]) array_push($view_list, array("file_index"=>$k, "file"=>$v));
		}
		
		foreach($idxList as $idx) {		
			$v = $view_list[$idx][file];
			$fidx = $view_list[$idx][file_index];
			if(!$v) continue;
			$origin = $v[path] . '/' .$v[file];
			if($use_thumb) {											
				$img = $thumb->getBoardThumb($wr_id=$view[wr_id], $thumb_width=$width, $thumb_height=$height, $img_idx=$fidx, $quality=100, $use_crop=-1);
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
	 * Show attached file link
	 * @format {{file=0 보여질 이름}}
	 */		
	public function wiki_file($matches, $params) {	
		
		$view = &$params[view];
		
		// $matches[1] => file index
		// $matches[3] => display name if there it is
		
		$find_file_index = $matches[1];	
		$str = "";
		$fIdx = 0;

		foreach($view[file] as $k=>$v) {	
			if(!$v[view] && $v[source]) {	// not count image
				if($fIdx == $find_file_index) {
					$title = ( $matches[3] ? $matches[3] : $v[source] );					
					$href = "javascript:file_download('".$this->g4[bbs_path]."/download.php?bo_table=".$this->wiki[bo_table]."&wr_id=".$view[wr_id]."&no=".$k."', '".$title."')";
					return "<a href=\"{$href}\" class=\"wikiFile\" style=\"$style\">$title</a>";
				}	
				$fIdx++;		
			}
		}
		return "{{".$matches[0]."}}";
	}
	
	/**
	 * 목차 출력 안함
	 */
	public function wiki_no_toc($matches, $params) {
		$this->no_toc = true;
		return "";		
	}
	
	/**
	 * 댓글 사용
	 */
	public function wiki_comment($matches, $params) {
		$params[view][use_comment] = true;
		return "";		
	}	
	
	/**
	 * 플러그인 설명
	 */
	public function description()
	{
		return "byfun 위키 기본 문법 해석기 (저자 : byfun, byfun@byfun.com)";
	}
	
}



?>