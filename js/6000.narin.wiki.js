
$(document).ready(function() {
	$(".wiki_content img").removeAttr("onclick");
	$(".wiki_content img").each(function() {
		if($(this).parent().get(0).tagName != "A")
			$(this).wrap("<a href='"+$(this).attr('src')+"' class='wiki_modal'></a>");
	});

	$.nmAnims({
		fade: {
			showBg: function(nm, clb) {
				nm.elts.bg.fadeTo(250, 0.35, clb);
			}
    }
  });		

	$(".wiki_modal").nm();
  
  $("#toc_fold").click(function(evt) {
  	evt.preventDefault();
  	$("#wiki_toc_content").slideToggle();
  	head = $("#wiki_toc_head a");
  	if(head.hasClass("fold_up")) {
  		head.removeClass("fold_up").addClass("fold_down");
  	} else head.removeClass("fold_down").addClass("fold_up");
  });  
  
  $(".button a, .button input").click(function() {
  	$(this).blur();
  }); 

	// 글쓰기의 에디터 툴바
	if(!is_comment) {
		$('.wr_content').narinEditor('wiki_write');
	}
    
});


/**
 * @param docname 경로는 제외한 문서명
 */
function check_doc_name(docname, hidemsg)
{
	if($.trim(docname) == '') {
		if(!hidemsg) alert("문서명을 입력하세요");
		return false;
	}	
	var pattern = /[\|\/\\\\]/;
	if(pattern.test(docname)) {
		if(!hidemsg) alert("문서명에 다음 문자는 사용할 수 없습니다 : \\, |, /");
		return false;
	}		
	return true;
}

/**
 * @param foldername 폴더 경로 전체
 */
function check_folder_name(foldername, hidemsg)
{
	if($.trim(foldername) == '') {
		if(!hidemsg) alert("폴더명을 입력하세요");
		return false;
	}
	var pattern = /[\|\\\\]/;
	if(pattern.test(foldername)) {
		if(!hidemsg) alert("폴더명에 다음 문자는 사용할 수 없습니다 : \\, |");
		return false;
	}
	
	pattern = /[\/]{2,}/;
	if(pattern.test(foldername)) {
		if(!hidemsg) alert("폴더명에 / 를 연속하여 사용할 수 없습니다.");
		return false;
	}	
	
	pattern = /^\//;
	if(!pattern.test(foldername)) {
		if(!hidemsg) alert("폴더명은 / 로 시작하는 문자열 이어야 합니다.");
		return false;
	}	
	
	return true;
}

function recover_history(wr_id, hid)
{
	if(confirm("이 문서로 복원하시겠습니까?")) {
		$.post(wiki_path+"/exe/history.php", { bo_table : g4_bo_table, w : 'r', wr_id : wr_id, hid : hid}, function(data) {
			if(data == 1) {
				location.href = g4_url + "/" + g4_bbs + "/board.php?bo_table=" + g4_bo_table + "&wr_id=" + wr_id;
			} else {
				alert("문서 이력 복원를 못하였습니다.");
			}
		});
	}
}

function delete_history(hid)
{
	if(confirm("이 문서 이력을 삭제하시겠습니까?")) {
		$.post(wiki_path+"/exe/history.php", { bo_table : g4_bo_table, w : 'd', hid : hid}, function(data) {
			if(data == 1) {
				location.reload();
			} else {
				alert("문서 이력 삭제를 못하였습니다.");
			}
		});
	}
}

function clear_history(wr_id)
{
	if(confirm("이 문서의 모든 이력을 삭제하시겠습니까?\n페이지에 표시되지 않는 이력도 삭제됩니다.")) {
		$.post(wiki_path+"/exe/history.php", { w : 'da', bo_table : g4_bo_table, wr_id : wr_id}, function(data) {
			if(data == 1) {
				location.href = g4_url + "/" + g4_bbs + "/board.php?bo_table=" + g4_bo_table + "&wr_id=" + wr_id;
			} else {
				alert("문서 이력 삭제를 못하였습니다.");
			}
		});
	}	
}

function delete_selected_history(wr_id)
{
	var hids = [];
	var chks = $("input[@name='hid[]']:checked").map(function() {
		hids.push(this.value);
	});
	
  if (hids.length == 0) 
  {
      alert("문서를 하나 이상 선택하세요.");
      return false;
  }	
	
	if(confirm("선택한 문서 이력을 삭제하시겠습니까?")) {		
		$.post(wiki_path+"/exe/history.php", { w : 'ds', bo_table : g4_bo_table, wr_id : wr_id, hids : hids}, function(data) {
			if(data == 1) {
				location.reload();
			} else {
				alert("문서 이력 삭제를 못하였습니다.");				
			}
		});
	}	
}

function delete_selected_changes()
{
	var cids = [];
	var chks = $("input[@name='cid[]']:checked").map(function() {
		cids.push(this.value);
	});
	
  if (cids.length == 0) 
  {
      alert("변경내역을 하나 이상 선택하세요.");
      return false;
  }	
	
	if(confirm("선택한 변경내역을 삭제하시겠습니까?")) {		
		$.post(wiki_path+"/exe/changes.php", { w : 'ds', bo_table : g4_bo_table, cids : cids}, function(data) {
			if(data == 1) {
				location.reload();
			} else {
				alert("변경내역 삭제를 못하였습니다.");				
			}
		});
	}	
}

function clear_changes()
{
	if(confirm("모든 변경내역을 삭제하시겠습니까?\n페이지에 표시되지 않는 내역도 모두 삭제됩니다.")) {
		$.post(wiki_path+"/exe/changes.php", { w : 'da', bo_table : g4_bo_table}, function(data) {
			if(data == 1) {
				location.reload();
			} else {
				alert("변경내역 삭제를 못하였습니다.");
			}
		});
	}	
}

function createDoc(folder)
{
	if(!check_folder_name(folder)) return;
	var doc = prompt('문서명 입력 : ', '');
	if(doc != null) {
		if(!check_doc_name(doc)) {
			createDoc(g4_bo_table, folder, wiki_path);
			return;
		}
		docpath = ( folder == "/" ? "/" : folder+"/") + doc;
		location.href = wiki_path + "/narin.php?bo_table="+g4_bo_table+"&doc=" + encodeURIComponent(docpath);
	}
}

function wiki_search(f)
{
	if($.trim(f.stx.value) == '') {
		alert("검색어를 입력하세요");
		return false;
	}
	return true;
}
