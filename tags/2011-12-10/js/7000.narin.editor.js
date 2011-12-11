/**
 * 나린위키 에디터 툴바 스크립트
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
  
// 이모티콘 드랍 메뉴
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

// 특수문자 드랍 메뉴
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

// 위키문서 검색
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
	$.getJSON(wiki_path+"/exe/a.php?bo_table="+g4_bo_table+"&w=find_doc&find_doc="+encodeURIComponent(stxt), function(json) {
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

// 툴바 배열 셋팅
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

mark_set.push({name:'글자크기', className:'narin_sizes', dropMenu: [
	{name:'6pt',	openWith:'<size 6pt>', closeWith:'</size>', className:"size6" },
	{name:'8pt',  openWith:'<size 8pt>', closeWith:'</size>', className:"size8" },
	{name:'10pt',  openWith:'<size 10pt>', closeWith:'</size>', className:"size10" },
	{name:'12pt',  openWith:'<size 12pt>', closeWith:'</size>', className:"size12" },
	{name:'14pt',  openWith:'<size 14pt>', closeWith:'</size>', className:"size14" },
	{name:'16pt',  openWith:'<size 16pt>', closeWith:'</size>', className:"size16" },
	{name:'18pt',  openWith:'<size 18pt>', closeWith:'</size>', className:"size18" },
	{name:'20pt',  openWith:'<size 20pt>', closeWith:'</size>', className:"size20" }
]});

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
mark_set.push({name:'위키문서 검색',   
	beforeInsert:function(h) {
		pos = $(".markItUp .narin_find_doc").position();
		find_panel = $(h.textarea).prev(".wiki_find_doc");
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
			$(h.textarea).before(find_panel);
			find_panel.css('margin-top', -4).css('margin-left', pos.left-5);			
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
	// 미디어관리자 추가로 '그림', '파일' 기능을 사용하지 않스빈다.
	// 사용하려면 다음 두줄의 주석을 지워주세요.
	//mark_set.push({name:'그림', replaceWith:'{{image=[![이미지 인덱스:]!]?width=[![너비]!]&height=[![높이]!]}}', className:'narin_picture'});
	//mark_set.push({name:'파일', replaceWith:'{{file=[![파일 인덱스:]!] [![파일명]!]}}', className:'narin_file'});
	mark_set.push({name:'폴더', replaceWith:'{{folder=[![폴더:]!]}}', className:'narin_folder'});
	mark_set.push({name:'미디어 관리자', className : 'narin_media'});	
}
mark_set.push({separator:'---------------' });	
mark_set.push({name:'인용', openWith:'(!(> |!|>)!)', className:'narin_quote'});
mark_set.push({name:'코드블럭', openWith:'(!(<code [![프로그래밍언어 (e.g. php)]!]>\n|!|<pre>)!)', closeWith:'(!(\n</code>|!|</pre>)!)', className:'narin_code'});
mark_set.push({	name:'표만들기', className:'tablegenerator', placeholder:"입력",
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
	mark_set.push({name:'메타데이터', className:'narin_meta', 
		dropMenu: [
			{name:'목차사용안함',  replaceWith : '~~NOTOC~~', className:"meta_notoc" },
			{name:'댓글사용',  replaceWith : '~~COMMENT~~', className:"meta_comment" },
			{name:'캐시안함',  replaceWith : '~~NOCACHE~~', className:"meta_cache" }
	  ]});

	mark_set.push( {separator:'---------------' } );
	mark_set.push( {name:'미리보기', call:'preview', className:'preview'} );
}


jQuery.fn.narinEditor = function(ns) {
	
	// 나린 위키 툴바 설정
	narinWikiSettings = {
		
		// 셋팅 네임스페이스
		nameSpace: ns,
	  markupSet:  mark_set,
	  previewParserVar : 'content',
		previewParserPath:  wiki_path+"/preview.php"
	}
	
	this.markItUp(narinWikiSettings);	
	
	if($.browser.mozilla) {
		this.keypress(function(e) {
			addKeyEvent($(this).get(0), e);
		});				
	} else {
		this.keydown(function(e) {
			addKeyEvent($(this).get(0), e);
		});	
	}	
	
	function addKeyEvent(ele, e) {
    if(e.which != 13 &&	// Enter
       e.which != 8  &&	// Backspace
       e.which != 32) return;	// Space
    var field     = ele;
    var selection = getSelection(field);
    if(selection.length) return; //there was text selected, keep standard behavior
    var search    = "\n"+field.value.substr(0,selection.start);
    var linestart = Math.max(search.lastIndexOf("\n"),
                             search.lastIndexOf("\r")); //IE workaround
    search = search.substr(linestart);


    if(e.which == 13){ // Enter
        // keep current indention for lists and code
        var match = search.match(/(\n  +([\*-] ?)?)/);
        if(match){
            var scroll = field.scrollHeight;
            var match2 = search.match(/^\n  +[\*-]\s*$/);
            // Cancel list if the last item is empty (i. e. two times enter)
            if (match2 && field.value.substr(selection.start).match(/^($|\r?\n)/)) {
                field.value = field.value.substr(0, linestart) + field.value.substr(selection.start);
                selection.start = linestart ;
                selection.end = linestart;
                setSelection(selection);	                
            } else {
                insertAtCarret(field.id,match[1]);
            }
            field.scrollTop += (field.scrollHeight - scroll);
            e.preventDefault(); // prevent enter key
            return false;
        }
    }else if(e.which == 8){ // Backspace
        // unindent lists
        var match = search.match(/(\n  +)([*-] ?)$/);
        if(match){
            var spaces = match[1].length-1;

            if(spaces > 3){ // unindent one level
                field.value = field.value.substr(0,linestart)+
                              field.value.substr(linestart+2);
                selection.start = selection.start - 2;
                selection.end   = selection.start;
            }else{ // delete list point
                field.value = field.value.substr(0,linestart)+
                              field.value.substr(selection.start);
                selection.start = linestart;
                selection.end   = linestart;
            }
            setSelection(selection);
            e.preventDefault(); // prevent backspace
            return false;
        }
    }else if(e.which == 32){ // Space
        // intend list item
        var match = search.match(/(\n  +)([*-] )$/);
        if(match){
            field.value = field.value.substr(0,linestart)+'  '+
                          field.value.substr(linestart);
            selection.start = selection.start + 2;
            selection.end   = selection.start;
            setSelection(selection);
            e.preventDefault(); // prevent space
            return false;
        }
    }			
	}
}

// textarea 에 caret 설정
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


/**
 * selection prototype
 *
 * Object that capsulates the selection in a textarea. Returned by getSelection.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function selection_class(){
    this.start     = 0;
    this.end       = 0;
    this.obj       = null;
    this.rangeCopy = null;
    this.scroll    = 0;
    this.fix       = 0;

    this.getLength = function(){
        return this.end - this.start;
    };

    this.getText = function(){
        if(!this.obj) return '';
        return this.obj.value.substring(this.start,this.end);
    };
}

/**
 * Get current selection/cursor position in a given textArea
 *
 * @link   http://groups.drupal.org/node/1210
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://linebyline.blogspot.com/2006/11/textarea-cursor-position-in-internet.html
 * @returns object - a selection object
 */
function getSelection(textArea) {
    var sel = new selection_class();

    sel.obj   = textArea;
    sel.start = textArea.value.length;
    sel.end   = textArea.value.length;
    textArea.focus();
    if(document.getSelection) {          // Mozilla et al.
        sel.start  = textArea.selectionStart;
        sel.end    = textArea.selectionEnd;
        sel.scroll = textArea.scrollTop;
    } else if(document.selection) {      // MSIE
        /*
         * This huge lump of code is neccessary to work around two MSIE bugs:
         *
         * 1. Selections trim newlines at the end of the code
         * 2. Selections count newlines as two characters
         */

        // The current selection
        sel.rangeCopy = document.selection.createRange().duplicate();
        if (textArea.tagName === 'INPUT')  {
            var before_range = textArea.createTextRange();
            before_range.expand('textedit');                       // Selects all the text
        } else {
            var before_range = document.body.createTextRange();
            before_range.moveToElementText(textArea);              // Selects all the text
        }
        before_range.setEndPoint("EndToStart", sel.rangeCopy);     // Moves the end where we need it

        var before_finished = false, selection_finished = false;
        var before_text, selection_text;
        // Load the text values we need to compare
        before_text  = before_range.text;
        selection_text = sel.rangeCopy.text;

        sel.start = before_text.length;
        sel.end   = sel.start + selection_text.length;

        // Check each range for trimmed newlines by shrinking the range by 1 character and seeing
        // if the text property has changed.  If it has not changed then we know that IE has trimmed
        // a \r\n from the end.
        do {
            if (!before_finished) {
                if (before_range.compareEndPoints("StartToEnd", before_range) == 0) {
                    before_finished = true;
                } else {
                    before_range.moveEnd("character", -1);
                    if (before_range.text == before_text) {
                        sel.start += 2;
                        sel.end += 2;
                    } else {
                        before_finished = true;
                    }
                }
            }
            if (!selection_finished) {
                if (sel.rangeCopy.compareEndPoints("StartToEnd", sel.rangeCopy) == 0) {
                    selection_finished = true;
                } else {
                    sel.rangeCopy.moveEnd("character", -1);
                    if (sel.rangeCopy.text == selection_text) {
                        sel.end += 2;
                    } else {
                        selection_finished = true;
                    }
                }
            }
        } while ((!before_finished || !selection_finished));


        // count number of newlines in str to work around stupid IE selection bug
        var countNL = function(str) {
            var m = str.split("\r\n");
            if (!m || !m.length) return 0;
            return m.length-1;
        };
        sel.fix = countNL(sel.obj.value.substring(0,sel.start));

    }
    return sel;
}

/**
 * Set the selection
 *
 * You need to get a selection object via getSelection() first, then modify the
 * start and end properties and pass it back to this function.
 *
 * @link http://groups.drupal.org/node/1210
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param object selection - a selection object as returned by getSelection()
 */
function setSelection(selection){
    if(document.getSelection){ // FF
        // what a pleasure in FF ;)
        selection.obj.setSelectionRange(selection.start,selection.end);
        if(selection.scroll) selection.obj.scrollTop = selection.scroll;
    } else if(document.selection) { // IE
        selection.rangeCopy.collapse(true);
        selection.rangeCopy.moveStart('character',selection.start - selection.fix);
        selection.rangeCopy.moveEnd('character',selection.end - selection.start);
        selection.rangeCopy.select();
    }
}

/**
 * Wraps around pasteText() for backward compatibility
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function insertAtCarret(textAreaID, text){
    var txtarea = $("#"+textAreaID);
    var selection = getSelection(txtarea.get(0));
    pasteText(selection,text,{nosel: true});
}

/**
 * Inserts the given text at the current cursor position or replaces the current
 * selection
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string text          - the new text to be pasted
 * @param objct  selecttion    - selection object returned by getSelection
 * @param int    opts.startofs - number of charcters at the start to skip from new selection
 * @param int    opts.endofs   - number of characters at the end to skip from new selection
 * @param bool   opts.nosel    - set true if new text should not be selected
 */
function pasteText(selection,text,opts){
    if(!opts) opts = {};
    // replace the content

    selection.obj.value =
        selection.obj.value.substring(0, selection.start) + text +
        selection.obj.value.substring(selection.end, selection.obj.value.length);

    // set new selection
    if ($.browser.opera) {
        // Opera replaces \n by \r\n when inserting text.
        selection.end = selection.start + text.replace(/\r?\n/g, '\r\n').length;
    } else {
        selection.end = selection.start + text.length;
    }

    // modify the new selection if wanted
    if(opts.startofs) selection.start += opts.startofs;
    if(opts.endofs)   selection.end   -= opts.endofs;

    // no selection wanted? set cursor to end position
    if(opts.nosel) selection.start = selection.end;

    setSelection(selection);
}

function markitup_set(opts) {
	var opt = { target : '#wr_content' };
	opt = $.extend(opt, opts);
	$.markItUp(opt);
}