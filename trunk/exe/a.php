<?
error_reporting(E_ALL);
ini_set('display_errors', '1');
define("__NARIN_API__", true);

/**
 * ajax 실행 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
include_once "./_common.php";

//wiki_only_ajax();

@extract(wiki_unescape($_POST));
@extract(wiki_unescape($_GET));

$inc_file = "./response/".$w.".php";
if(!$w || !file_exists($inc_file)) {
	wiki_not_found_page();
}

include_once $inc_file;
exit;


function wiki_ajax_error() {
	echo wiki_json_encode(array('code'=>-1, 'msg'=>'파라미터 오류'));
	exit;
}



// 문서 검색 (by toolbar)

if($w == "find_doc" && $find_doc) {
	
	if(wiki_is_euckr()) $find_doc = iconv("UTF-8", "CP949", rawurldecode($find_doc)); 
	
	$sql = "SELECT * FROM $wiki[write_table] AS wt LEFT JOIN $wiki[nsboard_table] AS nt ON nt.bo_table = '$wiki[bo_table]' AND wt.wr_id = nt.wr_id WHERE nt.ns <> '' AND wt.wr_subject LIKE '%$find_doc%'";
	$result = sql_list($sql);
	$list = array();
	foreach($result as $idx => $v) {
		array_push($list, array("folder"=>$v[ns], "docname"=>$v[wr_subject]));
	}

	if(wiki_is_euckr()) wiki_ajax_data($list);
	
	echo json_encode($list);
	exit;
}


// 플러그인 명령 실행
if($w == "plugin" && $p && $m) {
	$wikiEvent->trigger("AJAX_CALL", array("plugin"=>$p, "method"=>$m, "get"=>$_GET, "post"=>$_POST));
}


// 임시 저장 (쓰기)
if($w == "tmpsave_write" && $member[mb_id] && $wr_doc && $wr_content) {
	$id = md5($member[mb_id]."_".stripcslashes($wr_doc));
	$reg = "tmpsave/$id";	
	wiki_set_option($reg, array("wr_content", "wr_date"), array(stripcslashes($wr_content), date("Y-m-d h:i:s")));
	echo 1;	
	exit;
}

// 임시 저장 (삭제)
if($w == "tmpsave_delete" && $member[mb_id] && $wr_doc) {
	$id = md5($member[mb_id]."_".stripcslashes($wr_doc));
	$reg = "tmpsave/$id";	
	wiki_set_option($reg, null, null);
	echo 1;	
	exit;
}

// 임시 저장 (읽기)
if($w == "tmpsave_read" && $member[mb_id] && $wr_doc) {
	$id = md5($member[mb_id]."_".stripcslashes($wr_doc));
	$reg = "tmpsave/$id";	
	$tmp_saved = wiki_get_option($reg);	
	$ret = array();	
	if($tmp_saved) {
		$ret[code] = 1;
		$ret[wr_date] = $tmp_saved[wr_date];
		$ret[wr_content] = $tmp_saved[wr_content];
	} else {
		$ret[code] = -1;
	}
	
	if(wiki_is_euckr()) wiki_ajax_data($ret);	
	echo json_encode($ret);	
			
	exit;
}

if($w == 'media_reg' && $loc) {
	$media = wiki_class_load("Media");
	$thumb = wiki_class_load("Thumb");
	
	$ns = $media->getNS(stripcslashes($loc));		
	if($ns['ns_access_level'] > $member['mb_level'] || $ns['ns_upload_level'] > $member['mb_level']) {
		echo "권한이 없습니다.";
		exit;
	}
	
	$loc = stripcslashes($loc);
	$source = stripcslashes($source);
	$file = stripcslashes($file);
	
	$media->addFile($loc, $source, $file);
	
	$thumb_width = 30;
	$thumb_height = 30;
	$f = $media->getFile($loc, $source);	
	if($f['img_width']) {
		$thumb_path = $thumb->getMediaThumb($ns=$loc, $filename=$f['source'], $thumb_width, $thumb_height, $quality=90);
		$f['thumb'] = $thumb_path;
	} else $f['thumb'] = "";
	
	preg_match("/\.([a-zA-Z0-9]{2,4})$/", $f['source'], $m);
	if($m[1] && file_exists($wiki['path'].'/imgs/media_manager/ext/'.$m[1].'.png')) {		
		$f['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/'.$m[1].'.png';			
	} else $f['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/_blank.png';
	
	$f['code'] = 1;
	$f['filesize'] = wiki_file_size($f['filesize']);
	
	$json = json_encode($f);
		
	wiki_set_option("uploading", $file, null);
	
	// uploading 이 기록된 시간이 6시간 이전이면..
	// (6시간 이전에 파일 올리다 중단된 것이면)
	// 삭제
	$ctime = time();
	$expire = 6*60*60; // 6시간
	$not_completed_files = wiki_get_option("uploading");
	if(!empty($not_completed_files)) {
		foreach($not_completed_files as $file => $timestamp) {	
			if($ctime - $timestamp > $expire) {		
				$deleted = $media->deleteUnusedFile($file);
				unset($not_completed_files[$file]);
			}
		}		
		$uploading_files = array();
		$uploading_times = array();
		foreach($not_completed_files as $file => $timestamp) {
			array_push($uploading_files, $file);
			array_push($uploading_times, $timestamp);
		}	
		wiki_set_option("uploading", $uploading_files, $uploading_times);
	} else {
		wiki_set_option("uploading", null, null);
	}
	
	echo $json;
	exit;
}


// 미디어 목록
if($w == "media_list" && $loc) {
	$loc = stripcslashes($loc);
	$media = wiki_class_load("Media");
	$ns = $media->getNS($loc);
	
	// 권한 검사
	if($ns['ns_access_level'] > $member['mb_level']) {
		$ret = array('code'=>'-1', 'msg'=>'권한 없음');
		echo json_encode($ret);
		exit;
	}
	
	$thumb = wiki_class_load("Thumb");
	$thumb_width = 30;
	$thumb_height = 30;	
	$files = $media->getList($loc);
	foreach($files as $k=>$file) {
		if($file['img_width']) {
			$thumb_path = $thumb->getMediaThumb($loc, $filename=$file['source'], $thumb_width, $thumb_height, $quality=90);
			$files[$k]['thumb'] = $thumb_path;
		} else $files[$k]['thumb'] = "";
		preg_match("/\.([a-zA-Z0-9]{2,4})$/", $file['source'], $m);
		if($m[1] && file_exists($wiki['path'].'/imgs/media_manager/ext/'.$m[1].'.png')) {		
			$files[$k]['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/'.$m[1].'.png';			
		} else $files[$k]['ext_icon'] = $wiki['path'].'/imgs/media_manager/ext/_blank.png';
		$files[$k]['filesize'] = wiki_file_size($file['filesize']);
	}
	
	$ploc = wiki_get_parent_path($loc);
	$pNS = $media->getNS($ploc);
	
	$ret = array('code'=>1, 'files'=>$files, 'parent_mkdir_level'=>$pNS['ns_mkdir_level'], 'mkdir_level'=>$ns['ns_mkdir_level'], 'upload_level'=>$ns['ns_upload_level'], 'access_level'=>$ns['ns_access_level']);
	if(wiki_is_euckr()) wiki_ajax_data($ret);	
	echo json_encode($ret);	
	exit;
}

// 미디어 삭제
if($w == "media_delete" && $loc && $file) {
	
	$loc = stripcslashes($loc);
	$file = stripcslashes($file);	
	
	$media = wiki_class_load("Media");	
	$file_info = $media->getFile($loc, $file);
	
	if(!$file_info) {
		$ret = array('code'=>'-1', 'msg'=>'파일 정보가 없습니다.');
		echo json_encode($ret);
		exit;		
	}
	
	// 권한 검사
	if($file_info['mb_id'] != $member['mb_id'] && !$is_wiki_admin) {
		$ret = array('code'=>'-1', 'msg'=>'권한이 없습니다.');
		echo json_encode($ret);
		exit;
	}
	
	$media->deleteFile($loc, $file);
	echo json_encode(array('code'=>1));
	exit;
}

// 트리 HTML
if($w == "get_tree" && $loc) {	
	$media = wiki_class_load("Media");
	$ns = $media->getNS(stripslashes($loc));
	if(!$ns && $loc == '/') $media->addNamespace('/');
	else if(!$ns) echo "";
	echo $media->get_tree("/", stripslashes($loc));	
	exit;
}

// 미디어 폴더 생성
if($w == "media_mkdir" && $ploc && $loc) {
	$media = wiki_class_load("Media");
	$parent = $media->getNS(stripslashes($ploc));
	if(!$parent && $ploc == '/') {
		$media->addNamespace('/');
	} else if(!$parent || $parent['ns_mkdir_level'] > $member['mb_level']) {
		$ret = array('code'=>'-1', 'msg'=>'권한이 없습니다.');
		echo json_encode($ret);
		exit;		
	}
	$loc = stripslashes($loc);
	if(!wiki_check_folder_name($loc)) {
		$ret = array('code'=>'-1', 'msg'=>'폴더명 형식이 잘못되었습니다');
		echo json_encode($ret);
		exit;
	}
	
	$media->addNamespace($loc, $parent);
	echo json_encode(array('code'=>1));
}

// 미디어 폴더 삭제
if($w == "media_rmdir" && $loc) {
	if($loc == '/') {
		$ret = array('code'=>'-1', 'msg'=>'루트 폴더는 삭제할 수 없습니다.');
		echo json_encode($ret);
		exit;					
	}
	$media = wiki_class_load("Media");
	$loc = stripslashes($loc);	
	$folder = $media->getNS($loc);
	if($folder['ns_mkdir_level'] > $member['mb_level']) {
		$ret = array('code'=>'-1', 'msg'=>'권한이 없습니다.');
		echo json_encode($ret);
		exit;			
	}
	$success = $media->deleteFolder($loc);
	if($success) {
		echo json_encode(array('code'=>1, 'updir'=>wiki_get_parent_path($loc)));
	}
	else {
		$ret = array('code'=>'-1', 'msg'=>'빈폴더가 아닙니다.');
		echo json_encode($ret);		
	}
	exit;
}

// 미디어 : 폴더 권한 변경
if($w == "media_chmod" && $loc && $is_wiki_admin) {
	$media = wiki_class_load("Media");
	$media->updateLevel(stripcslashes($loc), $access_level, $upload_level, $mkdir_level);
	$ret = array('code'=>'1', 'access_level'=>$access_level, 'upload_level'=>$upload_level, 'mkdir_level'=>$mkdir_level);
	echo json_encode($ret);
	exit;
}

// Not used ////////////////////////////////////////
// 폴더 목록 
if($w == "folderlist") {
	$wikiNS = wiki_class_load("Namespace");
	$folders = $wikiNS->namespaces("/", $withArticle=false);
	
	$json = array();
	foreach($folders as $vp => $rp)
	{
		array_push($json, array("display"=>$vp, "path"=>$rp));
	}
	echo json_encode($json);
	exit;
}


function wiki_ajax_data(&$arr) {
	if(!is_array($arr)) {
		$arr = iconv("CP949", "UTF-8", $arr);
		return;
	}
	foreach($arr as $k => $v) {
		wiki_ajax_data($arr[$k]);
	}
}

?>