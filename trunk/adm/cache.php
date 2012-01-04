<?
/**
 *
 * 위키 관리 : 썸네일 페이지 스크립트
 *
 * @package	narinwiki
 * @subpackage admin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

$pageid = "cache";

include_once("_common.php");
include_once "admin.head.php";

list($file_size, $file_count) = wiki_dir_filesize(WIKI_PATH.'/data/'.$bo_table.'/thumb');
?>
<style type="text/css">
#admbasic th {
	text-align: right;
	width: 150px;
	padding-right: 10px;
}

#admbasic td {
	padding-left: 5px;
}

#progress { position:absolute;margin-top:5px;margin-left:10px; color:#0000AA}
</style>

<div class="list_table">

<table id="admbasic" cellspacing="0" cellpadding="0" border="0">
	<tbody>
		<tr>
			<th scope="row">생성된 썸네일 총 크기</th>
			<td id="file_size"><?=wiki_file_size($file_size)?></td>
		</tr>
		<tr>
			<th scope="row">생성된 썸네일 총 개수</th>
			<td id="file_count"><?=$file_count?> 파일</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<span class="button red"><input type="submit" id="cache_clear" value="썸네일 및 문서 캐시 초기화" /></span>
				<span id="progress"></span>
			</td>
		</tr>
	</tbody>
</table>
</div>

<script type="text/javascript">
	
	pan = $("#progress");
	
	/*
	$.ajaxSetup({
		error:function(x,e){
			pan.html('<font color="red">AJAX 에러 : ' + (x.status || e) + '</font>');
		}
	});
	*/
	
	$("#cache_clear").click(function(evt) {
		evt.preventDefault();
		if(confirm('썸네일을 모두 삭제하고 캐시를 재작성 하겠습니까?\n사용되지 않는 썸네일은 삭제되고 모든 캐시와 썸네일이 새로 만들어집니다.\n문서가 많을 경우 시간이 오래 걸릴 수 도 있습니다.')) {
			pan.html('썸네일을 삭제하고 있습니다.');
			$.getJSON(wiki_url+'/adm/exe_cache.php?bo_table='+g4_bo_table+'&md=clear', function(json) {
				if(json.code == 1) {
					pan.html('캐시를 새로 작성합니다. 잠시만 기다려주세요...');
					recreate_cache(1);					
				}
			});
		}
	});
	
	function recreate_cache(page) {
		$.get(wiki_url+'/adm/exe_cache.php?bo_table='+g4_bo_table+'&md=rc&page='+page, function(json) {
			try {
				json = $.parseJSON(json);
			} catch(ex) {
				alert('[ 서버 에러 ]\n\n' + json);
				return;
			}
			if(json.code == 100) {
				pan.html('완료');
				$("#file_size").html(json.file_size);
				$("#file_count").html(json.file_count + " 파일");
				setTimeout(function() { pan.html(''); }, 2000);
			} else if(json.code == 1) {
				pan.html('완료 : ' + json.to + ', 총 문서 : ' + json.total);
				recreate_cache(page+1);
			}
		});
	}
	
</script>
<?
include_once "admin.tail.php";
?>
