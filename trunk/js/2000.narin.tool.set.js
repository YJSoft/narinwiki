// 나린 위키 툴바 설정
narinWikiSettings = {
	
	// 셋팅 네임스페이스
	nameSpace: "wiki",
	
	// 탭 입력 이벤트 처리
  onTab : { keepDefault : false, replaceWith : function(h) {
		// check if it is list
  	lines = h.textarea.value.substring(0, h.caretPosition).split('\n');
  	if(lines.length <= 0) return '  ';
  	m =  lines[lines.length-1].match(/^(\s{2,})([\*|-])(.+)/);
  	if(m) {
  		if(h.shiftKey) {
				h.textarea.value = h.textarea.value.substring(0, h.caretPosition - m[0].length + 2) + 
    											h.textarea.value.substring(h.caretPosition -m[0].length, h.textarea.value.length);	    			
				return;		    											
  		} else {	    			
  			pos = h.caretPosition;
  			setTimeout(function() {
	    		h.textarea.value = h.textarea.value.substring(0, pos - m[0].length) 
	    											 + '  ' 
	    											 + h.textarea.value.substring(pos -m[0].length, h.textarea.value.length);
					setCaretPosition(h.textarea, pos+2);
				}, 10);
				return;
  		}
  	}
  	return '  ';
  }},
	
	// 엔터 입력 이벤트 처리
	onEnter: { keepDefault : true, afterInsert : function(h) {
		pos = h.caretPosition;
		lines = h.textarea.value.substring(0, pos).split('\n');
		if(lines.length <= 0) return;
		m =  lines[lines.length-1].match(/^(\s{2,})([\*|-])(.+)/);	    	
		if(m) {
			if($.trim(m[3]) != '') {
				setTimeout(function() {
					h.textarea.value = h.textarea.value.substring(0, pos) + '\n' + m[1] + m[2] + ' ' + h.textarea.value.substring(pos+1, h.textarea.value.length);
					setCaretPosition(h.textarea, pos+m[1].length+3);
				}, 10);
			} else {
				if(m[1].length >= 4 && m[1].length % 2 == 0) {
	  			setTimeout(function() {
						h.textarea.value = h.textarea.value.substring(0, pos - m[0].length) + h.textarea.value.substring(pos-m[0].length+2, pos) + h.textarea.value.substring(pos+1, h.textarea.value.length);
						setCaretPosition(h.textarea, pos-2);
					}, 10);	    				
				} else {
	  			setTimeout(function() {
						h.textarea.value = h.textarea.value.substring(0, pos - m[0].length) + h.textarea.value.substring(pos+1, h.textarea.value.length);  	  				
						setCaretPosition(h.textarea, pos-m[0].length);
					}, 10);
				}
			}
		}
	}},
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