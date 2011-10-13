
// 나린 위키 툴바 설정
narinWikiSettings = {
	
	// 셋팅 네임스페이스
	nameSpace: "wiki",
	
	// 탭 입력 이벤트 처리
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
		}
	},
	
	// 엔터 입력 이벤트 처리
	onEnter: { keepDefault : false, 
		replaceWith : function(h) {
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
		}
	},
	markupSet:  mark_set
}	

// 글쓰기의 에디터 툴바
$(document).ready(function() {		
	if(!is_comment) $('.wr_content').markItUp(narinWikiSettings);		
});	

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