<?

/*
+ 예제 사용법
  - 폴더구조 
    g4/example.php
    g4/narin.lib.class.php
    g4/wiki
    g4/skin/latest/narin_basic/latest.skin.php
  - 위키로 사용하는 bo_table : 'wiki'  
*/

// 그누보드의 _common.php include
include_once "_common.php";

// 나린위키 도움 라이브러리 클래스 include
include_once "narinwiki.lib.php";


// 나린위키 최근 업데이트 문서
// $options = array("new_day"=>2); 2일 안에 업데이트 된 글을 굵게 표시
echo narin_latest($skin_dir="narin_basic", $wiki_path="./wiki", $bo_table="wiki", $rows=5, $subject_len=40, $options=array("new_day"=>2));

// 나린위키 라이브러리 객체 생성
$narinLib = new NarinWikiLib($wiki_path="./wiki", $bo_table="wiki");

// 폴더내의 폴더/문서 목록 얻기
// $withArticle = false 라면, 폴더 목록만
// $withArticle = true 라면, 폴더와 파일목록
$list = $narinLib->folderList($folder="/", $withArticle=true);
print_r2($list);	

// 최근 업데이트된 문서 목록 5개 가져오기
$list = $narinLib->recentUpdate($count=5);
print_r2($list);

// 최근 변경내역 목록 5개 가져오기
$list = $narinLib->recentChanges($count=5);
print_r2($list);

?>