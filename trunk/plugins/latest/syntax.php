<?
/**
 *
 * 나린위키 최근문서 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 최근문서 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxLatest extends NarinSyntaxPlugin {
	 
	/**
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		$parser->addVariableParser(
          $id = "wiki_latest",
          $klass = $this,
          $startRegx = "latest=",	// latest=
          $endRegx = "((\?)(.*?))?",	// 파라미터 ? 다음 문자열 (optional)
          $method = "wiki_latest");
	}

	/**
	 * 
	 * HTML 변환
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function wiki_latest($matches, $params) {
		// $matches[1] = latest=
		// $matches[1] = 경로
		// $matches[4] = 파라미터
		
		$args = array();

		if($matches[4]) {
			parse_str($matches[4], $args);	
		}
				
		$args['path'] = $matches[1];
		
		if(isset($args['noajax'])) {
			return $this->wiki_latest_noajax($args, &$params);			
		}
		
		if(isset($args['nojs'])) {
			return $this->wiki_latest_nojs($args, &$params);			
		}		
		
		$options = wiki_json_encode($args);		
		
		return '<div class="wiki_latest" style="display:none">'.$options.'</div>';
	}

	/**
	 * 
	 * Ajax를 사용하지 않는 출력
	 * 
	 * @param array $args 최근문서문법에서 분석된 파라미터
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	protected function wiki_latest_noajax($args, $params) {
		
		$ns = $args['path'];
		$recursive = (isset($args['nosub']) ? false : true);
		$rows = (isset($args['rows']) ? $args['rows'] : 5);
		$cutstr = (isset($args['title_length']) ? $args['title_length'] : 512);
		$dateformat = (isset($args['dateformat']) ? $args['dateformat'] : "Y-m-d h:i:s");

		define("_LATEST_PLUGIN_", 1);
		include_once dirname(__FILE__).'/latest.lib.php';
		
		$list = wiki_latest_recentUpdate($this->wiki, $this->g4, stripslashes($ns), $recursive, $dateformat, $rows, $cutstr);
		$args['list'] = array('code'=>1, 'current_time'=>$this->g4['time_ymdhis'], 'list'=>&$list);
		$options = wiki_json_encode($args);
		return '<div class="wiki_latest" style="display:none">'.$options.'</div>';
	}
	
	/**
	 * 
	 * 자바스크립트를 사용하지 않는 출력
	 * 
	 * @param array $args 최근문서문법에서 분석된 파라미터
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	protected function wiki_latest_nojs($args, $params) {

		define("_LATEST_PLUGIN_", 1);
		include_once dirname(__FILE__).'/latest.lib.php';
		
		$recursive = (isset($args['nosub']) ? false : true);
		$rows = (isset($args['rows']) ? $args['rows'] : 5);
		$cutstr = (isset($args['title_length']) ? $args['title_length'] : 512);
		$dateformat = (isset($args['dateformat']) ? $args['dateformat'] : "Y-m-d h:i:s");
		$current_time = $this->g4['time_ymdhis'];
		
		$args['emp'] = (isset($args['emp']) ? $args['emp'] : 0);
		$args['emp_style'] = (isset($args['emp_style']) ? $args['emp_style'] : 'font-weight:bold');
		$args['order'] = isset($args['order']) ? $args['order'] : 'title,editor,date';
		$args['title_length'] = isset($args['title_length']) ? $args['title_length'] : 512;

		
		
		$list = wiki_latest_recentUpdate($this->wiki, $this->g4, stripslashes($args['path']), $recursive, $dateformat, $rows, $cutstr);
		if($args['type'] == 'table') return $this->render_table($args, &$list, &$params);
		else return $this->render_list($args, &$list, &$params);
		
	}
	
	/**
	 * 
	 * 목록 형태로 출력
	 * 
	 * @param array $args 최근문서문법에서 분석된 파라미터
	 * @param array $list 최근문서목록
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	protected function render_list($args, $list, $params) {	
				
		$div = '';
		$ul = '<ul class="wiki_list_1 latest_list">';
		
		// 목록
		for($i=0; $i<count($list); $i++) {
			$item = $list[$i];
			
			$emp = '';
			if($args['emp'] > 0) {
				if($this->elapsed_hour($item['datetime']) <= $args['emp']) {
					$emp = ' '.$args['emp_style'];
				}
			}		
			$row = '<li>
								<a href="'.$item['href'].'" class="wiki_active_link latest_title" style="'.$args['title_style'].$emp.'">
									'.$item['title'].'
								</a>
						  ';
			if(!isset($args['nocomment']) && $item['comments'] > 0) {
				$row .= '<span class="latest_comment" style="'.$args['comment_style'].'">
									'.$item['comments'].'
								 </span>
								';
			}

			if(isset($args['showfolder'])) {
				$row .= '<span class="latest_folder" style="'.$args['folder_style'].'">
									'.$item['ns'].'
								 </span>
								';
			}
			
			if(isset($args['showeditor'])) {
				$name = $item['name'];
				if(isset($args['usename'])) $name = $item['name'];
				else if(isset($args['usenick'])) $name = $item['nick'];
				
				$row .= '<span class="latest_editor" style="'.$args['folder_style'].'">
									'.$name.'
								 </span>
								';
			}	
			
			if(isset($args['showdate'])) {
				$date = $item['date'];
				if(isset($args['elapsed'])) $date = $item['elapsed'];
				$row .= '<span class="latest_date" style="'.$args['date_style'].'">
									'.$date.'
								 </span>
								';
			}					
			
			$row .= '</li>';

			$ul .= $row;
		}		
				
		unset($list);				
		return '</ul>'.$ul;
		
	}
	
	/**
	 * 
	 * 테이블 형태로 출력
	 * 
	 * @param array $args 최근문서문법에서 분석된 파라미터
	 * @param array $list 최근문서목록
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	protected function render_table($args, $list, $params) {	

		$head = array('title'=>'문서명', 'date'=>'날짜', 'editor'=>'편집자');
		
		$order = explode(',', str_replace(' ', '', $args['order']));
		
		$table_style = (isset($args['table_style']) ? ' style="'.$args['table_steyl'].'"' : '');
		$table = '<table cellspacing="0" cellpadding="0" class="latest_table" $table_style>';

		if(isset($args['elapsed'])) $head['date'] = '시간';
		
		if(!isset($args['nohead'])) {
			$table .= '<thead><tr>';
			for($i=0; $i<count($order); $i++) {
				$table .= '<td class="latest_'.$order[$i].'">'.$head[$order[$i]].'</td>';
			}
			$table .= '</tr></thead>';
		}

		$table .= '<tbody>';

		for($i=0; $i<count($list); $i++) {

			$item = $list[$i];
			$td = '';
			
			for($j=0; $j<count($order); $j++) {
				$content = $item[$order[$j]];

				$td .= '<td class="latest_'.$order[$j].'" style="'.$args[$order[$j].'_style'].'">';
				
				if($order[$j] == 'date') {
					if(isset($args['elapsed'])) $content = $item['elapsed'];					
				}
				
				if($order[$j] == 'editor') {
					if(isset($args['usename'])) { 
						$content = $item['name'];
					} else if(isset($args['usenick'])) { 
						$content = $item['nick'];
					}
				}	
				
				if($order[$j] == 'title') {

					$emp = '';
					if($args['emp'] > 0) {
						if($this->elapsed_hour($item['datetime']) <= $args['emp']) {
							$emp = ' style="'.$args['emp_style'].'"';
						}
					}

					$content = '<a href="'.$item['href'].'" class="wiki_active_link" '.$emp.'>'.$content.'</a>';
					
					if(!isset($args['nocomment']) && $item['comments'] > 0) {
						$content .= '<span class="latest_comment">'.$item['comments'].'</span>';
					}					
					if(!isset($args['nofolder'])) {
						$content .= '<span class="latest_folder">'.$item['ns'].'</span>';
					}
				}
				
				$td .= $content . '</td>';
				
			} // for $j
			
			$table .= '<tr>'.$td.'</tr>';
			
		} // for $i

		$table .= '</tbody></table>';
		
		unset($list);
		return $table;
	}
		
	
	
	protected function elapsed_hour($datetime) {
		return (strtotime($this->g4['time_ymdhis']) - strtotime($datetime)) / 3600;
	}
}



?>