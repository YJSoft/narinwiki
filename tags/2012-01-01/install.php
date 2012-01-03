<?
/**
 *
 * 나린위키 설치 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
function debug($msg) {
	echo '=> ' . $msg . '<br/>';
}

$charset = 'utf-8';

$g4_path = $_POST['g4_path'];
$g4_url = $_POST['g4_url'];
$wiki_bo_table = $_POST['wiki_bo_table'];

unset($_POST['g4_path']);

$port = ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']);
$wiki_url = 'http://' . $_SERVER['HTTP_HOST'] . $port . dirname($_SERVER['REQUEST_URI']);

//print_r($_POST);
// 초기 설정
if(!$g4_path || !$g4_url) {
?>
<html>
<head>
	<title>나린위키 설치</title>
	<meta http-equiv="content-type" content="text/html; charset=<?=$charset?>">
	<link	rel="stylesheet" href="./css/narin.wiki.style.css" type="text/css">
	<style type="text/css">
	</style>
</head>
<body>	
<h1>나린위키 설치</h1>
<form action="./install.php" method="post">
<div class="list_table" width="500px" >
<table cellspacing="0" cellpadding="0" border="0">
<colgroup>
	<col width="120px">
	<col>
</colgroup>	
<tbody>
	<tr>
		<th>그누보드 경로</th>
		<td>
			<input type="text" name="g4_path" size="50"/>
			<div>
				나린위키 폴더로부터의 상대 경로를 입력합니다. <br/>
				e.g. g4/narinwiki 일 경우 .. 를 입력 <br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;그누보드와 같은 레벨의 다른 폴더일 경우 ../g4 입력
			</div>
		</td>
	</tr>
	<tr>
		<th>그누보드 URL</th>
		<td>
			<input type="text" name="g4_url" size="50" value="http://<?=$_SERVER['HTTP_HOST']?>"/>
			<div>
				'http://<?=$_SERVER['HTTP_HOST']?>/그누보드폴더' 와 같이 전체 URL을 입력하세요. <br/>
				그누보드가 루트폴더에 설치되어있다면 http://<?=$_SERVER['HTTP_HOST']?> 만 입력합니다.
			</div>
		</td>
	</tr>
	<tr>
		<th>게시판 id</th>
		<td>
			<input type="text" name="wiki_bo_table" size="50"/>
			<div>
				위키로 사용할 게시판 아이디(bo_table)를 입력하세요.
			</div>
		</td>
	</tr>	
	<tr>
		<th>&nbsp;</th>
		<td><span class="button"><input type="submit" value="설치시작"/></span></td>
	</tr>
</tbody>
</table>
</div>
</form>
</body>	
<?	
exit;
}

// 파라미터 검사
if(!file_exists($g4_path."/dbconfig.php")) {
?>
	<script type="text/javascript">
		alert('그누보드가 설치되어있지 않거나 경로가 올바르지 않습니다.');
		history.go(-1);
	</script>
<?
exit;
}

include_once $g4_path."/common.php";

// 관리자 권한 검사
if(!$is_admin) header("location:".$g4['bbs_path']."/login.php?url=".$_SERVER['PHP_SELF']);

// 설치되어있다면 패스~
if(file_exists("./narin.config.php")) {
	header("/");
	exit;
}

if($_POST['md'] == "doit") {

	$db_prefix = "byfun_";

	// UPDATE DB PROCESS //////////////////////////////////////////////////////////////////////////

	// FROM 2011-10-15
	$history_table = $db_prefix."narin_history";
	if(wiki_db_table_exists($history_table)) {
		$history_columns = wiki_db_table_columns($history_table);
		if(!$history_columns['ip_addr']) {
			$sql = "ALTER TABLE $history_table ADD COLUMN ip_addr VARCHAR(255) AFTER summary";
			sql_query($sql);
		}
	}

	// FROM 2011-11-27
	if(wiki_db_table_exists($history_table)) {
		$history_columns = wiki_db_table_columns($history_table);
		if(!$history_columns['doc']) {
			$sql = "ALTER TABLE $history_table ADD COLUMN doc VARCHAR(255) DEFAULT NULL AFTER reg_date";
			sql_query($sql);
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////

	$query =<<<EOF
	
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_cache` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(255) NOT NULL,
  `wr_id` int(10) unsigned NOT NULL,
  `content` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(20) NOT NULL,
  `wr_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `editor_mb_id` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `ip_addr` varchar(255) DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `doc` varchar(255) DEFAULT NULL,  
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_namespace` (
  `ns` varchar(255) NOT NULL,
  `bo_table` varchar(20) NOT NULL,
  `ns_access_level` tinyint(4) NOT NULL DEFAULT '1',
  `has_child` tinyint(1) NOT NULL DEFAULT '0',
  `tpl` text,
  PRIMARY KEY (`ns`,`bo_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_nsboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(20) NOT NULL,
  `wr_id` int(11) NOT NULL,
  `ns` varchar(255) NOT NULL,
  `access_level` tinyint(4) NOT NULL DEFAULT '1',
  `edit_level` tinyint(4) DEFAULT NULL,
  `should_update_cache` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_board_wr_id` (`bo_table`,`wr_id`),
  KEY `ns_id` (`ns`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_option` (
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(20) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `ip_addr` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(20) NOT NULL,
  `ns` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `img_width` int(11) NOT NULL,
  `img_height` smallint(6) NOT NULL,
  `img_type` tinyint(4) NOT NULL,
  `downloads` int(11) NOT NULL DEFAULT '0',
  `mb_id` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_media_namespace` (
  `ns` varchar(255) NOT NULL,
  `bo_table` varchar(20) NOT NULL,
  `ns_access_level` tinyint(4) NOT NULL DEFAULT '0',
  `ns_upload_level` tinyint(4) NOT NULL DEFAULT '2',
  `ns_mkdir_level` tinyint(4) NOT NULL DEFAULT '9',
  `has_child` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ns`,`bo_table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$db_prefix}narin_contributor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(20) NOT NULL,
  `wr_id` int(11) NOT NULL,
  `editor` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_item` (`bo_table`,`wr_id`,`editor`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

	  
EOF;

	$f = explode(";", $query);
	for ($i=0; $i<count($f); $i++) {
		if (trim($f[$i]) == "") continue;
		sql_query($f[$i], false);
	}



	// UPDATE DB PROCESS //////////////////////////////////////////////////////////////////////////

	$history_table = $db_prefix."narin_history";

	// FROM 2011-12-03
	sql_query("ALTER TABLE  $history_table ORDER BY `reg_date` DESC");
	
	// FROM 2011-12-10
	sql_query("ALTER TABLE ".$db_prefix."narin_cache MODIFY content MEDIUMTEXT"); 
	
	///////////////////////////////////////////////////////////////////////////////////////////////

	$wiki_fancy_url = file_exists('./.htaccess');

	$config_file = "<?\n";
	$config_file .= "unset(\$wiki);\n";
	$config_file .= "\$wiki['url'] = \"".$wiki_url."\";\n";
	$config_file .= "\$wiki['g4_path'] = \"".$g4['path']."\";\n";
	$config_file .= "\$wiki['g4_url'] = \"".$g4_url."\";\n";
	$config_file .= "\$wiki['bo_table'] = \"".$wiki_bo_table."\";\n";
	$config_file .= "\$wiki['front'] = \$board['bo_subject'];\n";
	$config_file .= "\$wiki['tree_top'] = \"Home\";\n";
	$config_file .= "\$wiki['write_table'] = \$g4['write_prefix'] . \$wiki['bo_table'];\n";
	$config_file .= "\$wiki['ns_table'] = \"{$db_prefix}narin_namespace\";\n";
	$config_file .= "\$wiki['history_table'] = \"{$db_prefix}narin_history\";\n";
	$config_file .= "\$wiki['nsboard_table'] = \"{$db_prefix}narin_nsboard\";\n";
	$config_file .= "\$wiki['option_table'] = \"{$db_prefix}narin_option\";\n";
	$config_file .= "\$wiki['cache_table'] = \"{$db_prefix}narin_cache\";\n";
	$config_file .= "\$wiki['changes_table'] = \"{$db_prefix}narin_changes\";\n";
	$config_file .= "\$wiki['media_table'] = \"{$db_prefix}narin_media\";\n";
	$config_file .= "\$wiki['media_ns_table'] = \"{$db_prefix}narin_media_namespace\";\n";
	$config_file .= "\$wiki['contrib_table'] = \"{$db_prefix}narin_contributor\";\n";
	$config_file .= "\$wiki['fancy_url'] = $wiki_fancy_url;\n";
	$config_file .= "\$bo_table = \$wiki['bo_table'];\n";
	$config_file .= "?>\n";

	$fp = @fopen("narin.config.php", "w");
	if(!$fp) {
		echo "<script>alert('나린위키 폴더에 쓰기 권한을 주세요'); history.go(-1);</script>";
		exit;
	}
	fwrite($fp, $config_file);
	fclose($fp);

	@copy("wiki.extend.php", $g4['path']."/extend/narin.wiki.extend.php");
	if(!file_exists($g4['path']."/extend/narin.wiki.extend.php")) {
		$add = "<b>narin.wiki.extend.php</b> 파일을 <u>g4/extend/</u> 에 복사해주세요";
	}
	
	?>
<html>
<head><title>나린위키 설치 완료</title></head>
<meta http-equiv="content-type" content="text/html; charset=<?=$charset?>">
<link rel="stylesheet" href="./css/narin.wiki.style.css" type="text/css"/>
<body>	

<h1>나린위키</h1>
<div style="line-height: 160%;">축하합니다!. <b>나린위키</b>를 설치하였습니다.

<h3>나린위키 설정</h3>
나린위키를 사용하기 위해서 다음의 설정을 해야 합니다.
<ul>
	<li>그누보드의 <b>head.sub.php</b> 의 <u>&lt;body&gt; 태그위</u>에 다음 코드를 넣어주세요 <pre
		style="border: 1px dashed #ccc; padding: 5px; background-color: #F0F4F7;"><span
		style="color: #000000; font-weight: bold;">&lt;?</span> <span
		style="color: #b1b100;">if</span><span style="color: #009900;">&#40;</span><a
		href="http://www.php.net/defined"><span style="color: #990000;">defined</span></a><span
		style="color: #009900;">&#40;</span><span style="color: #0000ff;">&quot;__NARINWIKI__&quot;</span><span
		style="color: #009900;">&#41;</span><span style="color: #009900;">&#41;</span> <span
		style="color: #b1b100;">include_once</span> <span
		style="color: #000088;">$wiki</span><span style="color: #009900;">&#91;</span>path<span
		style="color: #009900;">&#93;</span><span style="color: #339933;">.</span><span
		style="color: #0000ff;">&quot;/inc/inc.head.sub.php&quot;</span><span
		style="color: #F0F4F7;">;</span> <span
		style="color: #000000; font-weight: bold;">?&gt;</span></pre></li>
		<? if($add) echo "<li>$add</li>";	?>
	<li>위키로 사용할 게시판의 bo_1 필드의 제목을 'narinwiki', 내용을 그누보드 설치 폴더부터 나린위키 설치 폴더로의 상대 경로를 입력하세요. (e.g. 그누보드가 /home, 나린위키가 /narinwiki 에 설치되었다면, ../narinwiki 입력)</li>		
	<li>나린위키 URL은 <a href="<?=$wiki_url?>/"><?=$wiki_url?>/</a> 입니다.</li>
</ul>

<h3>나린위키 기부</h3>
<div style="">나린위키는 무료 오픈소스 소프트웨어입니다. 누구나 무료로 다운받을 수 있고 사용할 수 있습니다. <br />

나린위키를 제작, 유지보수, 업데이트 하는데 많은 시간과 노력이 필요합니다. 기업/기관에서 나린위키를 사용하신다면 더 좋은
소프트웨어를 위해 기부를 생각해보세요. 개인도 환영입니다. ^^ <br />

기부는 온라인 결제 사이트인 페이팔을 이용해주세요 : <a href="http://narinwiki.org/donation">기부하기</a><br />

무통장입금으로 해주실분은 <a href="http://narinwiki.org/donation_by_transfer">이곳</a>에서 계좌
정보를 확인해주세요. <br />
<br />
감사합니다. <br />

</div>

<h3>나린위키 알아보기</h3>
<ul>
	<li><a href="http://narinwiki.org">나린위키 홈페이지</a></li>
	<li><a href="http://narinwiki.org/manual">나린위키 사용자 매뉴얼</a></li>
	<li><a href="http://narinwiki.org/syntax">나린위키 위키문법</a></li>
	<li><a href="http://narinwiki.org/license">나린위키 저작권</a></li>
</ul>



		<?
		exit;
}

?>
<html>
<head><title>나린위키 설치</title></head>
<meta http-equiv="content-type" content="text/html; charset=<?=$charset?>">
<link rel="stylesheet" href="./css/narin.wiki.style.css" type="text/css"/>
<body>
<h1>나린위키 설치</h1>
<form name="frmwikiinstall" action="install.php"
	onsubmit="return submit_check(this);" method="post"><input
	type="hidden" name="md" value="doit" />
<input type="hidden" name="g4_path" value="<?=$g4['path']?>"/>
<input type="hidden" name="g4_url" value="<?=$g4_url?>"/>
<input type="hidden" name="wiki_bo_table" value="<?=$wiki_bo_table?>"/>
<div class="list_table">
<table cellpadding="0" cellspacing="0" border="0">
<colgroup>
	<col width="100px">
	<col>
</colgroup>
	<tbody>
		<tr>
			<th scope="row">저작권 동의</th>
			<td><textarea
				style="width: 100%; height: 200px; padding: 5px 2px; margin-bottom: 10px; background-color: #f0f0f0;"
				readonly><?=file_get_contents("LICENSE")?></textarea>

			<p>설치를 원하시면 위 내용에 동의하셔야 합니다.</p>
			<input type="checkbox" name="agree" id="agree"><label for="agree"> 네,
			동의합니다.</label></td>
		</tr>
		<tr>
			<td colspan="2"><span class="button red"><input type="submit"
				value=" 설치하기 " /></span></td>
		</tr>
	</tbody>
</table>

</div>
</form>

<script type="text/javascript">

function submit_check(f) {
	if(!$("#agree").is(":checked")) {
		alert("설치하시려면 저작권 동의에 체크하셔야 합니다.");
		return false;
	}
	return true;
}
	
</script>
</body>
</html>
 <?

/**
 * 
 * DB 테이블이 존재하는가?
 * 
 * @param string $table 테이블명
 * @return true|false 테이블이 있으면 true, 없으면 false
 */
function wiki_db_table_exists ($table) {
	global $mysql_db;
	$tables = mysql_list_tables ($mysql_db);
	while (list ($temp) = mysql_fetch_array ($tables)) {
		if ($temp == $table) {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * 
 * 테이블 칼럼 목록 반환
 * 
 * @param string $table_name 테이블명
 * @return array 칼럼 목록
 */
function wiki_db_table_columns($table_name) {
	$list = array();
	$res = sql_query("SHOW COLUMNS FROM $table_name");
	while ($row = sql_fetch_array($res)) {
		$list[$row['Field']] = $row;
	}
	return $list;
}

/**
 * 
 * 절대경로를 상대 경로로 변환
 * 
 * @param string $from 시작 경로
 * @param string $to 도착 경로 
 * @return string $from 에서 $to 로의 상대 경로
 */

function wiki_relative_path($from, $to)
{
	$from     = explode('/', realpath($from));
	$to       = explode('/', realpath($to));
	$relPath  = $to;
	
	foreach($from as $depth => $dir) {
		if($dir === $to[$depth]) {
			array_shift($relPath);
		} else {
			$remaining = count($from) - $depth;
			if($remaining > 1) {
				$padLength = (count($relPath) + $remaining - 1) * -1;
				$relPath = array_pad($relPath, $padLength, '..');
				break;
			} else {
				$relPath[0] = './' . $relPath[0];
			}
		}
	}
	return implode('/', $relPath);
}
?>
