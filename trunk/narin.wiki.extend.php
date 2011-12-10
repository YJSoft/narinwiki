<?
/**
 * 
 * 그누보드용 확장 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
/* 현지 실행 스크립트 파일 명 */
$scriptFile = basename($_SERVER['SCRIPT_NAME']);

/* 위키 사용중인 게시판인 경우 */
if($board['bo_1_subj'] == "narinwiki" && $board['bo_1'] != "") {
	
	if($url) {
		header("location:$url");
	}
	
	$wiki_path = $g4['path'] . "/" . $board['bo_1'];
	$wiki_config = $wiki_path."/narin.config.php";

	// 위키 설정 & 라이브러리 로드
	if(file_exists($wiki_config)) {
		
		define("__NARINWIKI__", TRUE);
		
		include_once $wiki_config;	
		include_once $wiki_path . "/lib/narin.wiki.lib.php";	
		include_once $wiki_path ."/lib/narin.Class.class.php";
		
		$wikiControl = wiki_class_load("Control");
		
		// 스킨 경로 변경
		$board_skin_path = $wiki['inc_skin_path'];
		
		// 게시판 스킨 & 헤더-테일 변경
		$board[bo_include_head] = $wiki['path'] . "/head.php";
		$board[bo_include_tail] = $wiki['path'] . "/tail.php";				
		
		// 위키를 전체 검색에 노출 안되도록 함
		// 위키 자체 권한, 파싱 문제 등...
		$board[bo_use_search] = 0;
				
		$wikiControl->board($scriptFile);		
						
	} // if wiki_config
}	

?>
