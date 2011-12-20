<?
/**
 *
 * 미디어 관리자 스크립트
 *
 * @package	narinwiki
 * @subpackage pages
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */
$use_minify = true;

if($use_minify) ob_start();	
include_once "_common.php";

@mkdir($wiki['path'].'/data/'.$bo_table, 0707);
@mkdir($wiki['path'].'/data/'.$bo_table.'/files', 0707);

$g4['title'] = '나린위키 미디어 관리자';

$wikiConfig =& wiki_class_load("Config");
$media_setting = $wikiConfig->media_setting;

$is_admin_mode = false;
$colspan = 5;
if($is_wiki_admin && $md == 'admin') {
	$is_admin_mode = true;
	$colspan++;
}

$no_layout = true;
include_once "head.php";
?>
<style>
	html, body { background-color:#fff; }
	#media_manager_wrapper { padding:10px; background-color:#fff;}
	#file_list,#file_list th,#file_list td{border:0;}	
	#file_list{width:100%;border-bottom:2px solid #dcdcdc;text-align:left}
	#file_list caption{display:none}
	#file_list th{padding:7px 0 4px 4px;border-top:2px solid #dcdcdc;background-color:#f5f7f9;color:#666;font-family:'돋움',dotum;font-size:12px;font-weight:bold}
	#file_list td{padding:6px 0 4px 4px;border-top:1px solid #e5e5e5;color:#4c4c4c;}
	#narin_media_tree { width:200px; vertical-align:top;border-right:1px solid #ccc; padding-right:5px; }	
	#buttons { float:right;}
	#narin_media_content { height:400px; vertical-align:top; padding-left:10px; }
	#narin_media_tree h1,
	#narin_media_content h1 { font-size:14pt; padding:0 0 8px 0; margin:0 0 8px 0; border-bottom:1px solid #ccc; }	
	#chmod_option { display:none; text-align:right; padding:5px; margin:5px; background-color:#efefef;}
	#media_option { text-align:right; margin-bottom:5px; }	
	#media_search { }
	#media_search #stx { float:right;border:1px solid #ccc; }
	#media_search .button { float:right; margin-top:2px;}
	#media_gallery { float:left; margin-top:2px;}
	#gallery_table { width:100%; margin-top:8px; border-top:2px solid #888; padding:0; border-bottom:1px solid #888}
	#gallery_table th{ padding:5px;border-top:1px solid #e5e5e5;text-align:right;background-color:#f5f7f9;color:#666;font-weight:normal}
	#gallery_table td{ padding:5px;border-top:1px solid #e5e5e5;color:#4c4c4c;}
	#gallery_table label { top:6px; left:10px}
	.chk, .radio {width:13px;height:13px;margin:2px 5px 2px 0;padding:0;vertical-align:middle}			
	.media_msg { color:#DD0000; margin:2px 0px; padding:5px 5px; }
	.thumb { border:1px solid #ccc; padding:2px; }
	.image_size { color:#888; padding-left:8px; font-size:90%;}
	.plupload_header_content { display:none; }
</style>

<div id="media_manager_wrapper">
	
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
	<td id="narin_media_tree">
		<h1>나린미디어</h1>				
		<div id="tree_wrapper"></div>
	
	</td>
	<td id="narin_media_content" valign="top">
	<div id="buttons">			
		<span class="button"><a href="#upload" id="upload">업로드</a></span>	
		<span class="button"><a href="#mkdir" id="mkdir">새폴더</a></span>	
		<span class="button"><a href="#rmdir" id="rmdir">폴더삭제</a></span>
		<? if($is_wiki_admin) { ?>
		<span class="button"><a href="#chmod" id="chmod">권한설정</a></span>
			<? if($is_admin_mode) { ?>
			<span class="button"><a href="#zipdown" id="zipdown">ZIP다운로드</a></span>
			<? } ?>
		<? } ?>
	</div>
		<h1 id="folder_label">/</h1>	
		<? if($is_wiki_admin) { 
			$opts = "";
			for($i=1; $i<=10; $i++) $opts .= '<option value="'.$i.'">'.$i.'</option>';
		?>
		<div id="chmod_option">
			접근 : <select name="ns_access_level" id="ns_access_level"><?=$opts?></select>
			업로드 : <select name="ns_upload_level" id="ns_upload_level"><?=$opts?></select>
			폴더생성/삭제 : <select name="ns_mkdir_level" id="ns_mkdir_level"><?=$opts?></select>
			<span class="button small green"><a href="javascript:;" id="ns_level_update">적용</a></span>
		</div>
		<? } ?>
		<? if(!$is_admin_mode) { ?>
		<div id="media_option">
			<input type="checkbox" class="checkbox" id="media_opt_selection" name="media_opt_selection">
			<label for="media_opt_selection" class="label">파일 선택 후 창을 닫지 않음</label>
		</div>
		<? } ?>
		<div id="narin_media_upload"></div>
		<div id="media_msg"></div>		
		<? if(!$is_admin_mode) { ?>
		<div id="media_gallery">
			<a href="#gallery_insert_layer" id="gallery" class="wiki_modal">갤러리 삽입하기</a>
		</div>
		<? } ?>
		<div id="media_search" class="clear">			
			<span class="button blue small"><input type="button" name="sbtn" id="sbtn" value="검색"></span>			
			<input type="text" name="stx" id="stx"/>
		</div>
		<table id="file_list" width="100%" cellspacing="0" cellpadding="0" border="0">
		<colgroup>			
			<col>
			<col width="30px">	
			<? if($is_admin_mode) { ?>
			<col width="60px">	
			<? } ?>		
			<col width="80px">
			<col width="160px">
			<col width="20px">
		</colgroup>
		<thead>
		<tr>
			<th scope="col"><a href="#order_name" id="order_name" class="ordering" code="source">파일명</a></th>
			<th scope="col">&nbsp;</th>			
			<? if($is_admin_mode) { ?>
			<th scope="col"><a href="#order_downloads" id="order_downloads" class="ordering" code="reg_date">다운로드</a></th>
			<? }?>
			<th scope="col"><a href="#order_bytes" id="order_bytes" class="ordering" code="bytes">크기</a></th>
			<th scope="col"><a href="#order_date" id="order_date" class="ordering" code="reg_date">날짜</a></th>
			<th scope="col">&nbsp;</th>
		</tr>
		</thead>
		<tbody></tbody>			
		</table>	
	
	
	</td>
</tr>
</table>

<div style="display:none">
	<div id="image_select_layer">
		
		<div style="padding:5px;background-color:#333;color:#fff;margin-bottom:8px;">이미지 삽입</div>
		
		<div style="padding:0 10px;line-height:160%;">
		이미지 정렬 :
		<input type="radio" name="media_image_align" id="mia_no" value="no" checked="checked"><label for="mia_no">안함</label>
		<input type="radio" name="media_image_align" id="mia_left" value="left"><label for="mia_left">왼쪽</label>
		<input type="radio" name="media_image_align" id="mia_center" value="center"><label for="mia_center">가운데</label>
		<input type="radio" name="media_image_align" id="mia_right" value="right"><label for="mia_right">오른쪽</label>
		<br/>
		이미지 크기 : 
		<input type="radio" name="media_image_size" id="mia_small" value="<?=$media_setting['small_size']?>" checked="checked"><label for="mia_small">작게</label>
		<input type="radio" name="media_image_size" id="mia_medium" value="<?=$media_setting['medium_size']?>"><label for="mia_medium">보통</label>
		<input type="radio" name="media_image_size" id="mia_large" value="<?=$media_setting['large_size']?>"><label for="mia_large">크게</label>
		<input type="radio" name="media_image_size" id="mia_origin" value="0"><label for="mia_origin">원본크기</label>
		<br/>
		제목 : <input type="text" style="boder:1px solid #ccc" name="media_image_title" id="mit" size="20">
		</div>
		
		<div style="margin-top:5px; padding-top:5px; border-top:1px solid #ccc;text-align:center">
			<span class="button small"><a href="#close" class="close_button">닫기</a></span>
			<span class="button red small"><a href="#apply" id="media_image_apply">적용</a></span>
		</div>
		<a href="#image_select_layer" id="show_img_layer" class="wiki_modal" style="display:none"></a>
	</div>
</div>

</div> <!--// media_manager_wrapper -->

<div style="display:none">
	<div id="gallery_insert_layer">
		
		<div style="padding:5px;background-color:#333;color:#fff;margin-bottom:8px;">갤러리 삽입</div>
		모든 입력사항은 선택사항입니다. 입력/선택 하지 않으셔도 됩니다.
		<table id="gallery_table" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th>썸네일크기</th>
				<td>
					너비 : <input type="text" style="border:1px solid #ccc" name="media_gallery_width" id="mg_width" size="5">px 
					/ 높이 : <input type="text" style="border:1px solid #ccc" name="media_gallery_height" id="mg_height" size="5">px
				</td>
			</tr>			
			<tr>
				<th>파일명</th>
				<td>
						<input type="checkbox" name="media_gallery_showname" id="mg_showname" class="chk"/><label for="mg_showname">파일명 보기</label>
						<input type="checkbox" name="media_gallery_noext" id="mg_noext" checked disabled class="chk"/><label for="mg_noext">확장자 보기</label>
				</td>
			</tr>
			<tr>
				<th>정렬</th>
				<td>
						<select name="mg_sort" id="mg_sort">
							<option value="date">업로드 날짜</option>
							<option value="name">파일명</option>
							<option value="filesize">파일크기</option>
							<option value="random">랜덤</option>
						</select>
						<select name="mg_reverse" id="mg_reverse">
							<option value="0">내림차순</option>
							<option value="1">올림차순</option>
						</select>
				</td>
			</tr>
			<tr>
				<th>더보기</th>
				<td>
						<input type="checkbox" name="media_gallery_paging" id="mg_paging" class="chk"/><label for="mg_paging">더보기 사용</label>
						&nbsp;
						<select name="mg_page" id="mg_page" disabled>
							<option value="20">20</option>
							<option value="50">50</option>
							<option value="100" selected>100</option>
							<option value="200">200</option>
						</select> 장씩 보기
				</td>
			</tr>
		</table>
		
		<div style="margin-top:5px; padding-top:5px; text-align:center">
			<span class="button small"><a href="#close" class="close_button">닫기</a></span>
			<span class="button red small"><a href="#apply" id="media_gallery_apply">적용</a></span>
		</div>
	
	
	</div>
</div>

<style type="text/css">@import url(<?=$wiki['path']?>/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
<script type="text/javascript" src="<?=$wiki['path']?>/js/plupload/plupload.full.js"></script>
<script type="text/javascript" src="<?=$wiki['path']?>/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>
<script type="text/javascript" src="<?=$wiki['path']?>/js/plupload/i18n/ko.js"></script>

<script type="text/javascript">
	
	var mm = {
		loc : "/",
		files : [],
		tree : $('#tree_wrapper ul'),
		upload_button : $('#upload'),
		mkdir_button : $('#mkdir'),
		rmdir_button : $('#rmdir'),
		chmod_button : $('#chmod'),	
		folder_label : $('#folder_label'),
		stx : $('#stx'),
		sbtn : $('#sbtn'),
		img_select_layer : $('#image_select_layer'),
		file_select_layer : $('#file_select_layer'),
		table : $('#file_list'),
		uploader : null,
		uploading : false,
		uploader_wrapper : $('#narin_media_upload'),
		mb_id : '<?=$member['mb_id']?>',
		is_wiki_admin : <?=($is_wiki_admin ? 'true' : 'false')?>,
		mb_level : <?=$member['mb_level']?>,
		mkdir_level : 10,
		upload_level : 10,
		chmod_level : 10,
		access_level : 10,
		msg : null,
		msg_stack : $('#media_msg'),
		msg_timer : null,
		msg_timer_stack : null,
		filename : '',
		is_upload_visible : false,
		order_field : 'reg_date',	//source, bytes, reg_date
		order : 'desc',
		uploading_count : 0
	};
	
	// 미디어 관리자 초기화
	mm.init = function() {
		
		// 왼쪽 트리 메뉴 로딩
		mm.tree_load(mm.loc);
						
		// 검색 버튼 클릭 이벤트
		mm.sbtn.click(function(evt) {
			evt.preventDefault();
			mm.filter();
		});		
		
		// 검색창 엔터 입력 이벤트
		mm.stx.keypress(function(evt) {
			 if(evt.which == 13) {
			 	mm.filter();
			 }
		});							
		
		// 테이블 헤더 클릭 이벤트 (정렬)						
		$('.ordering').click(function(evt) {
			evt.preventDefault();
			field = $(this).attr('code');
			if(field != mm.order_field) {
				mm.order = 'desc';
			} else {
				mm.order = (mm.order == 'desc' ? 'asc' : 'desc');
			}
			mm.order_field = field;
			mm.ordering();
			mm.render();
		});								
		
		// 라이트박스 닫기 버튼	이벤트					
		$('.close_button').click(function(evt) { evt.preventDefault(); $.wiki_lightbox_close(); });

		// 이미지 삽입 확인 버튼 이벤트
		$('#media_image_apply').click(function() {

				var a = $("input[name=media_image_align]:checked").val();
				var s = $("input[name=media_image_size]:checked").val();				
				var t = $("#mit").val();
				var filepath = mm.clicked_link.data('file_path');
				var iw = parseInt(mm.clicked_link.data('img_width'));
				var ih = parseInt(mm.clicked_link.data('img_height'));
				var arg = [];
				
				if(s != 0 && iw > s) {					
					var h = Math.round(s * ih / iw);
					arg.push('width='+s+'&height='+h);
				}
				if(a != 'no') {
					arg.push('align='+a);
				}				
				if(t) {
					t = '|' + t;
				}
				if(arg.length > 0) arg = '?' + arg.join('&');
				else arg = '';
					
				if(!window.opener) window.close();

				window.opener.markitup_set({ replaceWith : "{{media="+filepath+arg+t+"}}" });
				if(!$("#media_opt_selection").is(':checked')) {
					window.close();
				}
				else $.wiki_lightbox_close();

		});
		
		// 갤러리 삽입 확인 버튼 이벤트
		$('#media_gallery_apply').click(function() {

				var g_width = $("#mg_width").val();
				var g_height = $("#mg_height").val();
				var g_showname = $("#mg_showname").is(":checked");
				var g_noext = $("#mg_noext").is(":checked");
				var g_sort_by = $("#mg_sort").val();
				var g_reverse = $("#mg_reverse").val();
				var g_paging = $("#mg_paging").is(":checked");
				var g_page = $("#mg_page").val();

				var arg = [];
				if(g_width) arg.push('width='+g_width);
				if(g_height) arg.push('height='+g_height);
				if(g_showname) {
					arg.push('showname');
					if(g_noext) arg.push('noext');
				}
				arg.push('sort='+g_sort_by);
				if(g_reverse == 1) arg.push('reverse');
				if(g_paging) {
					arg.push('paging='+g_page);
				}
				
				if(arg.length > 0) arg = '?' + arg.join('&');
				else arg = '';
					
				if(!window.opener) window.close();

				window.opener.markitup_set({ replaceWith : "{{gallery="+mm.loc+arg+"}}" });
				if(!$("#media_opt_selection").is(':checked')) {
					window.close();
				}
				else $.wiki_lightbox_close();

		});

		// 갤러리 삽입 창 : 페이징
		$('#mg_paging').click(function(evt) {
			if($(this).is(':checked')) {
				$('#mg_page').attr('disabled', '');
			} else {
				$('#mg_page').attr('disabled', 'disabled');
			}
		});
		
		// 갤러리 삽입 창 : 파일명보이기
		$('#mg_showname').click(function(evt) {
			if($(this).is(':checked')) {
				$('#mg_noext').attr('disabled', '');
			} else {
				$('#mg_noext').attr('disabled', 'disabled');
			}
		});		

		// 파일 삭제 버튼 클릭 이벤트
		$('.file_del').live('click', function() {
			tr = $(this).parents('.flist');
			mm.delete_file(tr.find('.fname').text(), tr);
		});
		
		<? if($is_admin_mode) { ?>
			// 폴더내 모든 파일 삭제 버튼 클릭
		$('#clear_media').live('click', function() {
			if(!confirm('폴더내의 모든 파일이 삭제됩니다.\n진행하시겠습니까?')) return;
			if(!confirm('정말 진행하시겠습니까?')) return;
			$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_clear&loc='+encodeURIComponent(mm.loc), function(data) {
				mm.load();
			});
		});
		
		// 압축 다운로드 버튼 클릭
		$('#zipdown').click(function(evt) { 
			evt.preventDefault();
			$.getJSON(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_zip&loc='+encodeURIComponent(mm.loc), function(json) {
				if(json.code == 1) {
					location.href = wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_zip_download&loc='+encodeURIComponent(mm.loc)+'&file='+json.file;
				} else mm.show_msg(json.msg, 2);
			});
		});
		<? }?>

		// 메시지 레이어
		mm.msg = $("<div></div>")
				.attr('style', 'display:none;position:absolute;padding:10px 30px;text-align:center;background-color:#333;color:#fff;z-index:999999')
				.html('').appendTo($("body"));		
		if(!$.browser.msie) {
			mm.msg.center();
		}
		
		// 상단 메뉴 : 업로드 버튼
		mm.upload_button.click(function(evt) {
				evt.preventDefault();
				if(mm.uploading) {
					mm.show_msg('업로드중입니다...', 2);
					return;
				}
				$('#narin_uploader').remove();
				if(mm.is_upload_visible) {
					mm.is_upload_visible = false;
				} else {
					mm.set_uploader();
				}				
		});		
		
		// 상단 메뉴 : 폴더생성 버튼
		mm.mkdir_button.click(function(evt) {
			evt.preventDefault();
			var folder = prompt('폴더명 : ', '');
			if(folder != null) {
				folder = ( mm.loc == '/' ? mm.loc : mm.loc + '/') + folder;
				if(!check_folder_name(folder)) {
					return;
				} else {
					mm.show_msg('새 폴더를 만드는 중입니다. 잠시만 기다리세요.');
					$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_mkdir&ploc='+encodeURIComponent(mm.loc)+'&loc='+encodeURIComponent(folder), function(data) {
								json = $.parseJSON(data);		
								if(json.code == 1) {
									mm.tree_load(mm.loc);
								}
								else mm.show_msg(json.msg, 2);
								mm.hide_msg();
							});
				}
			}			
		});
		
		// 상단 메뉴 : 폴더삭제 버튼
		mm.rmdir_button.click(function(evt) {
			evt.preventDefault();
			if(mm.files.length > 0) {
				mm.show_msg('폴더에 파일이 있어 삭제할 수 없습니다.', 2);
				return;
			}
			if(!confirm('폴더를 삭제하시겠습니까?')) return;
			$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_rmdir&loc='+encodeURIComponent(mm.loc), function(data) {
				json = $.parseJSON(data);
				if(json.code == 1) {
					mm.loc = json.updir;
					mm.tree_load(mm.loc);
					mm.load();
				} else {
					mm.show_msg(json.msg, 2);
				}
			});
		});
		
		// 상단 메뉴 : 권한설정 버튼
		mm.chmod_button.click(function(evt) {
			evt.preventDefault();
			var pan = $("#chmod_option");
			if(pan.is(':visible')) {
				pan.hide();
			} else {
				pan.show();
			}
		});
		
		// 상단 메뉴 : 권한설정 실행 버튼
		$("#ns_level_update").click(function(evt) {
			evt.preventDefault();
			al = $("#ns_access_level").val();
			cl = $("#ns_mkdir_level").val();
			ul = $("#ns_upload_level").val();
			$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_chmod&loc='+encodeURIComponent(mm.loc), {
					access_level : al,
					upload_level : ul,
					mkdir_level : cl
				}, function(data) {
				json = $.parseJSON(data);
				if(json.code == 1) {
					mm.set_ns_level(json.access_level, json.upload_level, json.mkdir_level, 9);
					mm.show_msg('권한이 변경되었습니다.', 2);
				} else {
					mm.show_msg(json.msg, 2);
				}
			});			
		});
		
		// 유틸 : 배열에서 원소 삭제
		Array.prototype.remove = function(from, to) {
		  var rest = this.slice((to || from) + 1 || this.length);
		  this.length = from < 0 ? this.length + from : from;
		  return this.push.apply(this, rest);
		};		
		
		// 파일목록 로딩						
		mm.load();			
			
	}; // mm.init 끝
	
	
	// 폴더 권한 설정 적용
	mm.set_ns_level = function(al, ul, cl, pcl) {
		mm.access_level = al;
		mm.mkdir_level = cl;
		mm.upload_level = ul;
		$("#ns_access_level").val(al);
		$("#ns_mkdir_level").val(cl);
		$("#ns_upload_level").val(ul);
		
		if(mm.loc == '/' || ( !mm.is_wiki_admin && ( mm.mb_level < pcl || mm.mb_level < cl ) )  ) {
			mm.rmdir_button.hide();
		} else {
			mm.rmdir_button.show();
		}
		if(!mm.is_wiki_admin && mm.mb_level < cl) {
			mm.mkdir_button.hide();
		} else {
			mm.mkdir_button.show();
		}		
		if(!mm.is_wiki_admin && mm.mb_level < ul) {
			mm.upload_button.hide();
		} else mm.upload_button.show();					
	};
	
	// 트리 로딩	
	mm.tree_load = function(dir, callback) {	
		mm.folder_label.text(dir);	
		$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_get_tree&loc='+encodeURIComponent(dir), function(data) {
			json = $.parseJSON(data);
			if(json.code < 0) {
				window.location.href = wiki_path + '/media.php?bo_table=' + g4_bo_table;
			}
			
			$("#tree_wrapper").html(json.tree);
			$(".narin_tree").treeview({
				collapsed: true
			});
			$("#tree_wrapper").find('span.folder a').click(function(evt) {
				evt.preventDefault();
				mm.loc = $(this).attr('code');
				mm.folder_label.text(mm.loc);
				mm.load();
				mm.set_tree_location();
			});
			mm.set_tree_location();
			if(callback) callback();
			
		});				
	};
	
	// 트리에 현재 폴더 스타일 적용
	mm.set_tree_location = function() {	
		$("#tree_wrapper").find('span.folder a').removeClass('selected');
		$("#tree_wrapper").find('span.leaf').removeClass('leaf_folder').addClass('leaf_folder');
		$("#tree_wrapper").find('span.folder a[code="'+mm.loc+'"]').addClass('selected').parent().removeClass('leaf_folder');		
	};
	
	// 유틸 : 메시지 보이기
	mm.show_msg = function(str, seconds) {
		mm.msg.html(str).center_now().show();		
		if(seconds) {
			if(mm.msg_timer) mm.msg_timer.clearTimeout();
			mm.msg_timer = setTimeout(mm.hide_msg, seconds*1000);
		}
	};
	
	// 유틸 : 메시지 감추기
	mm.hide_msg = function() {
		mm.msg.fadeOut();
		mm.msg_timer = null;
	};
	
	// 유틸 : 파일목록창 위에 여러 메시지 보이기
	mm.show_msg_stack = function(str, seconds) {
		$('<div></div>').attr('class', 'media_msg').text(str).prependTo(mm.msg_stack);
		if(seconds) {
			if(mm.msg_timer_stack) mm.msg_timer_stack.clearTimeout();
			mm.msg_timer_stack = setTimeout(mm.hide_msg_stack, seconds*1000);
		}		
	};
	
	// 유틸 : 파일목록창 위의 여러 메시지 감추기
	mm.hide_msg_stack = function() {
		mm.msg_stack.find('.media_msg').fadeOut().remove();
		mm.msg_timer_stack = null;
	};
	
	// 파일 삭제
	mm.delete_file = function(fname, tr) {
		if(!confirm('삭제하시겠습니까?\n파일을 삭제하면 파일을 링크하고 있는 문서의 링크가 끊깁니다.')) return;
		mm.show_msg('삭제중입니다. 잠시만 기다려주세요.');
		$.post(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_delete&loc='+encodeURIComponent(mm.loc)+'&file='+encodeURIComponent(fname), function(data) {
			json = $.parseJSON(data);		
			if(json.code == 1) {
				del_idx = -1;
				for(i=0; mm.files.length; i++) {					
					if(mm.files[i].source && mm.files[i].source == fname) {
						del_idx = i;
						break;
					}
				}
				tr.remove();				
				mm.files.remove(del_idx);
			}
			else mm.show_msg(json.msg, 2);
			mm.hide_msg();
			mm.render();
		});
	};
	
	// 파일 검색 (현재 목록 중에서 검색)
	mm.filter = function() {
		var stx = $.trim(mm.stx.val());
		mm.table.find('.flist').show();
		if(stx == '') {			
			return;
		}
		mm.table.find('.flist').each(function() {
			var regx = new RegExp(stx,"gi");
			if(!regx.test($(this).find('.fname').data('source'))) {
				$(this).hide();
			}
		});
	};
	
	// 파일 정렬 (현재 목록을 정렬)
	mm.ordering = function() {
		var m = ( mm.order == 'desc' ? -1 : 1);
		var k = function(v) {
			if(mm.order_field == 'bytes') return parseInt(v[mm.order_field]);
			else return v[mm.order_field];
		};
		mm.files.sort(function(a, b) {			
				return ( (k(a) < k(b)) ? -1 :
							   (k(a) > k(b)) ? 1 : 0 ) * m;				
		});	
	};
			
	// 파일 목록 로딩
	mm.load = function(callback) {
		mm.stx.val('');
		mm.table.find('.flist').remove();
		mm.show_msg('파일 목록을 읽어오고 있습니다...');
		$.get(wiki_path + '/exe/a.php?bo_table='+g4_bo_table+'&w=media_list&loc='+encodeURIComponent(mm.loc), function(data) {
			json = $.parseJSON(data);
			if(json.code == -101) {
				mm.show_msg(json.msg, 2);
				setTimeout("window.location.reload()", 2100);
				return;
			}
			if(json.code == -1) {
				mm.show_msg(json.msg, 2);
				return;
			}
			mm.files = json.files;
			mm.set_ns_level(json.access_level, json.upload_level, json.mkdir_level, json.parent_mkdir_level);			
			mm.ordering();
			mm.render();
			mm.hide_msg();
		});			
	};

	// 파일 목록 보이기
	mm.render = function() {
		
		//mm.files.sort(mm.sort_by(mm.order_field, mm.order));
		mm.stx.val('');
		mm.table.find('tbody .flist').remove();
		for(i=0; i<mm.files.length; i++) {
			file = mm.files[i];
			if(file.img_width > 0) {
				is_img = true;
				img = $('<a></a>').addClass('media_lightbox').attr('href', file.imgsrc.replace(/^(\.\.\/)/i, '')).html('<img class="thumb" src="'+file.thumb.replace(/^(\.\.\/)/i, '')+'"/>').wiki_lightbox();
				img_info = '<span class="image_size">'+file.img_width+'x'+file.img_height+'</span>';
			} else {
				is_img = false;
				img = '';
				img_info = '';
			}
			if(file.mb_id == mm.mb_id || mm.is_wiki_admin) {
				cmd_del = '<a href="javascript:;" class="file_del"><img src="'+wiki_path+'/imgs/media_manager/delete.gif" border="0"/></a>';
			} else cmd_del = '';

			if(mm.loc == '/') file_path = '/' + file.source;
			else file_path = mm.loc + '/' + file.source;
				
			var tr = $('<tr></tr>').attr('class', 'flist');
			$('<td></td>').attr('style', 'padding-left:28px;background:url("'+file.ext_icon.replace(/^(\.\.\/)/i, '')+'") no-repeat left center;')
										.append($('<a></a>').attr('href', 'javascript:;')
																				.addClass('fname').data('file_path', file_path).data('is_img', is_img)
																				.data('source', file.source).data('url', file.href.replace(/^(\.\.\/)/i, ''))
																				.data('img_width', file.img_width).data('img_height', file.img_height)
																				.html(file.source)
																				<? if($is_admin_mode) { ?>
																				.click(function(evt) {
																					if(!$(this).data('is_img')) {																						
																						location.href = $(this).data('url');
																					} else {																						
																						$(this).parent().parent().find('.media_lightbox').trigger('click');
																					}
																				})																					
																				<? } else { ?>
																				.click(function(evt) {
																					mm.mark($(this));
																				})																					
																				<? } ?>
										)
										.append(img_info)
										.appendTo(tr);
			$('<td></td>').append(img).attr('style', 'text-align:right;padding-right:5px').appendTo(tr);														
			<? if($is_admin_mode) { ?>
			$('<td></td>').attr('style', 'text-align:center').append((is_img ? '' : file.downloads)).appendTo(tr);
			<? } ?>
			$('<td></td>').html(file.filesize).appendTo(tr);
			$('<td></td>').html(file.reg_date).appendTo(tr);
			$('<td></td>').html(cmd_del).appendTo(tr);
			mm.table.find('tbody').append(tr);
		}

		if(mm.files.length == 0) {
			mm.table.find('tbody').append('<tr class="flist"><td colspan="<?=$colspan?>">파일이 없습니다.</td></tr>');
		} else {
		<? if($is_admin_mode) { ?>
		mm.table.find('tbody').append(['<tr class="flist"><td colspan="<?=$colspan?>" style="text-align:right">',
																	 '<span class="button red small"><a href="javascript:;" id="clear_media">모든파일삭제</a></span>',
																	 '</td></tr>'].join(''));
		<? } ?>			
		}
	};
	
	// 이미지 : 파일명 클릭시 
	mm.mark = function($a) {
		var is_img = $a.data('is_img');		
		mm.clicked_link = $a;
		$("#mit").val('');
		if(is_img) $("#show_img_layer").trigger('click');
		else {
			if(!window.opener) window.close();
			window.opener.markitup_set({ openWith : "{{media="+$a.data('file_path')+"|", closeWith : "}}" });
			if(!$("#media_opt_selection").is(':checked')) window.close();			
		}
	};
	
	// 업로더 : 업로더 설정 및 보이기
	mm.set_uploader = function() {
		mm.is_upload_visible = true;
		uploader = $('<div></div>').attr('id', 'narin_uploader').html('<p>&nbsp;</p>').appendTo(mm.uploader_wrapper);

		mm.uploader = uploader.pluploadQueue({
			runtimes : 'gears,flash,silverlight,html5',
			url : wiki_path+'/exe/media_upload.php?bo_table=<?=$wiki['bo_table']?>&loc='+encodeURIComponent(mm.loc),
			max_file_size : '<?=$media_setting['max_file_size']?>',
			chunk_size : '1mb',
			unique_names : true,
				
			<? if($media_setting['allow_extensions']) { ?>
			filters : [
				{title : "업로드 가능 파일", extensions : "<?=$media_setting['allow_extensions']?>"}
			],
			<? } ?>
	
			flash_swf_url : wiki_path+'/js/plupload/plupload.flash.swf',
			silverlight_xap_url : wiki_path+'/js/plupload/plupload.silverlight.xap',

			preinit : {
		 		UploadFile: function(up, file) {
					up.settings.url = wiki_path+'/exe/media_upload.php?bo_table=<?=$wiki['bo_table']?>&loc='+encodeURIComponent(mm.loc)+'&filename='+encodeURIComponent(file.name);
				}				
			},                 

			init : {	
						 						
				StateChanged: function(up) {
					if(up.state == plupload.STARTED) {
						mm.uploading = true;
						$(window).bind('beforeunload', function(){
						  return '업로드 중입니다. 다른 페이지로 이동하시겠습니까?';
						});						
					}
					if(up.state == plupload.STOPPED) {
						setTimeout(function() { 
							$('#narin_uploader').fadeOut(function() {
								$(this).remove(); 
								mm.hide_msg_stack(); 
							});
						}, 500);
						mm.is_upload_visible = false;
						mm.uploading = false;
						$(window).unbind('beforeunload');						
					}								
				},
					
				FileUploaded: function(up, file, info) {
					res = $.parseJSON(info.response);
					if(res.error) {
						mm.show_msg_stack(res.error.message);
						return;
					}

					if(file.percent == 100 && file.status == 5) {
						mm.uploading_count++;
						$.post(wiki_path+'/exe/a.php', { w : 'media_reg', bo_table : g4_bo_table, loc : mm.loc, source : file.name, file : file.target_name }, function(data) {
							mm.uploading_count--;
							try {
								json = $.parseJSON(data);
							} catch(exception) {
								mm.show_msg(data, 2);
								return;											
							}
							if(json.code == 1) {
								mm.files.splice(0,0,json);
								mm.render();
							}
						});
					}
				}
			}						
			
		});
			
		mm.uploader_wrapper.find('form').submit(function(e) {
	        if (mm.uploader.files.length > 0) {
	            mm.uploader.bind('StateChanged', function() {
	                if (mm.uploader.files.length === (mm.uploader.total.uploaded + mm.uploader.total.failed)) {
	                    $('form')[0].submit();
	                }
	            });	                
	            mm.uploader.start();
	        } else {
	            mm.show_msg('파일을 선택하세요.', 2);
	        }

	        return false;
	  });		

	};
	
	
	
	$(document).ready(function() {
		<? if(!$is_admin_mode) { ?>
		if(!window.opener) {
			alert('잘못된 접근입니다.');
			window.location.href = wiki_path + '/narin.php?bo_table='+g4_bo_table;
			return;
		}
		<? } ?>
		mm.init();
		window.focus();
	});	
</script>

<? include_once "tail.php"; ?>

<?
if($use_minify) {
	$content = ob_get_contents();
	ob_end_clean();
	
	include_once $wiki[path]."/lib/Minifier/htmlmin.php";
	include_once $wiki[path]."/lib/Minifier/jsmin.php";
	include_once $wiki[path]."/lib/Minifier/cssmin.php";
	echo Minify_HTML::minify($content, $options=array("jsMinifier"=>"JSMin::minify", "cssMinifier"=>"CssMin::minify"));
}
?>
