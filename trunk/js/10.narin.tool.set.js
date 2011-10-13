	function narin_emoticons() {
		var emos = {"8-)" : "icon_cool.gif",
									"8-O" : "icon_eek.gif",
									":-(" : "icon_sad.gif",
									":-)" : "icon_smile.gif",
									":=)" : "icon_smile2.gif",
									":-/" : "icon_doubt.gif",
									":-\\" : "icon_doubt2.gif",
									":-?" : "icon_confused.gif",
									":-D" : "icon_biggrin.gif",
									":-P" : "icon_razz.gif",
									":-O" : "icon_surprised.gif",
									":-X" : "icon_silenced.gif",
									":-|" : "icon_neutral.gif",
									";-)" : "icon_wink.gif",
									"^_^" : "icon_fun.gif",
									":?:" : "icon_question.gif",
									":!:" : "icon_exclaim.gif",
									"LOL" : "icon_lol.gif",
									"FIXME" : "fixme.gif",
									"DELETEME" : "delete.gif"};
		var dropmenu = [];
		var style = '';
		for(var k in emos) {
			c = emos[k];
			klass = emos[k].replace(".gif", "");			
			dropmenu.push({name:'', replaceWith : k, className : klass});
			style += ".markItUp .narin_emoticon ."+ klass + " a {background-image:url("+wiki_path+"/imgs/smileys/"+klass+".gif)}\n";			
			if(klass.indexOf("icon_") < 0) {
				style += ".markItUp .narin_emoticon ."+ klass + " {width:85px;}\n";			
				style += ".markItUp .narin_emoticon ."+ klass + " a {width:85px;}\n";			// fixme, delete : width 80px
			}
		}		
		$(document).ready(function() {
			$(document.body).append("<style type='text/css'>"+style+"</style>");
		});
		return dropmenu;		
	}
	
	function special_chars() {
		var chars = [
			"＃＆＊＠§※☆★○●◎◇◆□■△▲▽▼→←↑↓↔〓◁◀▷＃▷▶♤♠♡♥♧♣⊙◈▣◐◑▒▤▥▨▧▦▩♨☏☎☜☞¶†‡↕↗↙↖↘♭♩♪♬㉿㈜№㏇™㏂㏘℡®ªº",
			"＋－＜＝＞±×÷≠≤≥∞∴♂♀∠⊥⌒∂∇≡≒≪≫√∽∝",
			"＄％￦Ｆ′″℃Å￠￡￥¤℉‰€㎕㎖㎗ℓ㎘㏄㎣㎤㎥㎦㎛㎟㎠㎡㎢㏊㎍㏈㎧㎨㎰㎶Ω㎮㎯㏆",
			"‘’“”〔〕〈〉《》「」『』【】",
			"㉠㉡㉢㉣㉤㉥㉦㉧㉨㉩㉪㉫㉬㉭㉮㉯㉰㉱㉲㉳㉴㉵㉶㉷㉸㉹㉺㉻",
			"ⓐⓑⓒⓓⓔⓕⓖⓗⓘⓙⓚⓛⓜⓝⓞⓟⓠⓡⓢⓣⓤⓥⓦⓧⓨⓩ①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮",
			"ⅰⅱⅲⅳⅴⅵⅶⅷⅸⅹⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ",
			"ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩαβγδεζηθικλμνξοπρστυφχψω",
			"АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчш"
			//"ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをん",
			//"ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶ"
			].join('');
		var char_array = chars.split('');
		var dropmenu = [];
		for(i=0; i<char_array.length; i++) {
			c = char_array[i];
			dropmenu.push({name:c, replaceWith : c, className : "wiki_special_char"});
		}
		return dropmenu;
	}
	
	function find_doc(panel) {
		input = panel.find(".find_doc").eq(0);
		result = panel.find(".wf_result").eq(0);
		loading = panel.find(".wf_load").eq(0);
		stxt = $.trim(input.val());
		if(stxt == '') {
			result.html("검색어를 입력하세요.");
			return;
		}
		if(stxt.length < 2) {		
			result.html("검색어를 2자 이상 입력하세요.");	
			return;
		}
		result.html('');
		loading.show();
		$.getJSON(wiki_path+"/exe/get.php?bo_table="+g4_bo_table+"&w=find_doc&find_doc="+encodeURIComponent(stxt), function(json) {
			loading.hide();
			items = [];
			$.each(json, function(key, val) {
				items.push("<li><a href=\"javascript:;\" class=\"find_result\">"+(val.folder == "/" ? "" : val.folder) +"/"+val.docname+"</a></li>");
			});
			if(items.length > 0) result.html(items.join(''));
			else result.html("없습니다.");
		});
	}
	
	is_comment = ( wiki_script == 'write.php' ? false : true );
	
	mark_set = [];
if(!is_comment) {
	mark_set.push({name:'제목 1', key:'1', openWith:'= ', closeWith:' =', placeHolder:'제목', className:'narin_h1' });
	mark_set.push({name:'제목 2', key:'2', openWith:'== ', closeWith:' ==', placeHolder:'제목', className:'narin_h2' });
	mark_set.push({name:'제목 3', key:'3', openWith:'=== ', closeWith:' ===', placeHolder:'제목', className:'narin_h3' });
	mark_set.push({name:'제목 4', key:'4', openWith:'==== ', closeWith:' ====', placeHolder:'제목', className:'narin_h4' });
	mark_set.push({name:'제목 5', key:'5', openWith:'===== ', closeWith:' =====', placeHolder:'제목', className:'narin_h5' });
	mark_set.push({name:'제목 6', key:'6', openWith:'====== ', closeWith:' ======', placeHolder:'제목', className:'narin_h6' });
	mark_set.push({separator:'---------------' });
}
	mark_set.push({name:'굵게', key:'B', openWith:"** ", closeWith:" **", className:'narin_bold'});
	mark_set.push({name:'기울게', key:'I', openWith:"// ", closeWith:" //", className:'narin_italic'});
	mark_set.push({name:'밑줄', key:'U', openWith:"__ ", closeWith:" __", className:'narin_underline'});
	mark_set.push({name:'취소선', key:'S', openWith:'<del> ', closeWith:' </del>', className:'narin_stroke'});
	mark_set.push({name:'글자색', className:'narin_colors', dropMenu: [
	          {name:'Yellow',  openWith:'<color #FCE94F>', closeWith:'</color>', className:"col1-1" },
            {name:'Yellow',  openWith:'<color #EDD400>', closeWith:'</color>', className:"col1-2" },
            {name:'Yellow',  openWith:'<color #C4A000>', closeWith:'</color>', className:"col1-3" },
            {name:'Orange',  openWith:'<color #FCAF3E>', closeWith:'</color>', className:"col2-1" },
            {name:'Orange',  openWith:'<color #F57900>', closeWith:'</color>', className:"col2-2" },
            {name:'Orange',  openWith:'<color #CE5C00>', closeWith:'</color>', className:"col2-3" },
            {name:'Brown',   openWith:'<color #E9B96E>', closeWith:'</color>', className:"col3-1" },
            {name:'Brown',   openWith:'<color #C17D11>', closeWith:'</color>', className:"col3-2" },
            {name:'Brown',   openWith:'<color #8F5902>', closeWith:'</color>', className:"col3-3" },
            {name:'Green',   openWith:'<color #8AE234>', closeWith:'</color>', className:"col4-1" },
            {name:'Green',   openWith:'<color #73D216>', closeWith:'</color>', className:"col4-2" },
            {name:'Green',   openWith:'<color #4E9A06>', closeWith:'</color>', className:"col4-3" },
            {name:'Blue',    openWith:'<color #729FCF>', closeWith:'</color>', className:"col5-1" },
            {name:'Blue',    openWith:'<color #3465A4>', closeWith:'</color>', className:"col5-2" },
            {name:'Blue',    openWith:'<color #204A87>', closeWith:'</color>', className:"col5-3" },
            {name:'Purple',  openWith:'<color #AD7FA8>', closeWith:'</color>', className:"col6-1" },
            {name:'Purple',  openWith:'<color #75507B>', closeWith:'</color>', className:"col6-2" },
            {name:'Purple',  openWith:'<color #5C3566>', closeWith:'</color>', className:"col6-3" },
            {name:'Red',     openWith:'<color #EF2929>', closeWith:'</color>', className:"col7-1" },
            {name:'Red',     openWith:'<color #CC0000>', closeWith:'</color>', className:"col7-2" },
            {name:'Red',     openWith:'<color #A40000>', closeWith:'</color>', className:"col7-3" },
            {name:'Gray',    openWith:'<color #FFFFFF>', closeWith:'</color>', className:"col8-1" },
            {name:'Gray',    openWith:'<color #D3D7CF>', closeWith:'</color>', className:"col8-2" },
            {name:'Gray',    openWith:'<color #BABDB6>', closeWith:'</color>', className:"col8-3" },
            {name:'Gray',    openWith:'<color #888A85>', closeWith:'</color>', className:"col9-1" },
            {name:'Gray',    openWith:'<color #555753>', closeWith:'</color>', className:"col9-2" },
            {name:'Gray',    openWith:'<color #000000>', closeWith:'</color>', className:"col9-3" }
            ]
       		 });
	mark_set.push({name:'특수문자표', className:'narin_special_char', dropMenu: special_chars()});
	mark_set.push({name:'이모티콘',   dropMenu: narin_emoticons(), className:'narin_emoticon'});
	mark_set.push({separator:'---------------' });
	mark_set.push({name:'목록', openWith:'(!(  * |!|*)!)', className:'narin_ul'});
	mark_set.push({name:'순서목록', openWith:'(!(  - |!|-)!)', className:'narin_ol'});
	mark_set.push({separator:'---------------' });
	mark_set.push({name:'위키문서', openWith:'[[[![문서명]!]|', closeWith:']]', placeHolder:'문서명', className:'narin_doc' });
	mark_set.push({name:'위키문서 검색',   beforeInsert:function(h) {
											btn = $(".markItUp .narin_find_doc a");
											offset = btn.offset();
											find_panel = $(h.textarea).next(".wiki_find_doc");
											if(!find_panel.length) {
									    	find_panel = $("<div></div>").attr('class', 'wiki_find_doc')
									    									.html([
									    									"<img class='wf_load' src='"+wiki_path+"/css/tool_images/loading.gif'>",
									    									"<h4>문서 검색</h4>",
									    									"<div class='wf_body'>",
									    									"<div class='wf_form clear'>",
									    									"		<label>문서명</label>",
									    									"		<input type='text' name='find_doc' class='find_doc' size='20'>",
									    									"		<span class='button purple small'><a href='#find_doc' class='wf_do'>검색</a></span>",
									    									"	",
									    									"</div>",
									    									"<ul class='wf_result'></ul>",
									    									"</div>"
									    									].join(''));
												$(h.textarea).after(find_panel);
									   		find_panel.css('top', offset.top + 18).css('left', offset.left);		
									    	
									    	find_panel.find(".wf_do").eq(0).click(function() { find_doc(find_panel) } );
									    	find_panel.find(".find_doc").eq(0).keypress(function(evt) {
									    		if(evt.which == 13) {
									    			evt.preventDefault();
									    			find_doc(find_panel);
									    		}
									    	});

									    	setTimeout(function() { find_panel.find(".find_doc").eq(0).focus(); }, 100);
									    	$(".find_result").live('click', function() {
									    		val = $(this).text();
									    		$.markItUp({ openWith : "[["+val+"|", closeWith : "]]", placeHolder:"문서명" } );
									    		find_panel.hide();
									    	});
									    } else {																	    	
									    	find_panel.toggle();	
									    	if(find_panel.is(":visible")) setTimeout(function() { find_panel.find(".find_doc").eq(0).focus(); }, 100);
									    }
										  $(".wiki_emoticons").hide();									    
									  }, className:'narin_find_doc' });
	mark_set.push({name:'외부문서', openWith:'[[![Url:!:http://]!] ', closeWith:']', placeHolder:'링크명', className:'narin_url' });
	mark_set.push({name:'주석', replaceWith:'(([![주석:]!]))', className:'narin_footnote' });
if(!is_comment) {	
	mark_set.push({separator:'---------------' });
	mark_set.push({name:'그림', replaceWith:'{{image=[![이미지 인덱스:]!]?width=[![너비]!]&height=[![높이]!]}}', className:'narin_picture'});
	mark_set.push({name:'파일', replaceWith:'{{file=[![파일 인덱스:]!] [![파일명]!]}}', className:'narin_file'});
	mark_set.push({name:'폴더', replaceWith:'{{folder=[![폴더:]!]}}', className:'narin_folder'});
}
	mark_set.push({separator:'---------------' });	
	mark_set.push({name:'인용', openWith:'(!(> |!|>)!)', className:'narin_quote'});
	mark_set.push({name:'코드블럭', openWith:'(!(<code [![프로그래밍언어 (e.g. php)]!]>\n|!|<pre>)!)', closeWith:'(!(\n</code>|!|</pre>)!)', className:'narin_code'});
	mark_set.push({	name:'표만들기', 
								className:'tablegenerator', 
								placeholder:"입력",
								replaceWith:function(h) {
									var cols = prompt("몇 열 테이블을 만드시겠습니까 ?"),
										rows = prompt("몇 행 테이블을 만드시겠습니까 ?"),
										html = "";
									if (h.altKey) {
										for (var c = 0; c < cols; c++) {
											html += "^ [![헤드"+(c+1)+" 텍스트:]!] ";	
										}	
										html += "^\n";
									}
									for (var r = 0; r < rows; r++) {
										for (var c = 0; c < cols; c++) {
											html += "| "+(h.placeholder||"")+" ";	
										}
										html+= "|\n";
									}
									return html;
								}
							});
if(!is_comment) {
	mark_set.push({name:'메타데이터', className:'narin_meta', dropMenu: [
	          {name:'목차사용안함',  replaceWith : '~~NOTOC~~', className:"meta_notoc" },
	          {name:'댓글사용',  replaceWith : '~~COMMENT~~', className:"meta_comment" },
	          {name:'캐시안함',  replaceWith : '~~NOCACHE~~', className:"meta_cache" }
	        ]});
}
	
	narinWikiSettings = {
	    nameSpace: "wiki", // Useful to prevent multi-instances CSS conflict
	    onTab : { keepDefault : false, replaceWith : function(h) {
	    	lines = h.textarea.value.substring(0, h.caretPosition).split('\n');
	    	if(lines.length <= 0) return '  ';
	    	m =  lines[lines.length-1].match(/^(\s{2,})([\*|-])(.+)/);
	    	if(m) {
	    		// FIXME : it's not working
	    		if(h.shiftKey) {
						h.textarea.value = h.textarea.value.substring(0, h.caretPosition - m[0].length + 2) + 
		    											h.textarea.value.substring(h.caretPosition -m[0].length, h.textarea.value.length);	    			
						return;		    											
	    		} else {	    			
		    		h.textarea.value = h.textarea.value.substring(0, h.caretPosition - m[0].length) + 
		    											'  ' + 
		    											h.textarea.value.substring(h.caretPosition -m[0].length, h.textarea.value.length);
						return;
	    		}
	    	}
	    	return '  ';
	    }},
	    onEnter: { keepDefault : false, replaceWith : function(h) {
	    	lines = h.textarea.value.substring(0, h.caretPosition).split('\n');
	    	if(lines.length <= 0) return;
	    	m =  lines[lines.length-1].match(/^(\s{2,})([\*|-])(.+)/);	    	
	    	bs = sp = "";
	    	ent = '\n';
	    	if(m) {
	    		if($.trim(m[3]) != '') {
	    			sp = m[1] + m[2] + ' ';
	    		} else {
	    			pos = h.caretPosition;
						h.textarea.value = h.textarea.value.substring(0, h.caretPosition - m[0].length) + h.textarea.value.substring(h.caretPosition, h.textarea.value.length);
						setCaretPosition(h.textarea, pos-m[0].length);
						return;
	    		}
	    	}
	    	setTimeout(function() {
					if(h.caretPosition + h.scrollPosition > h.textarea.scrollHeight  ) {						
						h.textarea.scrollTop = h.textarea.scrollHeight;
					}
	    	}, 5);
	    	return bs + ent + sp;
	    	}},
	    markupSet:  mark_set
	}	
	
	
$(document).ready(function() {		
	if(!is_comment) $('.wr_content').markItUp(narinWikiSettings);		
});	

function setCaretPosition(ctrl, pos){
	if(ctrl.setSelectionRange)
	{
		ctrl.focus();
		ctrl.setSelectionRange(pos,pos);
	}
	else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}