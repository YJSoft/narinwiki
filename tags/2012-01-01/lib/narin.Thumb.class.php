<?
/**
 *
 * 썸네일 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */


// easy php thumbnail 클래스를 이용함
include_once WIKI_PATH."/lib/Thumb/easyphpthumbnail.class.php";

/**
 *
 * 썸네일 클래스
 *
 * 미디어/첨부이미지의 썸네일을 생성, 삭제하는 클래스
 * 
 * <b>사용 예제</b>
 * <code>
 * // 클래스 로딩
 * $wikiThumb =& wiki_class_load("Thumb");
 * 
 * // 미디어관리자에 등록된 "/images/example/bag.jpg" 파일의 썸네일을
 * // 너비 300px, 높이 120px, 품질 90%의 썸네일로 만들고
 * // 썸네일 패스 가져오기
 * $thumb_path = $wikiThumb->getMediaThumb("/images/example", "bag.jpg", 300, 120, 90);
 * 
 * </code>
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinThumb extends NarinClass {

	/**
	 *
	 * @var string 썸네일 저장 폴더
	 */
	var $thumb_path;
	
	/**
	 *
	 * @var string 썸네일 URL
	 */
	var $thumb_url;	

	/**
	 * 생성자
	 */
	public function __construct() {

		parent::__construct();
		$this->thumb_path = WIKI_PATH."/data/".$this->wiki['bo_table']."/thumb";
		$this->thumb_url = $this->wiki['url']."/data/".$this->wiki['bo_table']."/thumb";
	}

	/**
	 *
	 * 썸네일 삭제
	 *
	 * @param string $namePrefix 썸네일명 형식 => "$namePrefix_너비x높이"
	 */
	function deleteThumb($namePrefix) {
		if(!file_exists($this->thumb_path)) return;
		if ($handle = opendir($this->thumb_path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if($this->_startsWith($file, $namePrefix)) {
						@unlink($this->thumb_path."/". $file);
					}
				}
			}
			closedir($handle);
		}
	}

	/**
	 *
	 * 오래된 썸네일 삭제
	 *
	 * @deprecated
	 * @param $expire_time 설정된 값보다 오래된 파일 삭제 - 단위 초 (기본값 3일)
	 * @param $trigger_time 실행 주기 - 단위 초 (기본값 6시간)
	 */
	function clearThumb($expire_time = 259200, $trigger_time = 21600)
	{
		$wikiConfig =& wiki_class_load("Config");
		$last_clean_time = $wikiConfig->thumb_clean_time;

		if(empty($last_clean_time)) $last_clean_time = 0;
		$ctime = time();

		// $trigger_time 마다 썸네일 클리어
		if($ctime - $last_clean_time > $trigger_time) {
			// $expire_time 이전에 생성된 썸네일은 삭제
			$this->_clearImageCache($this->thumb_path, $expire_time);
			$wikiConfig->update("/thumb_clean_time", $ctime);
			sql_query($sql);
		}
	}

	/**
	 *
	 * 실제로 오래된 썸네일 삭제
	 *
	 * @deprecated
	 * @param string $path 대상 디렉토리 경로
	 * @param int $life 기준 시간 (단위 : 초)
	 * @param boolean $dbg 디버그만 할 경우 사용
	 */
	function _clearImageCache($path, $life, $dbg = false)
	{
		// delete tmp image
		if ($handle = @opendir($path)) {
			while (false !== ($file = @readdir($handle))) {
				if ($file != "." && $file != "..") {
					$filemtime = @filemtime($path ."/". $file);  // returns FALSE if file does not exis
					$del = "";
					$ctime = time();
					$dur = $ctime - $filemtime;
					if (!$filemtime or ($dur >= $life)){
						$del = "should be deleted";
						if(!$dbg) @unlink($path ."/". $file);
					} else $del = "it's fresh!";
					if($dbg) echo ">> $file ($life, $ctime, $filemtime, $dur, $del)<br/>";
				}
			}
			closedir($handle);
		}
	}

	/**
	 *
	 * 문자열($haystack)이 주어진 문자($needle)로 시작하는지 여부 확인
	 *
	 * @param $string 문자열
	 * @param $startString 찾을 문자열
	 * @return true|false 문자열 시작이 일치하면 true, 아니면 false
	 */
	function _startsWith($string, $startString)
	{
		$length = strlen($startString);
		return (substr($string, 0, $length) == $startString);
	}

	/**
	 *
	 * 게시판 썸네일 생성 및 패스 리턴
	 *
	 * @param int $wr_id 문서 id
	 * @param int $thumb_width 생성할 썸네일 너비
	 * @param int $thumb_height 생성할 썸네일 높이
	 * @param int $img_idx 생성할 이미지 index (첨부파일 index)
	 * @param int $quality 이미지 퀄리티
	 * @param boolean $use_crop 크롭기능 사용할지
	 * @return string 썸네일 경로
	 */
	function getBoardThumb($wr_id, $thumb_width, $thumb_height, $img_idx=0, $quality=90, $use_crop=-1) {
			
		$bo_table = $this->wiki['bo_table'];
		$write_table = $this->g4[write_prefix] . $bo_table;

		$wr = sql_fetch("SELECT * FROM $write_table WHERE wr_id = $wr_id");
		if(!$wr) return;

		// 파일 정보 로드
		$fileinfo = get_file($bo_table, $wr_id);
			
		// 원본 이미지 파일 경로
		$file = $fileinfo[$img_idx]['path'] . '/' .$fileinfo[$img_idx]['file'];
		
		$pathinfo = pathinfo($file);
		$extension = strtolower($pathinfo[extension]);

		// 확장자 검사 : 가능 이미지 아니면 원본 이미지 경로 반환
		if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'png' && $extension != 'gif') {
			return $file;
		}

		// 원본 이미지 파일명
		$filename = $fileinfo[$idx][source];
			
		// 썸네일 파일 패스  (썸네일 파일 명 : 게시판아이디-파일인덱스-퀄리티)
		$thumb_name = $bo_table."-".$wr_id . "-" . $img_idx . "-". $quality ."_".$thumb_width."x".$thumb_height. "." . $extension;
		$thumb_file = $this->thumb_path."/".$thumb_name;

		// 썸네일이 이미 존재하면 리턴
		if(file_exists($thumb_file)) return $thumb_file;
			
		$thumbLib = new easyphpthumbnail;
		$thumbLib->Thumbwidth = $thumb_width;
		$thumbLib->Thumbheight = $thumb_height;
		$thumbLib->Quality = $quality;
		$thumbLib->Thumblocation = $this->thumb_path."/";
		$thumbLib->Thumbfilename = $thumb_name;
		$thumbLib->Createthumb($file, 'file');
			
		return $thumb_file;
	}

	/**
	 *
	 * 미디어 썸네일 생성 및 패스 리턴
	 *
	 * @param string $ns 미디어 폴더 경로
	 * @param string $filename 미디어 파일명
	 * @param int $thumb_width 생성할 썸네일 너비
	 * @param int $thumb_height 생성할 썸네일 높이
	 * @param int $quality 썸네일 품질
	 * @return string 썸네일 경로
	 */
	function getMediaThumb($ns, $filename, $thumb_width, $thumb_height, $quality=90, $crop = false) {
			
		$bo_table = $this->wiki['bo_table'];
		$write_table = $this->g4['write_prefix'] . $bo_table;

		// 파일 정보 로드
		$media =& wiki_class_load("Media");
		$fileinfo = $media->getFile($ns, $filename);
		if(!$fileinfo) {
			return;
		}
			
		// 원본 이미지 파일 경로
		$file = $fileinfo['path'];
		
		$pathinfo = pathinfo($file);
		$extension = strtolower($pathinfo['extension']);

		// 확장자 검사 : 가능 이미지 아니면 원본 이미지 경로 반환
		if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'png' && $extension != 'gif') {
			return $this->wiki['url'] . "/data/".$this->bo_table."/files/".$fileinfo['source'];
		}

		// 원본 이미지 파일명
		$filename = $fileinfo['source'];
			
		// 썸네일 파일 패스  (썸네일 파일 명 : 게시판아이디-파일인덱스-퀄리티)
		$croping = ($crop ? "-c" : "");
		$thumb_name = "media-".$bo_table."-".$fileinfo['id'] . "-". $quality . $croping . "_".$thumb_width."x".$thumb_height. "." . $extension;
		$thumb_file = $this->thumb_path."/".$thumb_name;
		
		// 썸네일이 이미 존재하면 리턴
		if(file_exists($thumb_file)) return $this->thumb_url . "/". $thumb_name;
			
			
		if($crop) {						
			$sizes = getimagesize($file);			
	    $source_width = $sizes[0];
	    $source_height = $sizes[1];					
			$top = $left = $right = $bottom = 0;				
			
			// 원본 가로가 길 경우
	    if (($source_width / $source_height) > ($thumb_width / $thumb_height)) {
	        $temp_width = $source_height * $thumb_width / $thumb_height;
	        $right = $left = ($source_width - $temp_width) / 2;
	    }			
			// 원본 세로가 길 경우
	    if (($source_width / $source_height) < ($thumb_width / $thumb_height)) {
	        $temp_height = $source_width * $thumb_height / $thumb_width;
	        $top = $bottom = ($source_height - $temp_height) / 2;
	    }
		}

		$thumbLib = new easyphpthumbnail;
		$thumbLib->Thumbwidth = $thumb_width;
		$thumbLib->Thumbheight = $thumb_height;
		$thumbLib->Quality = $quality;
		$thumbLib->Thumblocation = $this->thumb_path."/";
		$thumbLib->Thumbfilename = $thumb_name;
		if($crop) {
			$thumbLib->Cropimage = array(1, 1, $left, $right, $top, $bottom);
		}

		$thumbLib->Createthumb($file, 'file');
			
		return $this->thumb_url . "/".$thumb_name;
	}

}

?>
