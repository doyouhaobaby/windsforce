/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 新鲜事评论AJAX提交($$)*/

/** 前端验证 */
function commentCheckForm(comment_content,customContent){
	var comment_name=$.trim($('#homefreshcomment_name').val());
	if(customContent==1){
		comment_content=$.trim($("#homefreshcomment_content").val());
	}

	if(nDisplayCommentSeccode==1){
		var seccode=$('#homefreshcomment_seccode').val();
		if(!seccode){
			windsforceAlert(Q.L('验证码不能为空','Common'),Q.L('评论发生错误','Common'),3);
			return false;
		}

		if(windsforceAjaxhtml(Q.U('home://public/validate_seccode?seccode='+encodeURIComponent(seccode)),-1)=='false'){
			windsforceAlert(Q.L('验证码错误','Common'),Q.L('评论发生错误','Common'),3);
			return false;
		}
	}
	
	if(comment_name==""){
		windsforceAlert(Q.L('评论名字不能为空','Common'),Q.L('评论发生错误','Common'),3);
		return false;
	}

	if(comment_name.length>25){
		windsforceAlert(Q.L('评论名字长度只能小于等于25个字符串','Common'),Q.L('评论发生错误','Common'),3);
		return false;
	}

	if(comment_content == ""){
		windsforceAlert(Q.L('评论内容不能为空','Common'),Q.L('评论发生错误','Common'),3);
		return false;
	}

	return true;
}

/** 评论浏览页面&提交评论 */
function commentSubmit(){
	var bResult=commentCheckForm();
	if(bResult===false){
		return false;
	}

	$("#comment-submit").val(Q.L('正在提交评论','Common'));
	$("#comment-submit").attr("disabled", "disabled");
	Q.AjaxSubmit('homefresh-commentform',Q.U('home://ucenter/add_homefreshcomment'),'',commentComplete);
}

function commentComplete(data,status){
	$("#comment-submit").attr("disabled", false);
	$("#comment-submit").val(Q.L('提交评论','Common'));
	if(status==1){
		setTimeout(function(){window.location.href=data.jumpurl;},1000);
	}
}

/** 我也来说一句&提交评论 */
function goodnum(id){
	Q.AjaxSend(Q.U('home://ucenter/update_homefreshgoodnum'),'ajax=1&id='+id,'',function(data,status,info){
		if(status==1){
			$('#goodnum_'+id).text(data.num);
			$('#goodnum_'+id).addClass('goodnum_click');
		}
	});
}

var nCurrentHomefreshid='';
var nCurrentHomefreshcommentid='';
var nCurrentHomefreshchildcommentid='';
var bCurrentHomefreshcommentopen=false;
var sCommentSeccode='';

function commentForm(id){
	if(nDisplayCommentSeccode==1){
		$('#homefreshcomment_seccode .input_errortips label').removeClass();
		$('#homefreshcomment_seccode .input_errortips label').text('');
	}
	
	if($("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
			$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
			$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');
			$("#homefreshcommentform_"+nCurrentHomefreshid).html('');
			homefreshcommentSwitchform(id);
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}

	if($("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
			bCurrentHomefreshcommentopen=false;
			homefreshcommentSwitchform(id);
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}

	if(bCurrentHomefreshcommentopen===true){
		homefreshchildcommentCancel();
	}

	$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
	$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
	$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');
	$("#homefreshcommentform_"+nCurrentHomefreshid).html('');
	homefreshcommentSwitchform(id);

	return true;
}

function setSeccode(sValue){
	sCommentSeccode=sValue;
}

function homefreshcommentSwitchform(id){
	$("#homefreshcommentdiv_"+id).css("display","none");
	$("#homefreshcommentform_"+id).css("display","block");
	$("#homefreshcommentform_"+id).html($("#homefreshcommentform_box").html());
		
	$('.homefreshcommentform_area').autoResize({
		onResize:function(){
			$(this).css({opacity:0.8});
		},
		animateCallback:function(){
			$(this).css({opacity:1});
		},
		animateDuration:300,
		extraSpace:0,
		min:'80px'
	});

	$(".homefreshcommentform_area").focus();
	nCurrentHomefreshid=id;

	showEmotion();
}

function homefreshcommentCancel(){
	if(bCurrentHomefreshcommentopen===true){
		homefreshchildcommentCancel();
		return false;
	}
	
	if($("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
			$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
			$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}

	$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
	$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
	$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');

	return true;
}

function homefreshcommentSubmit(){
	var comment_name=$.trim($('#homefreshcomment_name').val());
	var comment_email=$.trim($("#homefreshcomment_email").val());
	var comment_url=$.trim($("#homefreshcomment_url").val());
	var comment_parentid=$.trim($("#homefreshcomment_parentid").val());

	$("#homefreshcomment-submit").val(Q.L('正在提交评论','Common'));
	$("#homefreshcomment-submit").attr("disabled", "disabled");

	if(comment_parentid>0){
		var value=$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val();
	}else{
		var value=$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val();
	}
	
	var bResult=commentCheckForm(value);
	if(bResult===false){
		$("#homefreshcomment-submit").attr("disabled", false);
		$("#homefreshcomment-submit").val(Q.L('提交','Common'));
		return false;
	}

	var sUrlParameter="ajax=1&quick=1&homefreshcomment_content="+encodeURIComponent(value)+
		"&homefresh_id="+nCurrentHomefreshid+'&homefreshcomment_name='+encodeURIComponent(comment_name)+
		'&homefreshcomment_email='+encodeURIComponent(comment_email)+'&homefreshcomment_url='+
		encodeURIComponent(comment_url)+'&homefreshcomment_parentid='+comment_parentid+'&seccode='+sCommentSeccode;

	Q.AjaxSend(Q.U('home://ucenter/add_homefreshcomment'),sUrlParameter,'',function(data,status){
		$("#homefreshcomment-submit").attr("disabled", false);
		$("#homefreshcomment-submit").val(Q.L('提交','Common'));
		
		if(status==1){
			var sCommentReply='<div class="homefreshcomment_item" id="homefreshcommentitem_'+data.homefreshcomment_id+'">'+
					'<div class="homefreshcomment_avatar">'+
						'<img src="'+data.avatar+'" class="thumbnail"/>'+
					'</div>'+
					'<div class="homefreshcomment_content"><p>'+
						data.usericon+'&nbsp;<a href="'+data.url+'">'+data.comment_name+'</a>:'+data.homefreshcomment_content+'</p><p>'+
						'<em class="homefreshcomment_date">'+data.create_dateline+'</em>&nbsp;<img class="new_data" src="'+_ROOT_+'/Public/images/common/new.gif" />'+
						'<span class="pipe">|</span>';

			sCommentReply+='<a href="'+data.viewurl+'" target="_blank">'+Q.L('查看','App')+'</a>&nbsp;';
			
			if(nParentHomefreshcommentChild==1){
				if(comment_parentid>0){
					sCommentReply+='<a href="javascript:void(0);" onclick="childcommentForm(\''+data.homefresh_id+'\',\''+comment_parentid+'\',\'1\',\''+data.comment_name+'\');">'+Q.L('回复','App')+'</a>';
				}else{
					sCommentReply+='<a href="javascript:void(0);" onclick="childcommentForm(\''+data.homefresh_id+'\',\''+data.homefreshcomment_id+'\');">'+Q.L('回复','App')+'</a></p>'+
						'<div id="homefreshchildcommentlist_'+data.homefreshcomment_id+'" class="homefreshchildcommentlist_box">'+
						'</div>'+
						'<div id="homefreshchildcommentform_'+data.homefreshcomment_id+'" class="homefreshcomment_form">'+
						'</div>';
				}
			}
			
			sCommentReply+='</p></div>'+
				'</div>'+
				'<div class="clear"></div>';
			
			if(comment_parentid>0){
				$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
				$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
				$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
				$("#homefreshchildcommentlist_"+nCurrentHomefreshcommentid).append(sCommentReply);
				bCurrentHomefreshcommentopen=false;
			}else{
				$('#homefreshlist_item_'+data.homefresh_id+' .homefreshcommentlist_headerarrow').css("display","block");
				$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
				$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
				$("#homefreshcommentform_"+nCurrentHomefreshid).html("");
				$("#homefreshcommentlist_"+nCurrentHomefreshid).append(sCommentReply);
			}

			$("#homefreshcomment_"+nCurrentHomefreshid).html(data.num);
			$('#homefreshcommentitem_'+data.homefreshcomment_id+' .homefreshcomment_content').emotionsToHtml();
		}
	});
}

/** 子评论提交 */
function childcommentForm(id,commentid,childComment,username,childcommentid){
	if(childcommentid==nCurrentHomefreshchildcommentid && commentid==nCurrentHomefreshcommentid && bCurrentHomefreshcommentopen===true){
		homefreshchildcommentCancel();
		return false;
	}

	if($("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
			$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
			$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');
			$("#homefreshcommentform_"+nCurrentHomefreshid).html('');
			homefreshchildcommentSwitchform(id,commentid,username,childcommentid);
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}
	
	if($("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
			homefreshchildcommentSwitchform(id,commentid,username,childcommentid);
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}

	$("#homefreshcommentdiv_"+nCurrentHomefreshid).css("display","block");
	$("#homefreshcommentform_"+nCurrentHomefreshid).css("display","none");
	$("#homefreshcommentform_"+nCurrentHomefreshid+' .homefreshcommentform_area').val('');
	$("#homefreshcommentform_"+nCurrentHomefreshid).html('');
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
	homefreshchildcommentSwitchform(id,commentid,username,childcommentid);
	return true;
}

function childcommentAt(commentid,username){
	$("#homefreshchildcommentform_"+commentid+' .homefreshcommentform_area').insertAtCaret('@'+username+' ');
}

function homefreshchildcommentSwitchform(id,commentid,username,childcommentid){
	$("#homefreshcommentform_box").css("display","none");
	$("#homefreshchildcommentform_"+commentid).css("display","block");
	$("#homefreshchildcommentform_"+commentid).html($("#homefreshcommentform_box").html());
	$('.homefreshcommentform_area').autoResize({
		onResize:function(){
			$(this).css({opacity:0.8});
		},
		animateCallback:function(){
			$(this).css({opacity:1});
		},
		animateDuration:300,
		extraSpace:0,
		min:'80px'
	});

	$(".homefreshcommentform_area").focus();
	nCurrentHomefreshid=id;
	nCurrentHomefreshcommentid=commentid;
	nCurrentHomefreshchildcommentid=childcommentid;
	bCurrentHomefreshcommentopen=true;
	$('#homefreshcomment_parentid').val(commentid);
	if(username){
		childcommentAt(commentid,username);
	}

	showEmotion();
}

function homefreshchildcommentCancel(){
	if($('#homefreshchildcommentform_'+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val()){
		windsforceConfirm(Q.L('你确定要放弃正在编辑的评论?','App'),function(){
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
			$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
			if(nHomefreshviewcomment==1){
				$("#homefreshcommentform_box").css("display","block");
			}
			bCurrentHomefreshcommentopen=false;
			return true;
		},function(){
			$(".homefreshcommentform_area").focus();
			return true;
		});

		return false;
	}
	
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).css("display","none");
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid+' .homefreshcommentform_area').val('');
	$("#homefreshchildcommentform_"+nCurrentHomefreshcommentid).html('');
	if(nHomefreshviewcomment==1){
		$("#homefreshcommentform_box").css("display","block");
	}
	
	bCurrentHomefreshcommentopen=false;
	return true;
}

function homefreshcommentAudit(nCommentid,nStatus){
	Q.AjaxSend(Q.U('home://ucenter/audit_homefreshcomment?id='+nCommentid+'&status='+nStatus),'ajax=1','',function(data,status){
		if(status==1){
			setTimeout("window.location.replace(_SELF_);",1000);
		}
	});
}

/** 子评论分页 */
$oGlobalBody=(window.opera)?(document.compatMode=="CSS1Compat"?$('html'):$('body')):$('html,body');

function homefreshcommentAjaxpage(nHomefreshcomentId){
	$(function(){
		$('#pagination_'+nHomefreshcomentId+' a').live('click',function(e){
			e.preventDefault();
			$.ajax({
				type: "GET",
				url: $(this).attr('href'),
				beforeSend: function(){
					$('#pagination_'+nHomefreshcomentId).empty();
					$('#homefreshchildcommentlist_'+nHomefreshcomentId).empty();
					$('#loadinghomefreshchildcomments_'+nHomefreshcomentId).slideDown();
					$oGlobalBody.animate({scrollTop: $('#homefreshchildcommentlistheader_'+nHomefreshcomentId).offset().top-65},800);
				},
				dataType: "html",
				success: function(out){
					oResult=$(out).find('#homefreshchildcommentlist_'+nHomefreshcomentId);
					sNextlink=$(out).find('#pagination_'+nHomefreshcomentId);
					$('#loadinghomefreshchildcomments_'+nHomefreshcomentId).slideUp('fast');

					if($.trim($('#homefreshchildcommentlist_'+nHomefreshcomentId).html())==''){
						$('#homefreshchildcommentlist_'+nHomefreshcomentId).append(oResult);
						$('#pagination_'+nHomefreshcomentId).html(sNextlink);
					}
				}
			});
		});
	});
}
