$.fn.wiki_gallery = function() {
	
	var gallery_rendering = function($div, json, params) {
		var rand = Math.round(Math.random()*1000000);
		$div.show();
		var alist = [];
		for(i=0; i<json.length; i++) {
			var file = json[i];
			var div = $('<div></div>').attr('class', 'gallery_wrap')
																.attr('style', 'display:none')
																.appendTo($div);
			
			$('<a></a>').attr('rel', 'gal_'+rand)
								  .attr('class', 'gallery_modal')
								  .attr('href', file.href)
								  .attr('title', file.name + '('+file.width+'x'+file.height+', ' + file.filesize + ')')
								  .append($('<img/>').attr('src', file.thumb)
								  									.attr('title', file.name)
								  									.load(function() {
								  										$(this).parent().parent().attr('style', 'display:inline-block;zoom:1;*display:inline');
								  									}))
									.wiki_lightbox({showCloseButton : true})
									.appendTo(div);
										
			if(params.showname != undefined) {
				div.append($('<div class="gallery_name">'+file.name+'</div>'));
			}									
		
						
		}
	};
	
	return this.each(function() {
		$this = $(this);
		var setting = { width:120, sort:'date' };
		var params = $.parseJSON($this.html());		
		params = $.extend(setting, params);
		$this.empty();
		$.ajax({
			url : wiki_path + '/exe/a.php?bo_table=' + g4_bo_table + '&w=plugin&p=gallery&m=view', 
			data : params, 
			dataType : 'json',
			success : function(json) {
				gallery_rendering($this, json, params);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$this.html('<span style="color:red">갤러리 에러 : 이미지 로딩에 실패했습니다.</span>');
			}
		});		
	});
	
};

$(document).ready(function() {	
	$('.wiki_gallery').wiki_gallery();
});
