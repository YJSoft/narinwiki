mark_plugins.push({
	name : '칼럼',
	beforeInsert : function(h) {
		var cols = prompt("몇 칼럼을 만드시겠습니까?");
		if(!cols || cols <= 1 || cols > 10) return '';				
		var openTag = '<columns width="100%">\n';
		var closeTag = '';
		var w = Math.floor(100 / cols);
		var fw = 100 - (w * (cols - 1));
		for(c = 1; c<=cols; c++) {
			if(c == 1) openTag += '<col width="'+fw+'%" style="padding-right:10px" valign="top">\n';			
			else closeTag += (c == 2 ? '\n' : '') + '<col width="'+w+'%" style="padding-right:10px" valign="top">\n';			
		}
		closeTag += '</columns>\n';
		
		// setTimout 사용하지 않고,
		// $.markItUp 을 그냥 호출하면 double print out 됨
		setTimeout(function() { $.markItUp( { openWith : openTag, closeWith : closeTag }); }, 100);
	},
	className : "plugin_columns"
});