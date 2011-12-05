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
	var $height = 75;	// 실제로 안쓰임;;
	

	
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
		$ctrl->addHandler("AJAX_CALL", $this, "on_ajax_call");
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
		
		if($params['plugin'] != 'gallery' || $params['method'] != 'view') return;
		
		$get = $params['get'];
		
		$width = (isset($get['width']) ? $get['width'] : $this->width);
		$height = (isset($get['height']) ? $get['height'] : $this->height);
		
		if(isset($get['width']) && !isset($get['height'])) {
			$height = -1;
		}
		if(isset($get['height']) && !isset($get['width'])) {
			$width = -1;
		}			
		
		$path = wiki_ajax_data($get['path']);
		$wikiMedia = wiki_class_load('Media');
		$files = $wikiMedia->getList($path);
		
		$images = array();
		$wikiThumb = wiki_class_load('Thumb');

		foreach($files as $k=>$f) {
			if(!$f['img_width']) continue;
			list($w, $h) = $this->get_size($width, $height, $f['img_width'], $f['img_height']);
			$thumb = $wikiThumb->getMediaThumb($f['ns'], $f['source'], $w, $h, 90, $crop=true);
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
		
		$sort = (isset($get['sort']) ? $get['sort'] : 'date');
		if($sort == 'random') shuffle($images);
		else if(isset($images[0][$sort])) {
			if($sort == 'filesize') $sort = 'filesize_byte';
			$sort_function = (isset($get['reverse']) ? wiki_subval_asort : wiki_subval_sort);
			$images = $sort_function($images, $sort);
		}
		echo wiki_json_encode($images);
		exit;
	}

	/**
	 * 
	 * NarinMedia->getList 를 위한 이미지 필터
	 *
	 * @param array $fileinfo 파일정보 배열 ({@see NarinMedia})
	 * @return true|false 이미지이면 true, 아니면 false
	 */
	public function get_size($w, $h, $img_w, $img_h) {
		if($w > 0 && $h > 0) return array($w, $h);		
		if($w < 0) return array($h*$img_w/$img_h, $h);
		if($h < 0) return array($w, $w*$img_h/$img_w);
	}

}


?>