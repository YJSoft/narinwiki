$.fn.wiki_latest = function() {
	
	// 몇시간 전인가?
	var elapsed_hour = function(now, date) {
		return Math.round((now.getTime() - date.getTime()) / 1000) / 60 / 60;
	};
	
	// 위키 기본 목록 스타일로 출력
	var latest_list_rendering = function(div_id, json, params) {
		$div = $('#'+div_id);
		$list = $('<ul></ul>').addClass('wiki_list_1').addClass('latest_list');
		
		// 목록
		for(i=0; i<json.list.length; i++) {
			var item = json.list[i];
			var row = $('<li></li>').append($('<a></a>').attr('href', item.href)
																									.addClass('wiki_active_link')
																									.addClass('latest_title')
																									.attr('style', params.title_style)
																									.html(item.title));		

			if(params.nocomment == undefined && item.comments > 0) {
				row.append($('<span></span>').attr('class', 'latest_comment').attr('style', params.comment_style).html('(' + item.comments + ')'));
			}
			
			if(params.showfolder != undefined) {
				row.append($('<span></span>').attr('class', 'latest_folder').attr('style', params.folder_style).html(item.ns));
			}
			
			if(params.showeditor != undefined) {
				name = item.editor;
				if(params.usename != undefined) name = item.name;
				else if(params.usenick != undefined) name = item.nick;
				row.append($('<span></span>').attr('class', 'latest_editor').attr('style', params.editor_style).html(name));
			}
			
			if(params.showdate != undefined) {
				var date = item.date;
				if(params.elapsed != undefined) date = item.elapsed;
				row.append($('<span></span>').attr('class', 'latest_date').attr('style', params.date_style).html(date));
			}
			
			if(params.emp > 0) {
				if(elapsed_hour(new Date(json.current_time), new Date(item.datetime)) <= params.emp) {
					row.find('.latest_title').attr('style', params.emp_style);			
				}				
			}
			row.appendTo($list);			
		}		
				
		$div.append($list).show();
	};
	
	
	// 테이블 형태로 출력
	var latest_table_rendering = function(div_id, json, params) {
		$div = $('#'+div_id);
		var head = { title : '문서명', date : '날짜', editor : '편집자' };
		var order = params.order.replace(' ', '').split(',');
		
		var table = $('<table></table>').attr('cellspacing', '0')
																		.attr('cellpadding', '0')
																		.addClass('latest_table');
		
		if(params.table_style != undefined) {
			table.attr('style', params.table_style);
		}
		
		// 헤더
		if(params.nohead == undefined) {
			var thead = $('<thead></thead>');
			var header = $('<tr></tr>');
			for(j=0; j<order.length; j++) {
				if(order[j] == 'date' && params.elapsed != undefined) {
					head[order[j]] = '시간';
				}
				header.append($('<td></td>').html(head[order[j]])
																	  .addClass('latest_'+order[j]));
			}
			thead.append(header).appendTo(table);
		}
		
		var tbody = $('<tbody></tbody>');

		
		// 목록
		for(i=0; i<json.list.length; i++) {
			var item = json.list[i];
			var row = $('<tr></tr>');
			var is_recent = false;
			
			for(j=0; j<order.length; j++) {
				var as = '';
				var ae = '';
				var ns = '';
				var content = item[order[j]];
				var td = $('<td></td>').addClass('latest_'+order[j])
															 .attr('style', params[order[j]+'_style']);
				
				if(order[j] == 'date') {			
					if(elapsed_hour(new Date(json.current_time), new Date(item.datetime)) <= params.emp) {
						is_recent = true;
					}
					if(params.elapsed != undefined) {
						content = item.elapsed;
					}
				}
				
				if(order[j] == 'editor') {
					if(params.usename != undefined) {
						content = item.name;
					} else if(params.usenick != undefined) {
						content = item.nick;
					}
				}						
				
				if(order[j] == 'title') {
					
					$('<a></a>').attr('href', item.href)
										  .html(content)
										  .addClass('wiki_active_link')
										  .appendTo(td);				
					
					if(params.nocomment == undefined && item.comments > 0) {
						$('<span></span>').addClass('latest_comment').html(item.comments).appendTo(td);
					}					
					if(params.nofolder == undefined) {
						$('<span></span>').addClass('latest_folder').html(item.ns).appendTo(td);
					}
				} else {
					td.html(content);
				}

				row.append(td);
			}
			if(is_recent && params.emp > 0) row.find('.latest_title').attr('style', params.emp_style);
			row.appendTo(tbody);
		}
		
		tbody.appendTo(table);
		
		// 출력
		$div.append(table).show();
	};
	
		
	return this.each(function(idx) {
		var div_id = 'wiki_latest_'+ Math.round(Math.random()*1000000);
		$this = $(this);
		$this.attr('id', div_id);		
		var setting = { rows:5, type : 'list', title_length : 512, emp : 0, order:'title,editor,date',
										editor_style : '', date_style : '', title_style : '', 
										comment_style : '', folder_style : '', emp_style : 'font-weight:bold', 
										dateformat : 'Y-m-d H:i:s' 
									};
		var params = $.parseJSON($this.html());
		params = $.extend(setting, params);

		$.ajax({
			url : wiki_path + '/p.php?bo_table=' + g4_bo_table + '&p=latest&m=list', 
			data : params, 
			dataType : 'json',
			success : function(json) {
				$('#'+div_id).empty();
				if(json.code != 1) {
					$('#'+div_id).html(json.msg);
					return;
				}				
				if(params.type == 'table') latest_table_rendering(div_id, json, params);
				else latest_list_rendering(div_id, json, params);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$('#'+div_id).html('<span style="color:red">최근문서 로딩에 실패했습니다.</span>');
			}
		});		
	});
	
};

$(document).ready(function() {	
	$('.wiki_latest').wiki_latest();
});
