<?
/**
 *
 * 나린위키 설치 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$g4_path = "..";
include_once($g4_path."/common.php");

if(!$is_admin) header("location:".$g4['bbs_path']."/login.php");
if(file_exists("./narin.config.php")) {
	header("location:.");
	exit;
}

if($_POST['md'] == "doit") {
	$wiki_path = trim($wiki_path);
	if(!$wiki_path) {
		alert("설치할 수 없습니다!!");
		exit;
	}

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
  `content` text NOT NULL,
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

	///////////////////////////////////////////////////////////////////////////////////////////////

	$config_file = "<?\n";
	$config_file .= "unset(\$wiki);\n";
	$config_file .= "\$wiki['path'] = \$g4['path'] . \"/$wiki_path\";\n";
	$config_file .= "\$wiki['skin_path'] = \$wiki['path'] . \"/skin/board/basic\";\n";
	$config_file .= "\$wiki['bo_table'] = \$bo_table;\n";
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
	$config_file .= "?>\n";

	$fp = @fopen("narin.config.php", "w");
	if(!$fp) {
		$s_wiki_path = addslashes($wiki_path);
		echo "<script>alert('$s_wiki_path 에 쓰기 권한을 주세요'); history.go(-1);</script>";
		exit;
	}
	fwrite($fp, $config_file);
	fclose($fp);

	@copy("wiki.extend.php", $g4['path']."/extend/narin.wiki.extend.php");
	if(!file_exists($g4['path']."/extend/narin.wiki.extend.php")) {
		$add = "<b>narin.wiki.extend.php</b> 파일을 <u>g4/extend/</u> 에 복사해주세요";
	}
	include_once $g4['path']."/head.php";
	?>
<link
	rel="stylesheet" href="./css/narin.wiki.style.css" type="text/css">

<h1>나린위키</h1>
<div style="line-height: 160%;">축하합니다!. <b>나린위키</b>를 설치하였습니다.

<h3>나린위키 설정</h3>
위키를 사용하기 위해서 다음의 설정을 해야 합니다.
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
	<li>위키로 사용할 게시판의 여분필드 1의 제목을 <u>narinwiki</u> 로 설정하고, 여분필드 1의 내용에 <u><?=$wiki_path?></u>
	를 입력하세요</li>
	<li>"http://나린위키/narin.php?bo_table=게시판아이디" 로 나린위키 링크를 만들어 사용하세요</li>
</ul>

<h3>나린위키 기부</h3>
<div style="">나린위키는 무료 오픈소스 소프트웨어입니다. 누구나 무료로 다운받을 수 있고 사용할 수 있습니다. <br />

나린위키를 제작, 유지보수, 업데이트 하는데 많은 시간과 노력이 필요합니다. 기업/기관에서 나린위키를 사용하신다면 더 좋은
소프트웨어를 위해 기부를 생각해보세요. 개인도 환영입니다. ^^ <br />

기부는 온라인 결제 사이트인 페이팔을 이용해주세요 : <a href="http://narin.byfun.com/donation">기부하기</a><br />

무통장입금으로 해주실분은 <a href="http://byfun.com/donation/narinwiki">이곳</a>에서 계좌
정보를 확인해주세요. <br />
<br />
감사합니다. <br />

</div>

<h3>나린위키 알아보기</h3>
<ul>
	<li><a href="http://narin.byfun.com">나린위키 홈페이지</a></li>
	<li><a href="http://narin.byfun.com/manual">나린위키 사용자 매뉴얼</a></li>
	<li><a href="http://narin.byfun.com/syntax">나린위키 위키문법</a></li>
	<li><a href="http://narin.byfun.com/license">나린위키 저작권</a></li>
</ul>



		<?
		include_once $g4['path']."/tail.php";
		exit;
}

include_once $g4['path']."/head.php";

?>
<link rel="stylesheet" href="./css/narin.wiki.style.css" type="text/css"/>

<h1>위키 설치</h1>
<form name="frmwikiinstall" action="install.php"
	onsubmit="return submit_check(this);" method="post"><input
	type="hidden" name="md" value="doit" />
<div class="list_table">
<table cellpadding="0" cellspacing="0" border="0">
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
			<th scope="row">위키 경로</th>
			<td><input type="text" name="wiki_path" size="15" required
				itemname="위키 경로" /> &nbsp; (그누보드 폴더로 부터의 상대 경로 e.g. <그누보드>/wiki 일 경우
			wiki 입력)</td>
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
	
</script> <?
include_once $g4['path']."/tail.php";

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


?>
