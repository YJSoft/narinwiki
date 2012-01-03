<?
if (!defined("_GNUBOARD_")) exit; //개별 페이지 접근 불가 
$wikiConfig =& wiki_class_load("Config");
$defaultEditLevel = $wikiConfig->setting['edit_level'];
?>

<a href="http://byfun.com" target="_blank" style="float:right;font-size:8pt;color:#ccc;">byfun</a>
<h2 style="margin:0 0 10px 0">쉬운 권한 관리</h2>

<div id="msg" style="display:none;position:absolute;padding:10px 30px;text-align:center;background-color:#333;color:#fff;z-index:999999"></div>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
	<td valign="top" style="width:200px;border-right:1px solid #ccc;padding-right:5px; ">
		<div id="tree_wrapper"></div>
	</td>

	<td valign="top" style="padding-left:10px;">
		<div id="current_folder" style="font-size:12pt;font-weight:bold;margin-bottom:10px; "></div>
		<table id="folder_list" width="100%"></table>

	</td>
</tr>
</table>

<script type="text/javascript">

var f = {
	loc : '/',
	msg : $('#msg'),
	title : $('#current_folder'),
	msg_timer : null
};

// 유틸 : 메시지 보이기
f.show_msg = function(str, seconds) {
	f.msg.html(str).center_now().show();		
	if(seconds) {
		if(f.msg_timer) f.msg_timer.clearTimeout();
		f.msg_timer = setTimeout(f.hide_msg, seconds*1000);
	}
};

// 유틸 : 메시지 감추기
f.hide_msg = function() {
	f.msg.fadeOut();
	f.msg_timer = null;
};

// Ajax 통신 wrapper
f.get_json = function(m, params, callback) {
	$.get(wiki_url + '/adm/admin.plugin.php?p=easyLevel&nolayout=1&m='+m, params, function(json) {
		try {
			json = $.parseJSON(json);
		} catch(ex) {
			f.show_msg('AJAX 통신 오류', 2);	
			return;
		}
		if(callback) callback(json);
	});
};

// 문서 보기 링크
f.get_url = function(doc) {
	if(wiki_fancy) return wiki_url + '/read' + doc.replace(/\s/g, '+');
	else return wiki_url + '/narin.php?doc=' + doc;
};

// 트리 랜더링
f.render_tree = function(json) {
	$tw = $('#tree_wrapper');
	$tw.html(json.tree);
	$(".narin_tree").treeview({
		collapsed: true
	});
	$tw.find('span.folder a').click(function(evt) {
		evt.preventDefault();
		f.loc = $(this).attr('code');		
		f.load_list();
		f.set_tree_location();
	});	
	f.set_tree_location();
};

// 트리의 현재 폴더 설정
f.set_tree_location = function() {	
	$("#tree_wrapper").find('span.folder a').removeClass('selected');
	$("#tree_wrapper").find('span.leaf').removeClass('leaf_folder').addClass('leaf_folder');
	$("#tree_wrapper").find('span.folder a[code="'+f.loc+'"]').addClass('selected').parent().removeClass('leaf_folder');
	$("#tree_wrapper").find('span.folder a[code="'+f.loc+'"]').parent().parent().parents('li:first').each(function() {
		if(!$(this).hasClass('open') && $(this).hasClass('expandable')) {
			$(this).find('.hitarea').trigger('click');
		}
	});
};

// 목록 랜더링
f.render_list = function(json) {
	
	f.hide_msg();
	
	f.title.html(f.loc);
	
	$table = $('#folder_list').html('');

	$table.append(
		$('<tr></tr>').append(
			$('<th></th>').html('이름')
		)
		.append(
			$('<th></th>').attr('style', 'width:60px').html('접근권한')
		)
		.append(
			$('<th></th>').attr('style', 'width:150px').html('편집권한')
		)
	);
															 		
	for(i=0; i<json.list.length; i++) {
		var item = json.list[i];
		$table.append(
			$('<tr></tr>').append(
											$('<td></td>').append(
											  item.type == 'folder' ? (
																			$('<a></a>').html(item.name).data('loc', item.path).attr('href', 'javascript:;')
																								  .click(function() {
																								  	f.loc = $(this).data('loc');
																										f.load_list();
																									})
																			) : '<a href="'+f.get_url(item.path)+'" target="_blank">'+item.name+'</a>'
											)
											.data('type', item.type)
											.data('path', item.path)
											.attr('class', 'flist ' + item.type)
										)
										.append($('<td></td>').html(f.get_selectbox(item.type == 'folder' ? item.ns_access_level : item.access_level, 1)))
										.append($('<td></td>').html(f.get_selectbox(item.type == 'doc' ? item.edit_level : '-', 0)))
										.hover(function() {
											$(this).attr('style', 'background-color:#f0f0f0');
										}, function() {
											$(this).attr('style', 'background-color:#fff');
										})
		)
	}
	
	if(json.list.length > 0) {
		$table.append(
			$('<tr></tr>').append(
				$('<td></td>').attr('colspan', '3').append(
					$('<div></div>').attr('style', 'text-align:right; margin-top:10px;padding-top:10px;border-top:1px solid #ccc').append(
						$('<span></span>').attr('class', 'button red').append(
							$('<a></a>').attr('href', 'javascript:;').html('적용').click(function() {
								var ulist = [];
								$table.find('.flist').each(function() {
									_type = $(this).data('type');
									_path = $(this).data('path');
									_acc_level = $(this).next().find('select').val();
									_edit_level = ( _type == 'doc' ? $(this).next().next().find('select').val() : '');
									ulist.push({type : _type, path : _path, access_level : _acc_level, edit_level : _edit_level});
								});
								var recursive = $table.find('#recursive').is(':checked');
								f.get_json('update_level', { update_list : ulist, recursive : recursive}, function() {
									if(json.code == 1) f.show_msg('업데이트 완료', 2);
									else f.show_msg(json.msg);
								});
							})	// a							
						)	// span
					).append('<input type="checkbox" class="chk" name="recursive" id="recursive" style="width:13px;height:13px;margin:1px 5px 2px 10px;padding:0;vertical-align:middle"/><label for="recursive" style="margin:0" title="폴더의 하위폴더들의 권한도 재귀적으로 모두 변경합니다" >하위폴더에도 적용</label>')	// div
				)	// td
			)	// tr
		);	// table
	} else {
		$table.append('<tr><td colspan="3" style="padding:10px 0">자료가 없습니다.</td></tr>');
	}
	f.set_tree_location();
};

// 셀랙트박스 만들기
f.get_selectbox = function(slt, from) {
	if(slt == '-') return slt;
	var $sl = $('<select></select>');
	for(var i=from; i<=10; i++) {
		var o_name = i;
		if(i == 0 && from == 0) o_name = '위키기본설정따름(<?=$defaultEditLevel?>)';
		var $opt = $('<option></option>').attr('value', i).html(o_name);
		if(slt == i) $opt.attr('selected', 'selected');
		$opt.appendTo($sl);
	}
	return $sl;
};

// 트리 불러오기
f.load_tree = function() { this.get_json('get_tree', {loc : f.loc}, f.render_tree);	};	

// 목록 불러오기
f.load_list = function() { 
	f.show_msg('잠시만 기다려주세요');
	f.get_json('get_list', { loc : f.loc }, f.render_list); 
};

// html 이 모두 로드된 후 트리, 목록 불러오기
$(document).ready(function() {
	f.load_tree();
	f.load_list();
});
</script>
