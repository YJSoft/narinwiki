	
// 에디터 툴바 설정
mark_plugins.push({
	
	// 이름 : 툴바의 메뉴 텍스트
	name : '리스트',
	
	// 툴바 메뉴 클릭시 실행
	beforeInsert : function(h) {
		
		// '플러그인' 메뉴 아이템의 position
		pos = $(".markItUp .narin_plugin").position();
		
		// 설정창 panel 선택
		list_panel = $(h.textarea).prev(".list_plugin_pan");
		
		// 설정창 panel 이 없을 경우, 생성
		if (!list_panel.length) {
			
			// 설정창 panel 생성 
			list_panel = $("<div></div>").attr('class', 'list_plugin_pan')
						 .attr('style', 'position:absolute;left:-407px;width:400px;background-color:#fff;padding:0px;padding:5px;border:1px solid #3C769D;')							
						 .html([
						    // 타이틀바
						    '<div style="font-weight:bold;padding:5px;background-color:#eaeaea;margin-bottom:8px">',
						    '<a href="javascript:;" id="npl_close" style="float:right;font-weiht:normal">X</a>',
						    '리스트 삽입',
						    '</div>',
						    // 옵션 테이블 ////////////////////////////
						    '<form id="npl_form">',
						    '<table cellpadding="0" cellspacing="0" border="0">',
						    '<colgroup><col width="55px"><col width="145px"><col width="55px"><col width="145px"></colgroup>',
						    // 폴더
						    '<tr><td>폴더</td><td colspan="3"><input type="text" id="npl_folder" style="width:90%"/></td></tr>',
						   	// 타입 & 목록
						    '<tr><td>타입</td><td>',
								'<select id="npl_type">',
								'<option value="list">리스트</option>',
								'<option value="table">테이블</option>',
								'</td>',
							  '<td>목록수</td><td>',
								'<input type="text" id="npl_rows" size="3"/>',
								'</td></tr>',							
								
						   	// 정렬 필드 / 역순정렬
						    '<tr><td>정렬</td><td colspan="3">',
								'<select id="npl_order">',
								'<option value="date">날짜</option>',
								'<option value="title">문서명</option>',
								'<option value="hit">조회수</option>',
								'<option value="comment">댓글수</option>',
								'</select>&nbsp;&nbsp;',
								'<input type="checkbox" id="npl_reverse" class="chk"/><label for="npl_reverse">역순</label>',
								'</td></tr>',												
								
								// 날짜 형식
								'<tr><td>날짜형식</td><td><input type="text" id="npl_dateformat" value="Y-m-d H:i:s" style="width:90%"/></td>',
								'<td colspan="2"><input type="checkbox" id="npl_elapsed" class="chk"/>',
								'<label for="npl_elapsed">지난시간으로 표기</label></td></tr>',
								
								// 테이블 only //////////////////////
								// 필드 순서
								'<tr class="npl_only" style="display:none"><td>필드</td>',
								'<td colspan="3"><input type="text" id="npl_field" value="title,editor,date"/></td></tr>',
								
								// 리스트 only //////////////////////
								// showfolder, showeditor, showdate
								'<tr class="npl_only"><td>보이기</td>',
								'<td colspan="3">',
								'<input type="checkbox" id="npl_showfolder" class="chk"/><label for="npl_showfolder">폴더</label>&nbsp;&nbsp;',
								'<input type="checkbox" id="npl_showeditor" class="chk"/><label for="npl_showeditor">편집자</label>&nbsp;&nbsp;',
								'<input type="checkbox" id="npl_showdate" class="chk"/><label for="npl_showdate">날짜</label>',
								'</td></tr>',
								
								// 버튼
								'<tr><td colspan="4" style="border-top:1px solid #ccc;padding:5px;text-align:center;">',
								'<span class="button small red"><a href="javascript:;" id="npl_apply">적용</a></span>',
								'</td></tr>',
								'</table></form>'
							 ].join(''));
			
			// 설정창 닫기
			var close_npl = function() { list_panel.hide(); };
			
			// 설정창 안의 element 선택
			var npl = function(ele) { return list_panel.find(ele); };
			
			// 설정창 안의 data 읽어옴
			var npl_val = function(ele) { return $.trim(npl(ele).val()); };
			
			// 설정창 안의 input 이 checked 인지 확인
			var npl_is_checked = function(ele) { return npl(ele).is(":checked"); };
			
			// 'x' 클릭하면 창 숨김							 
			npl('#npl_close').click(close_npl);
			
			// type 을 바꾸면 table only 나 list only 를 toggle (숨김/보임)
			npl('#npl_type').change(function() {
				var v = $(this).val();
				npl('.npl_only').toggle();
			});
			
			// 적용 버튼 클릭 이벤트
			npl("#npl_apply").click(function() {
				
				// 문법 쿼리 생성
				var qry = '{{list=';
				
				// 폴더 경로
				var _folder = npl_val("#npl_folder");
				if(_folder == '') {
					alert('폴더 경로를 입력하세요');
					return;
				}				
				qry += _folder;				
				
				// 출력 타입 (list or table)
				qry += '?type=' + npl_val("#npl_type");
				
				// 몇행 출력?
				var _rows = npl_val("#npl_rows");
				if(_rows != '') qry += '&rows='+_rows;
				
				// 날짜 포멧
				var _dateformat = npl_val("#npl_dateformat");
				if(_dateformat != '') qry += '&dateformat='+_dateformat;				
				
				// 역순
				if(npl_is_checked("#npl_reverse")) qry += '&reverse';
				
				// 지난시간으로 표기
				if(npl_is_checked("#npl_elapsed")) qry += '&elapsed';
				
				// list only
				if(npl_val("#npl_type") == 'list') {					
					if(npl_is_checked("#npl_showfolder")) qry += '&showfolder';
					if(npl_is_checked("#npl_showeditor")) qry += '&showeditor';
					if(npl_is_checked("#npl_showdate")) qry += '&showdate';
				} else {	// table only
					qry += '&'+npl_val("#npl_field");
				}
				qry += '}}';

				// 에디터에 적용
				$.markItUp( { replaceWith : qry });
				
				// 설정창 닫기
				close_npl();

			});
			
			// 에디터에 설정창 추기
			$(h.textarea).before(list_panel);
			
			// 설정창 위치 조정
			list_panel.css('margin-top', -4).css('margin-left', pos.left - 5);
			
		} else {	// 이미 한번 만들어진 경우...			
			
			// 보임/안보임 toggle
			list_panel.toggle();			
		}
		
		// 설정창이 보이는 경우, 폴더 입력에 포커스
		if (list_panel.is(":visible")) {
			setTimeout(function() { npl("#npl_folder").focus(); }, 100);
		}		
	},
	className : "plugin_list"
});


/* 
// 부분 캐시 업데이트 기능 추가로 JS 사용하지 않음
// FROM 2012-01-0x 
$.fn.wiki_list = function() {

	// 몇시간 전인가?
	var elapsed_hour = function(now, date) {
		return Math.round((now.getTime() - date.getTime()) / 1000) / 60 / 60;
	};

	// 위키 기본 목록 스타일로 출력
	var list_list_rendering = function(div_id, json, params) {
		$div = $('#' + div_id);
		$list = $('<ul></ul>').attr('class', 'wiki_list wiki_list_1 list_list');

		// 목록
		for (i = 0; i < json.list.length; i++) {
			var item = json.list[i];
			var row = $('<li></li>').append(
					$('<a></a>').attr('href', item.href).addClass(
							'wiki_active_link').addClass('list_title').attr(
							'style', params.title_style).html(item.title));

			if (params.nocomment == undefined && item.comments > 0) {
				row.append($('<span></span>').attr('class', 'list_comment')
						.attr('style', params.comment_style).html(
								'(' + item.comments + ')'));
			}

			if (params.showfolder != undefined) {
				row.append($('<span></span>').attr('class', 'list_folder')
						.attr('style', params.folder_style).html(item.ns));
			}

			if (params.showeditor != undefined) {
				name = item.editor;
				if (params.usename != undefined)
					name = item.name;
				else if (params.usenick != undefined)
					name = item.nick;
				row.append($('<span></span>').attr('class', 'list_editor')
						.attr('style', params.editor_style).html(name));
			}

			if (params.showdate != undefined) {
				var date = item.date;
				if (params.elapsed != undefined)
					date = item.elapsed;
				row.append($('<span></span>').attr('class', 'list_date').attr(
						'style', params.date_style).html(date));
			}

			if (params.emp > 0) {
				if (elapsed_hour(new Date(json.current_time), new Date(
						item.datetime)) <= params.emp) {
					row.find('.list_title').attr('style', params.emp_style);
				}
			}
			row.appendTo($list);
		}

		$div.append($list).show();
	};

	// 테이블 형태로 출력
	var list_table_rendering = function(div_id, json, params) {
		$div = $('#' + div_id);
		var head = {
			title : '문서명',
			date : '날짜',
			editor : '편집자',
			hits : '조회수'
		};
		var field = params.field.replace(' ', '').split(',');

		var table = $('<table></table>').attr('cellspacing', '0').attr(
				'cellpadding', '0').addClass('list_table');

		if (params.table_style != undefined) {
			table.attr('style', params.table_style);
		}

		// 헤더
		if (params.nohead == undefined) {
			var thead = $('<thead></thead>');
			var header = $('<tr></tr>');
			for (j = 0; j < field.length; j++) {
				if (field[j] == 'date' && params.elapsed != undefined) {
					head[field[j]] = '시간';
				}
				header.append($('<td></td>').html(head[field[j]]).addClass(
						'list_' + field[j]));
			}
			thead.append(header).appendTo(table);
		}

		var tbody = $('<tbody></tbody>');

		// 목록
		for (i = 0; i < json.list.length; i++) {
			var item = json.list[i];
			var row = $('<tr></tr>');
			var is_recent = false;

			for (j = 0; j < field.length; j++) {
				var as = '';
				var ae = '';
				var ns = '';

				var content = item[field[j]];
				var td = $('<td></td>').addClass('list_' + field[j]).attr(
						'style', params[field[j] + '_style']);

				if (field[j] == 'date') {
					if (elapsed_hour(new Date(json.current_time), new Date(
							item.datetime)) <= params.emp) {
						is_recent = true;
					}
					if (params.elapsed != undefined) {
						content = item.elapsed;
					}
				}

				if (field[j] == 'editor') {
					if (params.usename != undefined) {
						content = item.name;
					} else if (params.usenick != undefined) {
						content = item.nick;
					}
				}

				if (field[j] == 'title') {

					$('<a></a>').attr('href', item.href).html(content)
							.addClass('wiki_active_link').appendTo(td);

					if (params.nocomment == undefined && item.comments > 0) {
						$('<span></span>').addClass('list_comment').html(
								item.comments).appendTo(td);
					}
					if (params.nofolder == undefined) {
						$('<span></span>').addClass('list_folder')
								.html(item.ns).appendTo(td);
					}
				} else {
					td.html(content);
				}

				row.append(td);
			}
			if (is_recent && params.emp > 0)
				row.find('.list_title a').attr('style', params.emp_style);
			row.appendTo(tbody);
		}

		tbody.appendTo(table);

		// 출력
		$div.append(table).show();
	};

	return this.each(function(idx) {

		var div_id = 'wiki_list_' + Math.round(Math.random() * 1000000);
		$this = $(this);
		$this.attr('id', div_id);
		var setting = {
			rows : 5,
			type : 'list',
			title_length : 512,
			emp : 0,
			field : 'title,editor,date',
			order : 'date',
			editor_style : '',
			date_style : '',
			title_style : '',
			hits_style : '',
			comment_style : '',
			folder_style : '',
			emp_style : 'font-weight:bold',
			dateformat : 'Y-m-d H:i:s'
		};
		var params = $.parseJSON($this.html());
		params = $.extend(setting, params);

		if (params.noajax != undefined) {
			$('#' + div_id).empty();
			if (params.type == 'table')
				list_table_rendering(div_id, params.list, params);
			else
				list_list_rendering(div_id, params.list, params);
			return;
		}

		$.ajax( {
			url : wiki_url + '/p.php?bo_table=' + g4_bo_table
					+ '&p=list&m=list',
			async : true,
			data : params,
			dataType : 'json',
			success : function(json) {
				$('#' + div_id).empty();
				$('#' + div_id).prev().remove();
				if (json.code != 1) {
					$('#' + div_id).html(json.msg);
					return;
				}
				if (params.type == 'table')
					list_table_rendering(div_id, json, params);
				else
					list_list_rendering(div_id, json, params);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$('#' + div_id).html(
						'<span style="color:red">최근문서 로딩에 실패했습니다.</span>');
			}
		});
	});

};

$(document).ready(function() {
	$('.wiki_lister').wiki_list();
});
*/