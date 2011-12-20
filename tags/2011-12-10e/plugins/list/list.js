$.fn.wiki_list = function() {
	
	// 몇시간 전인가?
	var elapsed_hour = function(now, date) {
		return Math.round((now.getTime() - date.getTime()) / 1000) / 60 / 60;
	};
	
	// 위키 기본 목록 스타일로 출력
	var list_list_rendering = function(div_id, json, params) {
		$div = $('#'+div_id);
		$list = $('<ul></ul>').attr('class', 'wiki_list wiki_list_1 list_list');
		
		// 목록
		for(i=0; i<json.list.length; i++) {
			var item = json.list[i];
			var row = $('<li></li>').append($('<a></a>').attr('href', item.href)
																									.addClass('wiki_active_link')
																									.addClass('list_title')
																									.attr('style', params.title_style)
																									.html(item.title));		

			if(params.nocomment == undefined && item.comments > 0) {
				row.append($('<span></span>').attr('class', 'list_comment').attr('style', params.comment_style).html('(' + item.comments + ')'));
			}
			
			if(params.showfolder != undefined) {
				row.append($('<span></span>').attr('class', 'list_folder').attr('style', params.folder_style).html(item.ns));
			}
			
			if(params.showeditor != undefined) {
				name = item.editor;
				if(params.usename != undefined) name = item.name;
				else if(params.usenick != undefined) name = item.nick;
				row.append($('<span></span>').attr('class', 'list_editor').attr('style', params.editor_style).html(name));
			}
			
			if(params.showdate != undefined) {
				var date = item.date;
				if(params.elapsed != undefined) date = item.elapsed;
				row.append($('<span></span>').attr('class', 'list_date').attr('style', params.date_style).html(date));
			}
			
			if(params.emp > 0) {
				if(elapsed_hour(new Date(json.current_time), new Date(item.datetime)) <= params.emp) {
					row.find('.list_title').attr('style', params.emp_style);			
				}				
			}
			row.appendTo($list);			
		}		
				
		$div.append($list).show();
	};
	
	
	// 테이블 형태로 출력
	var list_table_rendering = function(div_id, json, params) {
		$div = $('#'+div_id);
		var head = { title : '문서명', date : '날짜', editor : '편집자', hits : '조회수' };
		var field = params.field.replace(' ', '').split(',');
		
		var table = $('<table></table>').attr('cellspacing', '0')
																		.attr('cellpadding', '0')
																		.addClass('list_table');
		
		if(params.table_style != undefined) {
			table.attr('style', params.table_style);
		}
		
		// 헤더
		if(params.nohead == undefined) {
			var thead = $('<thead></thead>');
			var header = $('<tr></tr>');
			for(j=0; j<field.length; j++) {
				if(field[j] == 'date' && params.elapsed != undefined) {
					head[field[j]] = '시간';
				}
				header.append($('<td></td>').html(head[field[j]])
																	  .addClass('list_'+field[j]));
			}
			thead.append(header).appendTo(table);
		}
		
		var tbody = $('<tbody></tbody>');

		
		// 목록
		for(i=0; i<json.list.length; i++) {
			var item = json.list[i];
			var row = $('<tr></tr>');
			var is_recent = false;
						
			for(j=0; j<field.length; j++) {
				var as = '';
				var ae = '';
				var ns = '';

				var content = item[field[j]];
				var td = $('<td></td>').addClass('list_'+field[j])
															 .attr('style', params[field[j]+'_style']);
				
				if(field[j] == 'date') {			
					if(elapsed_hour(new Date(json.current_time), new Date(item.datetime)) <= params.emp) {
						is_recent = true;
					}
					if(params.elapsed != undefined) {
						content = item.elapsed;
					}
				}
				
				if(field[j] == 'editor') {
					if(params.usename != undefined) {
						content = item.name;
					} else if(params.usenick != undefined) {
						content = item.nick;
					}
				}						
				
				if(field[j] == 'title') {
					
					$('<a></a>').attr('href', item.href)
										  .html(content)
										  .addClass('wiki_active_link')
										  .appendTo(td);				
					
					if(params.nocomment == undefined && item.comments > 0) {
						$('<span></span>').addClass('list_comment').html(item.comments).appendTo(td);
					}					
					if(params.nofolder == undefined) {
						$('<span></span>').addClass('list_folder').html(item.ns).appendTo(td);
					}
				} else {
					td.html(content);
				}

				row.append(td);
			}
			if(is_recent && params.emp > 0) row.find('.list_title a').attr('style', params.emp_style);
			row.appendTo(tbody);
		}
		
		tbody.appendTo(table);
		
		// 출력
		$div.append(table).show();
	};
	
		
	return this.each(function(idx) {
		
		var div_id = 'wiki_list_'+ Math.round(Math.random()*1000000);
		$this = $(this);
		$this.attr('id', div_id);		
		var setting = { rows:5, type : 'list', title_length : 512, emp : 0, field:'title,editor,date', order : 'date', 
										editor_style : '', date_style : '', title_style : '', hits_style : '', 
										comment_style : '', folder_style : '', emp_style : 'font-weight:bold', 
										dateformat : 'Y-m-d H:i:s' 
									};
		var params = $.parseJSON($this.html());
		params = $.extend(setting, params);
		
		if(params.noajax != undefined) {
			$('#'+div_id).empty();
			if(params.type == 'table') list_table_rendering(div_id, params.list, params);
			else list_list_rendering(div_id, params.list, params);
			return;
		}

		$.ajax({
			url : wiki_path + '/p.php?bo_table=' + g4_bo_table + '&p=list&m=list', 
			async : true,
			data : params, 
			dataType : 'json',
			success : function(json) {
				$('#'+div_id).empty();
				$('#'+div_id).prev().remove();
				if(json.code != 1) {
					$('#'+div_id).html(json.msg);
					return;
				}				
				if(params.type == 'table') list_table_rendering(div_id, json, params);
				else list_list_rendering(div_id, json, params);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$('#'+div_id).html('<span style="color:red">최근문서 로딩에 실패했습니다.</span>');
			}
		});		
	});
	
};

$(document).ready(function() {	
	$('.wiki_lister').wiki_list();
});
