<?
if (!defined('_GNUBOARD_')) exit;

require_once $wiki[path]."/lib/Thumb/easyphpthumbnail.class.php";

class NarinThumb extends NarinClass {
	
	var $thumb_path;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		parent::__construct();
		$this->thumb_path = $this->wiki[path] . "/data/".$this->wiki[bo_table]."/thumb";
	}

	 /**
	 * 썸네일 삭제
	 * @params $namePrefix : 썸네일명 형식 => "$namePrefix_너비x높이"
	 */
	function deleteThumb($namePrefix) {
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
	  * 오래된 썸네일 삭제
	  * @params $bo_clean_field : 마지막 클린 시간 저장 필드 (기본값 bo_9)
	  * @params $expire_time : 단위 초 (기본값 3일)
	  * @params $trigger_time : 단위 초 (기본값 6시간)
	  */
	function clearThumb($expire_time = 259200, $trigger_time = 21600)
	{
		$wikiConfig = wiki_class_load("Config");
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
	  * 오래된 파일 삭제
	  * @params $path : 대상 디렉토리 경로
	  * @params $life : 기준 시간 (단위 : 초)
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
	  * 문자열($haystack)이 주어진 문자($needle)로 시작하는지 여부 확인
	  * @params $string 문자열
	  * @params $startString 찾을 문자열
	  */
	function _startsWith($string, $startString)
	{
	    $length = strlen($startString);
	    return (substr($string, 0, $length) == $startString);
	}
		

	/**
	 * 게시판 썸네일 생성 및 패스 리턴
	 */
	function getBoardThumb($wr_id, $thumb_width, $thumb_height, $img_idx=0, $quality=90, $use_crop=-1) {
		
		// 오래된 썸네일 삭제
		if($bo_table) $this->clearThumb();
				
		$bo_table = $this->wiki[bo_table];
		$write_table = $this->g4[write_prefix] . $bo_table;
		
		$wr = sql_fetch("SELECT * FROM $write_table WHERE wr_id = $wr_id");
		if(!$wr) return;
		
		// 파일 정보 로드
		$fileinfo = get_file($bo_table, $wr_id);
			
		// 원본 이미지 파일 경로
		$file = $fileinfo[$img_idx][path] . '/' .$fileinfo[$img_idx][file];
		
		$pathinfo = pathinfo($file);
		$extension = $pathinfo[extension];
		
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
		$thumbLib->Quality = 100;
		$thumbLib->Thumblocation = $this->thumb_path."/";
		$thumbLib->Thumbfilename = $thumb_name;
		$thumbLib->Createthumb($file, 'file');
					
		return $thumb_file;
	}
	
}

?>