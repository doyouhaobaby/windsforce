/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 后台公用($$)*/

// 选择表格行
var selectRowIndex=Array();
var nCurrentId='';

// 获取选择值
function getSelectValue(){
	var obj=document.getElementsByName('key');
	var result='';
	for(var i=0;i<obj.length;i++){
		if(obj[i].checked==true){
			return obj[i].value;
		}
	}

	return false;
}

function getSelectValues(){
	var obj=document.getElementsByName('key');
	var result='';
	var j=0;
	for(var i=0;i<obj.length;i++){
		if(obj[i].checked==true){
			selectRowIndex[j]=i+1;
			result+=obj[i].value+",";
			j++;
		}
	}

	return result.substring(0,result.length-1);
}

// 添加方法
function add(sMore){
	window.location.href=Q.U('add'+(sMore?'?'+sMore:''));
}

function addApp(appId,controller,sMore){
	window.location.href=Q.U('app/config?id='+appId+'&action=add&controller='+controller+(sMore?'&'+sMore:''));
}

// 编辑方法
function edit(id,controller,appId,sMore){
	var keyValue;
	if(id){
		keyValue=id;
	}else{
		keyValue=getSelectValue();
	}

	if(!keyValue){
		windsforceAlert(Q.L('请选择操作项','Common'),'',3);
		return false;
	}

	if(controller){
		window.location.href=Q.U('app/config?id='+appId+'&action=edit&controller='+controller+'&value='+keyValue+(sMore?'&'+sMore:''));
	}else{
		window.location.href=Q.U('edit?id='+keyValue+(sMore?'&'+sMore:''));
	}
}

function editApp(appId,controller,id,sMore){
	edit(id,controller,appId,sMore);
}

// 删除操作
function foreverdel(id,appId,controller,sMore,nDeep){
	var keyValue;
	if(id){
		getSelectValues(id);
		nCurrentId=keyValue=id;
	}else{
		keyValue=getSelectValues();
	}

	if(!keyValue){
		windsforceAlert(Q.L('请选择操作项','Common'),'',3);
		return false;
	}

	windsforceConfirm(Q.L('确实要永久删除选择项吗？','Common'),function(){
		if(controller){
			Q.AjaxSend(Q.U('app/config?action='+(nDeep==1?'foreverdelete_deep':'foreverdelete')),'id='+appId+'&ajax=1'+'&value='+keyValue+'&controller='+controller+(sMore?'&'+sMore:''),'',completeDelete);
		}else{
			Q.AjaxSend(Q.U((nDeep==1?'foreverdelete_deep':'foreverdelete')),'id='+keyValue+'&ajax=1'+(sMore?'&'+sMore:''),'',completeDelete);
		}
	});
}

function foreverdelApp(appId,controller,id,sMore){
	foreverdel(id,appId,controller,sMore);
}

function foreverdelAppDeep(appId,controller,id,sMore){
	foreverdel(id,appId,controller,sMore,1);
}

function foreverdelDeep(id,appId,controller,sMore){
	foreverdel(id,appId,controller,sMore,1);
}

function completeDelete(data,status){
	if(status==1){
		var Table=$WF('checkList');
		var len=selectRowIndex.length;
		if(len==0){
			if(nCurrentId && $('#datalist-'+nCurrentId).length>0){
				$('#datalist-'+nCurrentId).remove();
			}else{
				setTimeout("window.location.replace(_SELF_);",1000);
			}
		}

		for(var i=len-1;i>=0;i--){
			Table.deleteRow(selectRowIndex[i]);
		}

		selectRowIndex=Array();
	}
}

// 清空回收站
function clearRecycle(appId,controller,sMore){
	var sUrl='';
	if(controller){
		sUrl=Q.U('app/config?action=clear_recycle&'+'id='+appId+'&controller='+controller+(sMore?'&'+sMore:''));
	}else{
		sUrl=Q.U('clear_recycle'+(sMore?'?'+sMore:''));
	}

	windsforceConfirm(Q.L('确实要永久删除选择项吗？','Common'),function(){
		Q.AjaxSend(sUrl,'ajax=1','',function(data,status){
			if(status==1){
				setTimeout("window.location.replace(_SELF_);",1000);
			}
		});
	});
}

// 排序
function sort(id){
	var keyValue;
	keyValue=getSelectValues();
	window.location.href=Q.U('sort?sort_id='+keyValue);
}

function sortBy(field,sort){
	window.location.href=Q.U('?order_='+field+'&sort_='+sort,SORTURL);
}

function status_(sAction,id,appId,controller,sMore){
	var sUrl='';
	if(controller){
		sUrl=Q.U('app/config?action='+sAction+'&'+'id='+appId+'&value='+id+'&controller='+controller+(nIsRecycle==1?'&recycle_=1':'')+(sMore?'&'+sMore:''));
	}else{
		sUrl=Q.U(sAction+'?id='+id+(nIsRecycle==1?'&recycle_=1':'')+(sMore?'&'+sMore:''));
	}

	Q.AjaxSend(sUrl,'ajax=1','',function(data,status){
		if(status==1){
			if($('#datalist-'+id).length>0){
				$('#datalist-'+id).remove();
			}else{
				setTimeout("window.location.replace(_SELF_);",1000);
			}
		}
	});
}

function forbid(id,appId,controller,sMore){
	status_('forbid',id,appId,controller,sMore);
}

function forbidApp(appId,controller,id,sMore){
	forbid(id,appId,controller,sMore);
}

function resume(id,appId,controller,sMore){
	status_('resume',id,appId,controller,sMore);
}

function resumeApp(appId,controller,id,sMore){
	resume(id,appId,controller,sMore);
}

// 商家后台
function closeItem(id,appId,controller,sMore){
	status_('closeitem',id,appId,controller,sMore);
}

function openItem(id,appId,controller,sMore){
	status_('openitem',id,appId,controller,sMore);
}

// AJAX添加更新
function addForm(){
	if( $('#'+sSubmitFormId).length>0 && $('#'+sSubmitFormId).val()!='' && $('#'+sSubmitFormId).val()!==null){
		updateForm();
		return;
	}
	$("#submit_button").attr("disabled", "disabled");
	$("#submit_button").val( 'add...' );
	if(currentBeforeFun){
		currentBeforeFun();
	}
	Q.AjaxSubmit(sSubmitFormName,sSubmitFormAddUrl,'',completeForm);
}

function completeForm(data,status){
	$("#submit_button").attr("disabled", false);
	$("#submit_button").val( sSubmitFormAdd );
	if(status==1){
		if($('#'+sSubmitFormId).length>0){
			$('#'+sSubmitFormId).val(data[sSubmitFormKey]);
		}
		if(currentAfterFun){
			currentAfterFun(data,status);
		}
	}
}

function updateForm(){
	$("#submit_button").attr("disabled", "disabled");
	$("#submit_button").val( 'update...' );
	if(currentBeforeFun){
		currentBeforeFun();
	}
	Q.AjaxSubmit(sSubmitFormName,sSubmitFormUpdateUrl,'',function(data,status){ 
		$("#submit_button").attr("disabled", false);
		$("#submit_button").val( sSubmitFormUpdate );
		if(status==1){
			if(currentAfterFun){
				currentAfterFun(data,status);
			}
		}
	});
}

function clickToInput(field,id,appId,controller,unique){
	var idObj=$('#'+field+'_'+id);
	if($('#'+field+'_input_'+id).attr("type")=="text"){
		return false;
	}

	var name=$.trim(idObj.html());
	var m=$.trim(idObj.text());

	idObj.html("<input type='text' value='"+name+"' id='"+field+"_input_"+id+"' title='"+Q.L('点击修改值','Common')+"' >");
	$('#'+field+'_input_'+id).focus();
	$('#'+field+'_input_'+id).blur(function(){
		var n=$.trim($(this).val());
		if(n!=m && n!=""){
			if(!appId){
				Q.AjaxSend(Q.U('input_change_ajax'),'ajax=1&input_ajax_id='+id+'&input_ajax_val='+$('#'+field+'_input_'+id).val()+'&input_ajax_field='+field+(unique?'&unique=1':''),'',clickToInputComplete);
			}else{
				Q.AjaxSend(Q.U('app/config?action=input_change_ajax&id='+appId),'ajax=1&input_ajax_id='+id+'&input_ajax_val='+$('#'+field+'_input_'+id).val()+'&input_ajax_field='+field+'&controller='+controller+(unique?'&unique=1':''),'',clickToInputComplete);
			}
		}else{
			$(this).parent().html(name);
		}
	});
}

function clickToInputApp(field,value,id,controller){
	clickToInput(field,value,id,controller);
}

function clickToInputComplete(data,status){
	if(status==1){
		$('#'+data.id).html(data.value);
	}
}

// 长消息提示
function showMianmenu(){
	$('#main-menu').toggle('fast');
}

function messageDisplay(sClass,sClickClass){
	sClass=sClass || 'message_display';
	sClickClass=sClickClass || 'message-display';

	if($('.'+sClass).css('display')=='block'){
		$('.'+sClickClass).css('display','block');
	}else{
		$('.'+sClickClass).css('display','none');
	}

	$('.'+sClass).toggle();
}

// 后台预览
function viewPreview(id,controller,appId,sMore){
	if($('#adminpreview-'+id).html()!=''){
		$('#adminpreview-'+id).html('');
		return true;
	}

	if(controller){
		sUrl=Q.U('app/config?id='+appId+'&action=view_preview&controller='+controller+'&value='+id+(sMore?'&'+sMore:''));
	}else{
		sUrl=Q.U('view_preview?id='+id+(sMore?'&'+sMore:''));
	}
	
	$('.adminpreview').html('');
	$('#adminpreview-'+id).html('<img src="'+_ROOT_+'/Public/images/common/ajax/loading.gif" />');
	$.ajax({
		type:"POST",
		url:sUrl,
		success:function(str){
			$('#adminpreview-'+id).html(str);
		 }
	});
}

function closePreview(id){
	$('#adminpreview-'+id).html('');
}

function savePreview(id,field,controller,appId){
	if(controller){
		sUrl=Q.U('app/config?id='+appId+'&action=save_preview&controller='+controller+'&value='+id+'&field='+field);
	}else{
		sUrl=Q.U('save_preview?id='+id+'&field='+field);
	}

	$('#preview_field'+field).attr('disabled',true);
	$('#preview_field'+field).val('正在提交');

	$.post(sUrl,{'fieldvalue':$('#'+controller+'_'+field).val()}, function(result){
		if(result.status=='1'){
			$('#adminpreview_successmessage'+field).html('保存成功');
			setTimeout(function(){$('#adminpreview_successmessage'+field).html('');},2000);
			$('#preview_field'+field).removeAttr('disabled');
			$('#preview_field'+field).val('保存');
		}else{
			alert(result.message);
			$('#preview_field'+field).removeAttr('disabled');
			$('#preview_field'+field).val('保存');
		}
	},'json');
}

function savePreviewstatus(id,nStatus,controller,appId){
	if(controller){
		sUrl=Q.U('app/config?id='+appId+'&action=save_previewstatus&controller='+controller+'&value='+id+'&status='+nStatus);
	}else{
		sUrl=Q.U('save_previewstatus?id='+id+'&status='+nStatus);
	}

	sReason=$('#adminpreview_reason').val();
	sOldText=$('#savepreviewbutton_'+nStatus).val();

	if((nStatus==3 || nStatus==2) && $("#adminpreview_reason").length>0 && !sReason){
		windsforceAlert(Q.L('请填写操作理由','Common'),'',3);
		return false;
	}

	$('#savepreviewbutton_'+nStatus).attr('disabled',true);
	$('#savepreviewbutton_'+nStatus).val('Load...');

	$.post(sUrl,{'reason':sReason}, function(result){
		if(result.status=='1'){
			$('#adminpreviewstatus_successmessage').html('操作成功');
			setTimeout(function(){
				$('#adminpreviewstatus_successmessage').html('');
				closePreview(id);
				$('#datalist-'+id).remove();
			},500);
			$('#savepreviewbutton_'+nStatus).removeAttr('disabled');
			$('#savepreviewbutton_'+nStatus).val(sOldText);
		}else{
			alert(result.message);
			$('#savepreviewbutton_'+nStatus).removeAttr('disabled');
			$('#savepreviewbutton_'+nStatus).val(sOldText);
		}
	},'json');
}

function viewCompanymessage(module,id){
	oEditSelect=windsforceAjax(Q.U('public/companymessage?module='+module+'&id='+id),Q.L('管理员提醒','Common'),'','','',400,200,'',0,1);
}
