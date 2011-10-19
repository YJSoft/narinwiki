
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
	markupSet:  mark_set
}	

// 글쓰기의 에디터 툴바
$(document).ready(function() {		
	if(!is_comment) {
		$('.wr_content').markItUp(narinWikiSettings);	
		addKeyEvent($('.wr_content'));
	}
});	

function addKeyEvent(txtElement) {
		$(txtElement).keydown(function(e) {
	    if(e.which != 13 &&
	       e.which != 8  &&
	       e.which != 32) return;
	    var field     = $(this).get(0);
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
		});
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
