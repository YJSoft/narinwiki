<?php
	
define("EVENT_AFTER_PARSING_ALL", "EVT_PARSING_FINSISHING_ALL");
define("EVENT_AFTER_PARSING_LINE", "EVT_PARSING_FINSISHING_LINE");
	
class NarinParser extends NarinClass
{
	protected $id = "wiki_parser";
  protected $nowikis = array();
  protected $pres = array();
	protected $blockParsers = array();
	protected $variableParsers = array();
	protected $lineParsers = array();
	protected $wordParsers = array();
	protected $events = array();
	protected $plugins = array();
  protected $currentBlockParser = null;	  
  protected $currentWordParser = null;	  
	protected $view = null;
	protected $output = array();
	protected $ele = array("/~~NOCACHE~~/");
	
	public $stop = false;
	public $stop_all = false;
	public $current_row = 0;
	
	
	/**
	 * 생성자
	 */
	public function __construct() {

		parent::__construct();
											
		$this->loadPlugins();
		
	}	
	
	protected function init() {
	  $nowikis = array();
		$stop = false;
		$stop_all = false;
		$current_row = 0;
	  $currentBlockParser = null;	  
	  $currentWordParser = null;	  
		$view = null;
		$output = array();		
	}
	
	/**
	 * 플러그인 로드
	 */
	protected function loadPlugins()
	{
		include_once $this->wiki[path]."/lib/narin.Plugin.class.php";
		include_once $this->wiki[path]."/lib/narin.SyntaxPlugin.class.php";
				
		$path = $this->wiki[path]."/plugins";
		$use_plugins = array();
		foreach($this->wiki_config->using_plugins as $v) $use_plugins[$v] = $v;
		
		// 기본 문법 해석기 로드
		include_once "narin.syntax.php";
		$syntax = new NarinSyntax();
		$syntax->register($this);
		array_push($this->plugins, $syntax);
		// syntax 플러그인 로드
		
		$d = dir($path);		
		while ($entry = $d->read()) {
			
			$pluginPath = $path ."/". $entry;
			
			if(is_dir($pluginPath) && substr($entry, 0, 1) != ".") {
				
				if(!$use_plugins[$entry]) {
					continue;
				}
				
				$classFile = $pluginPath ."/syntax.php";				
				if(file_exists($classFile)) {
			
					$realClassName = "NarinSyntax".ucfirst($entry);
					include_once $classFile;
			
					if(class_exists($realClassName)) {
						
						$p = new $realClassName();
						array_push($this->plugins, $p);
						if(!is_a($p, "NarinSyntaxPlugin")) continue;				
						$p->register($this);
					}
					
				}
			}
		}		
		
	}

  /***************************************************
   * 위키 문법 분석
   * @param string  $text 문서 내용 
   *                (dhtml 아니어야 함, html char 변환된 상태 e.g. > to &gt;)
   **************************************************/
  public function parse(&$view)
  {
  	$this->init();
  	foreach($this->plugins as $p) {
  		$p->init();  	
  	}
  	
  	$this->view = &$view;
  	
  	$text = $this->_wikiTxt($view[wr_content]);
		$this->output = array();  	    

    // nowiki 와 nowiki_block 저장 
    $text = preg_replace_callback('/&lt;pre&gt;(.*?)&lt;\/pre&gt;/si',array($this,"_savePre"),$text); 
    $text = preg_replace_callback('/&lt;nowiki&gt;(.*?)&lt;\/nowiki&gt;/si',array($this,"_saveNoWiki"),$text);    
    
    // block parser 실행
    foreach($this->blockParsers as $id => $p) {
    	$this->currentBlockParser = $p;
    	$text = preg_replace_callback('/'.$p[start_regx].'(.*?)'.$p[end_regx].'/si', array($this, "do_block_parser"), $text);
    }

    
    // 라인별로 파싱
    $lines = explode("\n",$text);
    
    foreach ($lines as $k=>$line)
    {
    	$this->current_row = $k;
      $line = $this->parse_line($line);
      array_push($this->output, $line);
    }
		
  	
  	// 출력 버퍼 병합
  	$output_string = implode(" ", $this->output);
		
  	// URL 자동 링크	
		$output_string = str_replace("HREF=", "class='wiki_external_link' href=", url_auto_link($output_string));  	

		$output_string = $this->emoticons($output_string);

		// 이벤트
		$this->trigger_event(EVENT_AFTER_PARSING_ALL, array("lines"=>&$this->output, "output"=>&$output_string, "parser"=>&$this, "view"=>$this->view));

  	$output_string = preg_replace($this->ele, "", $output_string);
  	
  	// nowiki 복원
    $output_string = preg_replace_callback('/<pre><\/pre>/i', array($this,"_restorePre"), $output_string);
    $output_string = preg_replace_callback('/<nowiki><\/nowiki>/i', array($this,"_restoreNoWiki"), $output_string);

    $output_string = "<div class='narin_contents'>".$output_string."</div>";
        
    return $output_string;
  }
  
  
  
  
	/***************************************************
	 * 라인 분석
	 **************************************************/
  protected function parse_line($line)
  {
    $this->stop = false;
    $this->stop_all = false;

    $called = array();
    $line = rtrim($line);
		
		// 라인 핸들
    foreach ($this->lineParsers as $id => $p)
    {
    	$regex = $p[regx];
    	$klass = $p[klass];
    	$func = $p[func];

      if (preg_match("/$regex/i", $line, $matches))
      {
        $called[$id] = true;               
        $line = $klass->$func($matches, array("lines"=>&$this->output, "parser"=>&$this, "view"=>&$this->view));
        if ($this->stop || $this->stop_all)
        {
          break;
        }
      }
    }
    
    // 단어 핸들
    if (!$this->stop_all)
    {
      $this->stop = false;
           
      foreach ($this->wordParsers as $k => $p)
      {
	    	$regex = $p[regx];
	    	$klass = $p[klass];
	    	$func = $p[func];      	
    		$this->currentWordParser = $p;
        $line = preg_replace_callback("/$regex/i",array($this, "do_word_parser"),$line);
        if ($this->stop)
        {
          break;
        }
      }
      
      // variable 포멧 처리 : {{ something }}
      $line = preg_replace_callback('/('. '\{\{' . '([^\}]*?)' . '\}\}' . ')/', array($this, "parse_variable"), $line);      
      
    }

    $isline = strlen(trim($line)) > 0;

		// 이벤트 (EVENT_PARSING_FINISHING_LINE)		
		$this->trigger_event(EVENT_AFTER_PARSING_LINE, array("line"=>&$line, "called"=>$called, "lines"=>&$this->output, "parser"=>&$this, "view"=>&$this->view));

    return $line;
  }
   
   
   

	/***************************************************
	 * 변수 분석
	 **************************************************/
  protected function parse_variable($matches)
  {
		$loc = wiki_input_value($this->folder);
		$path = $this->wiki[path];

    foreach ($this->variableParsers as $id => $p)
    {
    	$regex = $p[start_regx]."(.*?)".$p[end_regx]."$";    	
      if (preg_match("/$regex/i", $matches[2], $m))
      {
				if(method_exists($p[klass], $p[func])) {
					return $p[klass]->$p[func]($m, array("lines"=>&$this->output, "parser"=>&$this, "view"=>&$this->view) );
				}		        
      }
    }    
    
		return $matches[0];
  }
  
  /**************************************************/
  
  
  
	/**
	 * event 추가
	 */
	public function addEvent($eventType, $class, $func) {
		$this->events[$eventType][] = array("klass"=>$class, "func"=>$func);		
	}
	
	/**
	 * event 실행
	 */
	protected function trigger_event($eventType, $params=array()) {
		foreach($this->events[$eventType] as $handler) {
			$handler[klass]->$handler[func]($params);
		}
	}
	
	/**
	 * block parser 추가
	 * @param $id (string) 플러그인 고유 아이디
	 * @param $class (object) 플러그인 객체
	 * @param $start_regx (string) 블럭 시작 표현식
	 * @param $end_regx (string) 블럭 끝 표현식
	 * @param $func (string) 핸들러 
	 */
	public function addBlockParser($id, $class, $start_regx, $end_regx, $func)
	{
		if($this->blockParsers[$id]) throw Exception("Already exists block parser : " . $id);		
		$this->blockParsers[$id] = array("klass"=>$class, "start_regx"=>$start_regx, "end_regx"=>$end_regx, "func"=>$func);
	}

	/**
	 * variable parser 추가
	 */
	public function addVariableParser($id, $class, $start_regx, $end_regx, $func)
	{		
		if($this->variableParsers[$id]) throw Exception("Already exists variable parser : " . $id);		
		$this->variableParsers[$id] = array("klass"=>$class, "start_regx"=>$start_regx, "end_regx"=>$end_regx, "func"=>$func);
	}
	
	/**
	 * line parser 추가
	 */	
	public function addLineParser($id, $class, $regx, $func)
	{
		if($this->lineParsers[$id]) throw Exception("Already exists line parser : " . $id);
		$this->lineParsers[$id] = array("klass"=>$class, "regx"=>$regx, "func"=>$func);
	}	
	
	/**
	 * word parser 추가
	 */	
	public function addWordParser($id, $class, $regx, $func)
	{
		if($this->wordParsers[$id]) throw Exception("Already exists word parser : " . $id);
		$this->wordParsers[$id] = array("klass"=>$class, "regx"=>$regx, "func"=>$func);
	}	
  
  
  
  /**
   * block parser 실행
   */
  protected function do_block_parser($matches)
  {
  	$p = $this->currentBlockParser; 
  	return $p[klass]->$p[func]($matches, array("lines"=>&$this->output, "parser"=>$this, "view"=>&$this->view));
  }
  
  /**
   * word parser 실행
   */
  protected function do_word_parser($matches)
  {
  	$p = $this->currentWordParser; 
  	return $p[klass]->$p[func]($matches, array("lines"=>&$this->output, "parser"=>$this, "view"=>&$this->view));
  }  
  

	/**
	 * <nowiki></nowiki> 내용 저장
	 * @param array $matches 
	 */
  protected function _saveNoWiki($matches)
  {
    array_push($this->nowikis,$matches[1]);
    return "<nowiki></nowiki>";
  }
  
	/**
	 * <pre></pre> 내용 저장
	 * @param array $matches 
	 */
  protected function _savePre($matches)
  {
    array_push($this->pres,$matches[1]);
    return "<pre></pre>";
  }  
  
	/**
	 * <nowiki></nowiki> 내용 복구
	 * @param array $matches 
	 */
  protected function _restoreNoWiki($matches)
  {
    $m = $this->nowikis[0];
    array_shift($this->nowikis);
    return $m;
  }
  
	/**
	 * <pre></pre> 내용 복구
	 * @param array $matches 
	 */
  protected function _restorePre($matches)
  {
    $m = $this->pres[0];
    array_shift($this->pres);
    return "<pre>".$m."</pre>";
  }  

	/**
	 * 이모티콘 처리
	 */
	protected function emoticons($content) {
		$source = array("8-)", "8-O", ":-(", ":-)", ":=)", ":-/", ":-\\", ":-?", ":-D", ":-P", ":-O", ":-X", ":-|", ";-)", "^_^", ":?:", ":!:", "LOL", "FIXME", "DELETEME");
		foreach($source as $k => $v) {
			$source[$k] = "/".preg_quote($source[$k], "/")."/";
		}
		$target = array("icon_cool.gif", "icon_eek.gif", "icon_sad.gif", "icon_smile.gif", "icon_smile2.gif", "icon_doubt.gif", "icon_doubt2.gif", "icon_confused.gif", "icon_biggrin.gif", "icon_razz.gif", "icon_surprised.gif", "icon_silenced.gif", "icon_neutral.gif", "icon_wink.gif", "icon_fun.gif", "icon_question.gif", "icon_exclaim.gif", "icon_lol.gif", "fixme.gif", "delete.gif", );		
		foreach($target as $k => $v) {
			$target[$k] = "<img src=\"{$this->wiki[path]}/imgs/smileys/$v\" class=\"middle\" alt=\"\\0\" title=\"\\0\"/>";
		}		 		
		$content = preg_replace($source, $target, $content);
		return $content;
	}

	/**
	 * html TO text
	 */
	protected function _wikiTxt($content)
	{
		$content = html_symbol($content);		
		$content = get_text($content, 0);		
		return $content;
	}
}