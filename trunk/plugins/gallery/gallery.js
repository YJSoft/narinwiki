$.fn.wiki_gallery = function() {
	
	var gallery_rendering = function(div_id, json, params) {
		$div = $('#'+div_id);				
		if(json.code != 1) {
			$div.html(json.msg);
			return;
		}
		for(i=0; i<json.files.length; i++) {
			var file = json.files[i];
			var div = $('<div></div>').attr('class', 'gallery_wrap')
																.attr('style', 'display:none')
																.appendTo($div);
			
			$('<a></a>').attr('rel', div_id)
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
				if(params.noext != undefined) {
					name = file.name.replace(/\.[^\.]*$/, '');
				} else name = file.name;
				div.append($('<div class="gallery_name">'+name+'</div>'));
			}
		}
		
		if(json.more == 1) {
			params.page = params.page + 1;
			$link = $('<a></a>').attr('href', 'javascript:;')
													.attr('style', 'display:block;border:1px solid #888;margin:10px auto;padding:5px 10px;width:300px;text-align:center')
													.html('이미지 더보기')
													.click(function(evt) {
															$loading = $('<div style="background:url(plugins/gallery/loading.gif) 10px 40% no-repeat;padding:5px 5px 5px 30px;margin:10px auto;border:1px solid #888; width:170px;">이미지를 불러오는 중입니다.</div>');
															$link.before($loading);
															$.ajax({
																url : wiki_path + '/p.php?bo_table=' + g4_bo_table + '&p=gallery&m=view', 
																data : params, 
																dataType : 'json',
																success : function(json) {
																	$loading.remove();
																	gallery_rendering(div_id, json, params);
																},
																error : function(jqXHR, textStatus, errorThrown) {
																	$loading.attr('background:none').html('<span style="color:red">갤러리 에러 : 이미지 로딩에 실패했습니다.</span>');
																}
															});															
															$link.remove();
													});
			$link.appendTo($div);
		}
	};
	
		
	return this.each(function(idx) {
		var div_id = 'gal_'+ Math.round(Math.random()*1000000);
		$this = $(this);
		$this.attr('id', div_id);		
		var setting = { width:120, sort:'date', paging : 0,page : 1 };
		var params = $.parseJSON($this.html());		
		params = $.extend(setting, params);
		$this.html('<div style="background:url(plugins/gallery/loading.gif) left 40% no-repeat;padding:5px 5px 5px 20px;">이미지를 불러오는 중입니다.</div>').show();
		$.ajax({
			url : wiki_path + '/p.php?bo_table=' + g4_bo_table + '&p=gallery&m=view', 
			data : params, 
			dataType : 'json',
			success : function(json) {
				$('#'+div_id).empty();
				gallery_rendering(div_id, json, params);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				$('#'+div_id).html('<span style="color:red">갤러리 에러 : 이미지 로딩에 실패했습니다.</span>');
			}
		});		
	});
	
};

$(document).ready(function() {	
	$('.wiki_gallery').wiki_gallery();
});
