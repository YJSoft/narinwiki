<?
/**
 *
 * 나린위키 리스트 플러그인 문법 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 리스트 플러그인 : 문법 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinSyntaxList extends NarinSyntaxPlugin {
	 
	/**
	 *
	 * @see lib/NarinSyntaxPlugin::register()
	 */
	function register($parser)
	{
		$parser->addVariableParser(
          $id = "wiki_list",
          $klass = $this,
          $startRegx = "list=",	// list=
          $endRegx = "((\?)(.*?))?",	// 파라미터 ? 다음 문자열 (optional)
          $method = "wiki_list");
	}

	/**
	 * 
	 * HTML 변환
	 * 
	 * @param array $matches 패턴매칭 결과
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function wiki_list($matches, $params) {
		// $matches[1] = list=
		// $matches[1] = 경로
		// $matches[4] = 파라미터
		
		$args = array();

		if($matches[4]) {
			parse_str($matches[4], $args);	
		}
				
		$args['path'] = $matches[1];

		$list = $this->wiki_list_nojs($args, &$params);
		
		$options = wiki_json_encode($args);		
		
		return '<nocache plugin="list" method="cache_render" params="'.addslashes($options).'">'.$list.'</nocache>';
		//return $list.'<div class="wiki_lister" style="display:none">'.$options.'</div>';
	}

	/**
	 * 
	 * 부분 캐시 랜더 함수
	 * 
	 * @param array $args {@link narin.php} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	public function cache_render($args) {
		return $this->wiki_list_nojs($args, null);
	}

	/**
	 * 
	 * 자바스크립트를 사용하지 않는 출력
	 * 
	 * @param array $args 최근문서문법에서 분석된 파라미터
	 * @param array $params {@link NarinParser} 에서 전달하는 파라미터
	 * @return string HTML 태그
	 */
	protected function wiki_list_nojs($args, $params) {

		define("_LIST_PLUGIN_", 1);
		include_once dirname(__FILE__).'/list.lib.php';
		
		$recursive = (isset($args['nosub']) ? false : true);
		$rows = (isset($args['rows']) ? $args['rows'] : 5);
		$cutstr = (isset($args['title_length']) ? $args['title_length'] : 512);
		$dateformat = (isset($args['dateformat']) ? $args['dateformat'] : "Y-m-d h:i:s");
		$current_time = $this->g4['time_ymdhis'];
		$reverse = (isset($args['reverse']) ? true : false);
		$with_content = ($args['type'] == 'webzine' ? true : false);
		
		$args['emp'] = (isset($args['emp']) ? $args['emp'] : 0);
		$args['emp_style'] = (isset($args['emp_style']) ? $args['emp_style'] : 'font-weight:bold');
		$args['field'] = isset($args['field']) ? $args['field'] : 'title,editor,date';
		$args['title_length'] = isset($args['title_length']) ? $args['title_length'] : 512;
		$args['order'] = isset($args['order']) ? $args['order'] : 'date';
		
		$wild = '';
		foreach($args as $k => $v) {
			if(strpos($k, '*') !== false) {
				$wild = $k;				
				break;				
			}
		}		
		
		$list = wiki_list_docs($this->wiki, $this->g4, stripslashes($args['path']), $args['order'], $wild, $recursive, $dateformat, $rows, $cutstr, $reverse, $with_content);
		
		// TODO : 웹진모드  (보류)
		if($args['type'] == 'table' || $args['type'] == 'webzine') return $this->render_table($args, &$list, &$params);
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
		$ul = '<ul class="wiki_list wiki_list_1 list_list">';
		
		// 목록
		for($i=0; $i<count($list); $i++) {
			$item = $list[$i];
			
			$emp = '';
			if($args['emp'] > 0) {
				if($this->elapsed_hour($item['datetime']) <= $args['emp']) {
					$emp = ' '.$args['emp_style'];
				}
			}		
			$row = '<li><a href="'.$item['href'].'" class="wiki_active_link list_title" style="'.$args['title_style'].$emp.'">'.$item['title'].'</a>';
			if(!isset($args['nocomment']) && $item['comments'] > 0) {
				$row .= '<span class="list_comment" style="'.$args['comment_style'].'">('.$item['comments'].')</span>';
			}

			if(isset($args['showfolder'])) {
				$row .= '<span class="list_folder" style="'.$args['folder_style'].'">'.$item['ns'].'</span>';
			}
			
			if(isset($args['showeditor'])) {
				$name = $item['name'];
				if(isset($args['usename'])) $name = $item['name'];
				else if(isset($args['usenick'])) $name = $item['nick'];
				
				$row .= '<span class="list_editor" style="'.$args['folder_style'].'">'.$name.'</span>';
			}	
			
			if(isset($args['showdate'])) {
				$date = $item['date'];
				if(isset($args['elapsed'])) $date = $item['elapsed'];
				$row .= '<span class="list_date" style="'.$args['date_style'].'">'.$date.'</span>';
			}					
			
			$row .= '</li>';

			$ul .= $row;
		}		
				
		unset($list);				
		return $ul.'</ul>';
		
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

		$head = array('title'=>'문서명', 'date'=>'날짜', 'editor'=>'편집자', 'hits'=>'조회수');
		
		$field = explode(',', str_replace(' ', '', $args['field']));
		
		$table_style = (isset($args['table_style']) ? ' style="'.$args['table_style'].'"' : '');
		
		// 테이블 열기
		$table = '<table cellspacing="0" cellpadding="0" class="list_table" '.$table_style.'>';

		if(isset($args['elapsed'])) $head['date'] = '시간';
		
		// 테이블 해드
		if(!isset($args['nohead'])) {
			$table .= '<thead><tr>';
			for($i=0; $i<count($field); $i++) {
				$table .= '<td class="list_'.$field[$i].'">'.$head[$field[$i]].'</td>';
			}
			$table .= '</tr></thead>';
		}

		$table .= '<tbody>';

		// 테이블 바디
		for($i=0; $i<count($list); $i++) {

			$item = $list[$i];
			$td = '';
			
			
			// 필드 순서대로 출력
			for($j=0; $j<count($field); $j++) {
				$content = $item[$field[$j]];
				$td .= '<td class="list_'.$field[$j].'" style="'.$args[$field[$j].'_style'].'">';
				
				// 필드 : 데이트
				if($field[$j] == 'date') {
					if(isset($args['elapsed'])) $content = $item['elapsed'];					
				}
				
				// 필드 : 편집자
				if($field[$j] == 'editor') {
					if(isset($args['usename'])) { 
						$content = $item['name'];
					} else if(isset($args['usenick'])) { 
						$content = $item['nick'];
					}
				}	
				
				// 필드 : 제목
				if($field[$j] == 'title') {

					$emp = '';
					if($args['emp'] > 0) {
						if($this->elapsed_hour($item['datetime']) <= $args['emp']) {
							$emp = ' style="'.$args['emp_style'].'"';
						}
					}

					// 제목에 링크
					$content = '<a href="'.$item['href'].'" class="wiki_active_link" '.$emp.'>'.$content.'</a>';
					
					// 댓글
					if(!isset($args['nocomment']) && $item['comments'] > 0) {
						$content .= '<span class="list_comment">'.$item['comments'].'</span>';
					}				
					
					// 폴더	
					if(!isset($args['nofolder'])) {
						$content .= '<span class="list_folder">'.$item['ns'].'</span>';
					}
					
					// TODO : 내용 보이기 (보류)
					//
					//if($args['type'] == 'webzine') {
					//	$content .= '<div class="list_content" style="width:100%;overflow:auto">'.$item['content'].'</div>';
					//}			
										
				}
				
				// TD에 내용 추가
				$td .= $content . '</td>';
				
			} // for $j
						
			// TABLE에 TR 추가
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
