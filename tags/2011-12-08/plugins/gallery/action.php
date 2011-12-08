<?
/**
 * 
 * 나린위키 갤러리 플러그인 액션 클래스 스크립트
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 * 
 * 나린위키 갤러리 플러그인 : 액션 클래스
 *
 * @package	narinwiki
 * @subpackage plugin
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinActionGallery extends NarinActionPlugin {
	
	/**
	 * 
	 * @var string 기본 너비
	 */	
	var $width = 100;

	/**
	 * 
	 * @var boolean 기본 높이
	 */	
	var $height = 75;
	

	
	/**
	 * 
	 * 생성자
	 */
	public function __construct() {
		$this->id = "wiki_action_gallery";		
		parent::__construct();
	}	  	

	/**
	 * 
	 * @see lib/NarinActionPlugin::register()
	 */
	public function register($ctrl)
	{
		$ctrl->addHandler("PX_GALLERY_VIEW", $this, "on_ajax_call");
	}	
	
	/**
	 * 
	 * AJAX 콜에 대한 응답
	 * 
	 * ajax로 문서 lock 갱신
	 * 
	 * @param array $params {@link NarinEvent) 에서 넘겨주는 파라미터
	 */
	public function on_ajax_call($params) {

		$member = $this->member;
		
		$get = $params['get'];
		
		// 크롭 사용?
		$crop = (isset($get['nocrop']) ? false : true);
		
		// 썸네일 품질
		$quality= (isset($get['q']) && is_int($get['q']) ? $get['q'] : 90);
		
		// 패턴 처리
		$add_where = "";
		foreach($params['get'] as $k => $v) {
			if(strpos($k, '*')) {
				// *.jpg => %.jpg
				// image_* => image_%
				// image_*.jpg => image%.jpg
				$add_where = ' AND m.source LIKE "' . addslashes(str_replace('*', '%', $k)) . '"';
				break;				
			}
		}
		
		// 페이징 처리
		$paging = $get['paging'];
		$page = $get['page'];		
		if($paging > 0 && $page > 0) {
			$arg_paging = array('page'=>$page, 'page_rows'=>$paging);
		} else $arg_paging = array();

		// 너비, 높이 처리
		$width = (isset($get['width']) ? $get['width'] : $this->width);
		$height = (isset($get['height']) ? $get['height'] : $this->height);
		
		if(isset($get['width']) && !isset($get['height'])) {
			$height = -1;
		}
		if(isset($get['height']) && !isset($get['width'])) {
			$width = -1;
		}			
		
		
		// 정렬 처리
		$sort = $get['sort'];
		$possible_ordering = array('name'=>'source', 'date'=>'reg_date', 'filesize'=>'filesize', 'width'=>'img_width', 'height'=>'img_height', 'random'=>'random');		
		if(isset($possible_ordering[$sort])) {
			$order = ( isset($get['reverse']) ? 'ASC' : 'DESC' );
			$arg_ordering = array('by'=>$possible_ordering[$sort], 'order'=>$order);
		} else $arg_ordering = array('by'=>'reg_date', 'order'=>'DESC');
		
		
		// 이미지 목록 가져오기
		$path = wiki_ajax_data($get['path']);		
		$wikiMedia = wiki_class_load('Media');
		$ns = $wikiMedia->getNS($path);
		if($ns['ns_access_level'] > $member['mb_level']) {
			echo wiki_json_encode(array('code'=>-1, 'msg'=>'권한이 없어 이미지를 표시할 수 없습니다.'));
			exit;
		}
		
		list($total, $from_record, $page_rows, $files) = $this->get_media_list($path, $add_where, $arg_paging, $arg_ordering);
				
		// 목록 정리		
		$images = array();
		$wikiThumb = wiki_class_load('Thumb');

		foreach($files as $k=>$f) {
			if(!$f['img_width']) continue;
			list($w, $h) = $this->get_size($width, $height, $f['img_width'], $f['img_height']);
			$thumb = $wikiThumb->getMediaThumb($f['ns'], $f['source'], $w, $h, $quality, $crop);
			array_push($images, array('name'=>$f['source'], 
															  'thumb'=>$thumb, 
															  'href'=>$f['imgsrc'], 
															  'thumb_width'=>$w,
															  'thumb_height'=>$h,
															  'width'=>$f['img_width'], 
															  'height'=>$f['img_height'],
															  'filesize'=>wiki_file_size($f['filesize']),
															  'filesize_byte'=>$f['filesize'],
															  'user'=>$f['mb_id'],															  
															  'date'=>$f['reg_date']
															  ));
		}
		
		$more = 0;
		if(!empty($images) && !empty($arg_paging)) {
			if(count($images) >= $page_rows && $total != $from_record + $page_rows) $more = 1;
		}
		

		echo wiki_json_encode(array('code'=>1, 'files'=>$images, 'more'=>$more));
		exit;
	}

	/**
	 * 
	 * NarinMedia->getList 를 위한 이미지 필터
	 *
	 * @param int $w 원하는 썸네일 너비
	 * @param int $h 원하는 썸네일 높이
	 * @param int $img_w 이미지 너비
	 * @param int $img_w 이미지 높이
	 * @return array 만들어야할 썸네일 너비와 높이 array(width, height)
	 */
	public function get_size($w, $h, $img_w, $img_h) {
		if($w > 0 && $h > 0) return array($w, $h);		
		if($w < 0) return array($h*$img_w/$img_h, $h);
		if($h < 0) return array($w, $w*$img_h/$img_w);
	}
	
	
	
	function get_media_list($parent = "/", $add_where = "", $paging = array(), $ordering = array()) {
		$escapedParent = mysql_real_escape_string($parent);
		$regp = ($parent == "/" ? "/" : $escapedParent."/");

		$top = "";
		$use_paging = false;
		$limit = "";
		$from_record = 0;
		$page_rows = 0;
		if(isset($paging['page']) && isset($paging['page_rows']) && $ordering['by'] != 'random') {
			$sql = "SELECT count(m.id) as count FROM ".$this->wiki['media_ns_table']." AS nt 
							LEFT JOIN ".$this->wiki['media_table']." AS m 
					  		ON nt.ns = m.ns AND nt.bo_table = m.bo_table 
					  	WHERE nt.ns = '$escapedParent' AND nt.bo_table = '".$this->wiki['bo_table']."'";
			$tmp = sql_fetch($sql);
			$total = $tmp['count'];
			$page = $paging['page'];
			$page_rows = $paging['page_rows'];
			$from_record = ($page - 1) * $page_rows; // 시작 열을 구함
			$limit = " LIMIT $from_record, " .  $page_rows;
			
			$use_paging = true;
		}
		
		$order_by = 'm.reg_date';
		$order = 'DESC';
		 
		$order_by = 'm.'.$ordering['by'];
		$order = $ordering['order'];
		if($ordering['by'] == 'random') {
			$order_by = 'RAND()';
			$order = '';
			if($limit) {
				$limit = '';
			}
		}

		$sql = "SELECT m.id, nt.ns, m.source, m.file, m.filesize, m.downloads, m.reg_date, m.img_width, m.img_height, m.img_type, m.mb_id, mb.mb_name, mb.mb_nick
					  FROM ".$this->wiki['media_ns_table']." AS nt 
					  LEFT JOIN ".$this->wiki['media_table']." AS m 
					  	ON nt.ns = m.ns AND nt.bo_table = m.bo_table 
					  LEFT JOIN {$this->g4['member_table']} AS mb 
					  	ON m.mb_id = mb.mb_id 
					  WHERE nt.ns = '$escapedParent' AND nt.bo_table = '".$this->wiki['bo_table']."' $add_where
					  ORDER BY $order_by $order $limit";
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
		return array($total, $from_record, $page_rows, $files);
	}
		
	

}


?>