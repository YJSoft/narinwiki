<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<script type="text/javascript">
// 글자수 제한
var char_min = parseInt(<?=$write_min?>); // 최소
var char_max = parseInt(<?=$write_max?>); // 최대
</script>

<form name="fwrite" method="post" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data" style="margin:0px;">
<input type=hidden name=null> 
<input type=hidden name=w        value="<?=$w?>">
<input type=hidden name=bo_table value="<?=$bo_table?>">
<input type=hidden name=wr_id    value="<?=$wr_id?>">
<input type=hidden name=sca      value="<?=$sca?>">
<input type=hidden name=sfl      value="<?=$sfl?>">
<input type=hidden name=stx      value="<?=$stx?>">
<input type=hidden name=spt      value="<?=$spt?>">
<input type=hidden name=sst      value="<?=$sst?>">
<input type=hidden name=sod      value="<?=$sod?>">
<input type=hidden name=page     value="<?=$page?>">
<input type=hidden name=wr_doc     value="<?=$wr_doc?>">
<input type=hidden name=wr_subject id="wr_subject" itemname="제목" value="<?=$subject?>">

	<div style="font-weight:bold; font-size:14pt; padding:8px"><?=$title_msg?> : <?=$wr_doc?></div>
	
	<div style="margin:8px 0;padding:5px;border-bottom:1px dashed #ccc;font-size:9pt;">
		문서를 작성하시고 완료 버튼을 클릭하세요.
		위키 문법에 대해서 더 알려면 <a href="http://narin.byfun.com/syntax">위키 문법 학습</a> 페이지를 참고하세요.
	</div>
	
	<table width="100%" class="write_table" border="0" cellspacing="0" cellpadding="0">
	<? if ($is_name) { ?>
	<tr>
	    <th>이 름</th>
	    <td><input class='ed' maxlength=20 size=15 name=wr_name itemname="이름" required value="<?=$name?>"></td>
	</tr>
	<? } ?>
	
	<? if ($is_password) { ?>
	<tr>
	    <th>패스워드</th>
	    <td><input class='ed' type=password maxlength=20 size=15 name=wr_password itemname="패스워드" <?=$password_required?>></td>
	</tr>
	<? } ?>
	
	<? if ($is_email) { ?>
	<tr>
	    <th>이메일</th>
	    <td><input class='ed' maxlength=100 size=50 name=wr_email email itemname="이메일" value="<?=$email?>"></td>
	</tr>
	<? } ?>
	
	<? if ($is_homepage) { ?>
	<tr>
	    <th>홈페이지</th>
	    <td><input class='ed' size=50 name=wr_homepage itemname="홈페이지" value="<?=$homepage?>"></td>
	</tr>
	<? } ?>
	
	<? 
	$option = "";
	$option_hidden = "";
	if ($is_notice || $is_html || $is_secret || $is_mail) { 
	    $option = "";
	    if ($is_notice) { 
	        //$option .= "<input type=checkbox name=notice value='1' $notice_checked>공지&nbsp;";
	    }
	
	    if ($is_html) {
	        if ($is_dhtml_editor) {
	            $option_hidden .= "<input type=hidden value='html1' name='html'>";
	        } else {
	            $option_hidden .= "<input type=hidden value='html1' name='html' $html_checked>";
	        }
	    }
	
	    if ($is_secret) {
	        if ($is_admin || $is_secret==1) {
	            $option .= "<input type=checkbox value='secret' name='secret' $secret_checked><span class=w_title>비밀글</span>&nbsp;";
	        } else {
	            $option_hidden .= "<input type=hidden value='secret' name='secret'>";
	        }
	    }
	    
	    if ($is_mail) {
	        $option .= "<input type=checkbox value='mail' name='mail' $recv_email_checked>답변메일받기&nbsp;";
	    }
	}
	echo $option_hidden;
	if ($option) {
	?>
	<tr>
	    <th>옵 션</th>
	    <td><?=$option?></td>
	</tr>
	<? } ?>
	
	<tr>
	    <th>문서요약</th>
	    <td><input class='ed' name="wr_history" size="80" name=wr_name itemname="문서요약" ></td>
	</tr>
	</table>
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	    <td>
	        <textarea id="wr_content" name="wr_content" class="wr_content tx" style='width:100%;height:350px; word-break:break-all;' itemname="내용" required 
	        <? if ($write_min || $write_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?>><?=$content?></textarea>
	        <? if ($write_min || $write_max) { ?><script type="text/javascript"> check_byte('wr_content', 'char_count'); </script><?}?>
	    </td>
	</tr>
	</table>
	
	<table width="100%" class="write_table" border="0" cellspacing="0" cellpadding="0">        
	<? if ($is_file) { ?>
	<tr>
		<th style="padding-top:10px; line-height:20px;">
		    파일첨부 <br/>
		    <span onclick="add_file();" style="cursor:pointer;"><img src="<?=$board_skin_path?>/img/btn_file_add.gif"></span> 
		    <span onclick="del_file();" style="cursor:pointer;"><img src="<?=$board_skin_path?>/img/btn_file_minus.gif"></span>
		</th>
		<td style='padding:5 0 5 0;'>
		<table id="variableFiles" cellpadding=0 cellspacing=0></table>
	        <script type="text/javascript">
	        var flen = 0;
	        function add_file(delete_code)
	        {
	            var upload_count = <?=(int)$board[bo_upload_count]?>;
	            if (upload_count && flen >= upload_count)
	            {
	                alert("이 게시판은 "+upload_count+"개 까지만 파일 업로드가 가능합니다.");
	                return;
	            }
	
	            var objTbl;
	            var objRow;
	            var objCell;
	            if (document.getElementById)
	                objTbl = document.getElementById("variableFiles");
	            else
	                objTbl = document.all["variableFiles"];
	
	            objRow = objTbl.insertRow(objTbl.rows.length);
	            objCell = objRow.insertCell(0);
	
	            objCell.innerHTML = "<input type='file' class='ed' name='bf_file[]' title='파일 용량 <?=$upload_max_filesize?> 이하만 업로드 가능'>";
	            if (delete_code)
	                objCell.innerHTML += delete_code;
	            else
	            {
	                <? if ($is_file_content) { ?>
	                objCell.innerHTML += "<br><input type='text' class='ed' size=50 name='bf_content[]' title='업로드 이미지 파일에 해당 되는 내용을 입력하세요.'>";
	                <? } ?>
	                ;
	            }
	
	            flen++;
	        }
	
	        <?=$file_script; //수정시에 필요한 스크립트?>
	
	        function del_file()
	        {
	            // file_length 이하로는 필드가 삭제되지 않아야 합니다.
	            var file_length = <?=(int)$file_length?>;
	            var objTbl = document.getElementById("variableFiles");
	            if (objTbl.rows.length - 1 > file_length)
	            {
	                objTbl.deleteRow(objTbl.rows.length - 1);
	                flen--;
	            }
	        }
	        </script>
		</td>
	</tr>
	
	<? } ?>
	
	<? if ($is_trackback) { ?>
	<tr>
	  <th>트랙백주소</th>
	  <td><input class='ed' size=50 name=wr_trackback itemname="트랙백" value="<?=$trackback?>">
	      <? if ($w=="u") { ?><input type=checkbox name="re_trackback" value="1">핑 보냄<? } ?>
		</td>
	</tr>
	<? } ?>
	
	<? if ($is_guest) { ?>
	<tr>
	  <th>보안이미지</th>
	  <td>
	  <input class='ed' type=input size=10 name=wr_key itemname="자동등록방지" required><img id='kcaptcha_image' /><span style="margin-left:85px">왼쪽의 글자를 입력하세요.</span>
	  </td>
	</tr>
	<? } ?>
	
	<tr>
		<td colspan="2" style="text-align:center">
			<span class="button red"><input type=submit id="btn_submit" value="완료" border=0 accesskey='s'></span>&nbsp;
	    <span class="button"><a href="javascript:history.go(-1);" id="btn_back">뒤로</a></span>&nbsp;
	    <span class="button"><a href="<?=$wiki[path]?>/narin.php?bo_table=<?=$wiki[bo_table]?>" id="btn_list">시작페이지</a></span></td>
	  </td>
	 </tr>
	</table>

</form>

<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
<?
// 관리자라면 분류 선택에 '공지' 옵션을 추가함
if ($is_admin) 
{
    echo "
    if (typeof(document.fwrite.ca_name) != 'undefined')
    {
        document.fwrite.ca_name.options.length += 1;
        document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].value = '공지';
        document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].text = '공지';
    }";
} 
?>

with (document.fwrite) 
{
    if (typeof(wr_name) != "undefined")
        wr_name.focus();
    else if (typeof(wr_subject) != "undefined")
        wr_content.focus();
    else if (typeof(wr_content) != "undefined")
        wr_content.focus();

    if (typeof(ca_name) != "undefined")
        if (w.value == "u")
            ca_name.value = "<?=$write[ca_name]?>";
}

function html_auto_br(obj) 
{
    if (obj.checked) {
        result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
        if (result)
            obj.value = "html2";
        else
            obj.value = "html1";
    }
    else
        obj.value = "";
}

function fwrite_submit(f) 
{
    /*
    var s = "";
    if (s = word_filter_check(f.wr_subject.value)) {
        alert("제목에 금지단어('"+s+"')가 포함되어있습니다");
        return false;
    }

    if (s = word_filter_check(f.wr_content.value)) {
        alert("내용에 금지단어('"+s+"')가 포함되어있습니다");
        return false;
    }
    */
	
    if (document.getElementById('char_count')) {
        if (char_min > 0 || char_max > 0) {
            var cnt = parseInt(document.getElementById('char_count').innerHTML);
            if (char_min > 0 && char_min > cnt) {
                alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                return false;
            } 
            else if (char_max > 0 && char_max < cnt) {
                alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                return false;
            }
        }
    }

    if (document.getElementById('tx_wr_content')) {
        if (!ed_wr_content.outputBodyText()) { 
            alert('내용을 입력하십시오.'); 
            ed_wr_content.returnFalse();
            return false;
        }
    }

    var subject = "";
    var content = "";
    $.ajax({
        url: "<?=$wiki[path]?>/exe/ajax.filter.php",
        type: "POST",
        data: {
            "subject": f.wr_subject.value,
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data, textStatus) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (subject) {
        alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
        f.wr_subject.focus();
        return false;
    }

    if (content) { 
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        if (typeof(ed_wr_content) != "undefined") 
            ed_wr_content.returnFalse();
        else 
            f.wr_content.focus();
        return false;
    }

    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    document.getElementById('btn_submit').disabled = true;
    document.getElementById('btn_list').disabled = true;

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/write_update.php';";
    else
        echo "f.action = '$g4[bbs_path]/write_update.php';";
    ?>   
		
		$(window).unbind('beforeunload');    
 
    return true;
}
</script>

<script type="text/javascript" src="<?="$g4[path]/js/board.js"?>"></script>
<script type="text/javascript"> window.onload=function() { drawFont(); } </script>
