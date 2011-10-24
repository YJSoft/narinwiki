<?

if(!file_exists($wiki_path."/narin.config.php")) {
	header("location:".$wiki_path."/install.php");
	exit;
}

if ($_GET['wiki_path'] || $_POST['wiki_path'] || $_COOKIE['wiki_path']) {
    unset($_GET['wiki_path']);
    unset($_POST['wiki_path']);
    unset($_COOKIE['wiki_path']);
    unset($wiki_path);
}

if (!$wiki_path || preg_match("/:\/\//", $wiki_path))
    die("<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'><script type='text/javascript'> alert('잘못된 방법으로 변수가 정의되었습니다.'); </script>");

$g4_path = $wiki_path . "/..";

include_once $g4_path."/common.php";

include_once $wiki_path ."/narin.config.php";
include_once $wiki_path ."/lib/narin.Class.class.php";
include_once $wiki_path ."/lib/narin.wiki.lib.php";
include_once $wiki_path."/lib/narin.Plugin.class.php";

//$doc = wiki_url_data($doc);
//$loc = wiki_url_data($loc);

if($loc && $doc) {
	$doc = $loc."/".$doc;
}

if(!$doc) $doc = "/".$wiki[front];


$doc = preg_replace('/\/+/', '/', $doc);

list($ns, $docname, $doc) = wiki_validate_doc($doc);

if(!$board || $board[bo_1_subj] != "narinwiki" || $wiki[path] != $g4[path]."/".$board[bo_1]) {
	echo "<script type='text/javascript'>alert('존재하지 않는 위키입니다.'); location.href='{$g4[path]}';</script>";
	exit;
}

?>