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

ob_start();	
include_once "_common.php";
$g4['title'] = '나린위키 미디어 관리자';
$loc = stripslashes($loc);
if(!$loc) $loc = "/";
$wikiConfig = wiki_class_load("Config");
$media_setting = $wikiConfig->media_setting;

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
		<? } ?>
	</div>
		<h1 id="folder_label"><?=$loc?></h1>	
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
		<div id="media_option">
			<input type="checkbox" class="checkbox" id="media_opt_selection" name="media_opt_selection">
			<label for="media_opt_selection" class="label">파일 선택 후 창을 닫지 않음</label>
		</div>
		<div id="narin_media_upload"></div>
		<div id="media_msg"></div>		
		<div id="media_search" class="clear">			
			<span class="button blue small"><input type="button" name="sbtn" id="sbtn" value="검색"></span>			
			<input type="text" name="stx" id="stx"/>
		</div>
		<table id="file_list" width="100%" cellspacing="0" cellpadding="0" border="0">
		<colgroup>			
			<col>
			<col width="30px">			
			<col width="80px">
			<col width="160px">
			<col width="20px">
		</colgroup>
		<thead>
		<tr>
			<th scope="col"><a href="#order_name" id="order_name" class="ordering" code="source">파일명</a></th>
			<th scope="col">&nbsp;</th>			
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
			<span class="button red small"><a href="#apply" id="media_image_apply">적용</span>
		</div>
		<a href="#image_select_layer" id="show_img_layer" class="wiki_modal" style="display:none"></a>
	</div>
</div>

</div> <!--// media_manager_wrapper -->


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
	
	mm.init = function() {
		mm.tree_load(mm.loc);
						
		mm.sbtn.click(function(evt) {
			evt.preventDefault();
			mm.filter();
		});		
		
		mm.stx.keypress(function(evt) {
			 if(evt.which == 13) {
			 	mm.filter();
			 }
		});							
						
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
						
		$('.close_button').click(function(evt) { evt.preventDefault(); $.wiki_lightbox_close(); });

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


		$('.file_del').live('click', function() {
			tr = $(this).parents('.flist');
			mm.delete_file(tr.find('.fname').text(), tr);
		});

		mm.msg = $("<div></div>")
				.attr('style', 'display:none;position:absolute;padding:10px 30px;text-align:center;background-color:#333;color:#fff;z-index:999999')
				.html('').appendTo($("body"));		
		if(!$.browser.msie) {
			mm.msg.center();
		}
		mm.upload_button.click(function(evt) {
				evt.preventDefault();
				if(mm.uploading) {
					mm.show_msg('업로드중입니다...', 2);
					return;
				}
				if(mm.is_upload_visible) {
					$('#narin_uploader').remove();
					mm.is_upload_visible = false;
				} else {
					mm.set_uploader();
				}				
		});		
		
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
									mm.loc = folder;
									mm.tree_load(folder);
									mm.load();
								}
								else mm.show_msg(json.msg, 2);
								mm.hide_msg();
							});
				}
			}			
		});
		
		mm.rmdir_button.click(function(evt) {
			evt.preventDefault();
			if(mm.files.length > 0) {
				mm.show_msg('폴더에 파일이 있어 삭제할 수 없습니다.', 2);
				return;
			}
			if(!confirm('폴더를 삭제하시겠습니까')) return;
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
		
		mm.chmod_button.click(function(evt) {
			evt.preventDefault();
			var pan = $("#chmod_option");
			if(pan.is(':visible')) {
				pan.hide();
			} else {
				pan.show();
			}
		});
		
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
		
		Array.prototype.remove = function(from, to) {
		  var rest = this.slice((to || from) + 1 || this.length);
		  this.length = from < 0 ? this.length + from : from;
		  return this.push.apply(this, rest);
		};		
						
		mm.load();				
	};
	
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
	
	mm.set_tree_location = function() {	
		$("#tree_wrapper").find('span.folder a').removeClass('selected');
		$("#tree_wrapper").find('span.leaf').removeClass('leaf_folder').addClass('leaf_folder');
		$("#tree_wrapper").find('span.folder a[code="'+mm.loc+'"]').addClass('selected').parent().removeClass('leaf_folder');		
	};
	
	mm.show_msg = function(str, seconds) {
		mm.msg.html(str).center_now().show();		
		if(seconds) {
			if(mm.msg_timer) mm.msg_timer.clearTimeout();
			mm.msg_timer = setTimeout(mm.hide_msg, seconds*1000);
		}
	};
	
	mm.hide_msg = function() {
		mm.msg.fadeOut();
		mm.msg_timer = null;
	};
	
	mm.show_msg_stack = function(str, seconds) {
		$('<div></div>').attr('class', 'media_msg').text(str).prependTo(mm.msg_stack);
		if(seconds) {
			if(mm.msg_timer_stack) mm.msg_timer_stack.clearTimeout();
			mm.msg_timer_stack = setTimeout(mm.hide_msg_stack, seconds*1000);
		}		
	};
	
	mm.hide_msg_stack = function() {
		mm.msg_stack.find('.media_msg').fadeOut().remove();
		mm.msg_timer_stack = null;
	};
	
	mm.delete_file = function(fname, tr) {
		if(!confirm('삭제하시겠습니까?')) return;
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

	mm.render = function() {
		
		//mm.files.sort(mm.sort_by(mm.order_field, mm.order));
		mm.stx.val('');
		mm.table.find('tbody .flist').remove();
		for(i=0; i<mm.files.length; i++) {
			file = mm.files[i];
			if(file.img_width > 0) {
				is_img = true;
				img = $('<a></a>').attr('href', file.imgsrc).html('<img class="thumb" src="'+file.thumb+'"/>').wiki_lightbox();
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
			$('<td></td>').attr('style', 'padding-left:28px;background:url("'+file.ext_icon+'") no-repeat left center;')
										.append($('<a></a>').attr('href', 'javascript:;')
																				.addClass('fname').data('file_path', file_path).data('is_img', is_img)
																				.data('source', file.source)
																				.data('img_width', file.img_width).data('img_height', file.img_height)
																				.html(file.source)
																				.click(function(evt) {
																					mm.mark($(this));
																				})
										)
										.append(img_info)
										.appendTo(tr);
			$('<td></td>').append(img).attr('style', 'text-align:right;padding-right:5px').appendTo(tr);														
			$('<td></td>').html(file.filesize).appendTo(tr);
			$('<td></td>').html(file.reg_date).appendTo(tr);
			$('<td></td>').html(cmd_del).appendTo(tr);
			mm.table.find('tbody').append(tr);
		}
		if(mm.files.length == 0) {
			mm.table.find('tbody').append('<tr class="flist"><td colspan="5">파일이 없습니다.</td></tr>');
		}
	};
	
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
	
	mm.set_uploader = function() {
		$('#narin_uploader').remove();
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
				{title : "업로드 가능 파일", extensions : "<?=$media_setting['allow_extensions']?>"},			
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
	   
	}
	
	
	
	$(document).ready(function() {
		if(!window.opener) {
			alert('잘못된 접근입니다.');
			window.location.href = wiki_path + '/narin.php?bo_table='+g4_bo_table;
		}
		else mm.init();
		window.focus();
	});	
</script>

<? include_once "tail.php"; ?>

<?

$content = ob_get_contents();
ob_end_clean();

include_once $wiki[path]."/lib/Minifier/htmlmin.php";
include_once $wiki[path]."/lib/Minifier/jsmin.php";
include_once $wiki[path]."/lib/Minifier/cssmin.php";
echo Minify_HTML::minify($content, $options=array("jsMinifier"=>"JSMin::minify", "cssMinifier"=>"CssMin::minify"));

?>