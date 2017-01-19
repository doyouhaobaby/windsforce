/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 前后台公用($$)*/

function isUndefined(variable){
	return typeof variable=='undefined'?true:false;
}

function isArray(oObj){
	return oObj && !(oObj.propertyIsEnumerable('length')) && typeof oObj==='object' && typeof oObj.length==='number';
}

function in_array(needle,haystack){
	if(typeof needle=='string' || typeof needle=='number'){
		for(var i in haystack){
			if(haystack[i]==needle){
				return true;
			}
		}
	}

	return false;
}

function trim(str){
	return(str+'').replace(/(\s+)$/g,'').replace(/^\s+/g,'');
}

function strlen(str){
	return(Q.Browser.Ie && str.indexOf('\n')!=-1)?str.replace(/\r?\n/g,'_').length:str.length;
}

function $WF(id){
	return document.getElementById(id);
}

function mb_strlen(str){
	var len=0;

	for(var i=0;i<str.length;i++){
		len+=str.charCodeAt(i)<0 || str.charCodeAt(i)>255?(charset=='utf-8'?3:2):1;
	}

	return len;
}

function subStr(str,len,elli){
	if(!str || !len){
		return '';
	}

	if(!elli){
		elli='';
	}

	var a=0;
	var i=0;
	var temp='';
	for(i=0;i<str.length;i++){
		if(str.charCodeAt(i)>255){
			a+=2;
		}else{
			a++;
		}
		if(a>len){
			return temp+elli;
		}
		temp+=str.charAt(i);
	}

	return str;
}

function getObjectClass(obj) {
	if (typeof obj!="object" || obj===null){
		return false;
	}else{
		return /(\w+)\(/.exec(obj.constructor.toString())[1];
	}
}

function preg_replace(search,replace,str,regswitch){
	var regswitch=!regswitch?'ig':regswitch;
	var len=search.length;

	for(var i=0;i<len;i++){
		re=new RegExp(search[i],regswitch);
		str=str.replace(re,typeof replace=='string'?replace:(replace[i]?replace[i]:replace[0]));
	}

	return str;
}

function htmlspecialchars(str){
	return preg_replace(['&','<','>','"'],['&amp;','&lt;','&gt;','&quot;'],str);
}

function stripscript(s){
	return s.replace(/<script.*?>.*?<\/script>/ig,'');
}

function getCurrentStyle(obj,cssproperty,csspropertyNS){
	if(obj.style[cssproperty]){
		return obj.style[cssproperty];
	}

	if(obj.currentStyle){
		return obj.currentStyle[cssproperty];
	}else if (document.defaultView.getComputedStyle(obj,null)){
		var currentStyle=document.defaultView.getComputedStyle(obj,null);
		var value=currentStyle.getPropertyValue(csspropertyNS);
		
		if(!value){
			value=currentStyle[cssproperty];
		}

		return value;
	}else if(window.getComputedStyle){
		var currentStyle = window.getComputedStyle(obj,"");
		return currentStyle.getPropertyValue(csspropertyNS);
	}
}

function fetchOffset(obj,mode){
	var left_offset=0,top_offset=0,mode=!mode?0:mode;

	if(obj.getBoundingClientRect && !mode){
		var rect=obj.getBoundingClientRect();
		var scrollTop=Math.max(document.documentElement.scrollTop,document.body.scrollTop);
		var scrollLeft=Math.max(document.documentElement.scrollLeft,document.body.scrollLeft);
		
		if(document.documentElement.dir=='rtl'){
			scrollLeft=scrollLeft+document.documentElement.clientWidth-document.documentElement.scrollWidth;
		}

		left_offset=rect.left+scrollLeft-document.documentElement.clientLeft;
		top_offset=rect.top+scrollTop-document.documentElement.clientTop;
	}

	if(left_offset<=0 || top_offset<=0){
		left_offset=obj.offsetLeft;
		top_offset=obj.offsetTop;
		while((obj=obj.offsetParent)!= null){
			position=getCurrentStyle(obj,'position','position');
			if(position=='relative'){
				continue;
			}

			left_offset+=obj.offsetLeft;
			top_offset+=obj.offsetTop;
		}
	}

	return {'left':left_offset,'top':top_offset};
}

function showDiv(id){
	try{
		var oDiv=$WF(id);
		if(oDiv){
			if(oDiv.style.display=='none'){
				oDiv.style.display='block';
			}else{
				oDiv.style.display='none';
			}
		}
	}catch(e){}
}

function resizeUp(obj,size){
	size=size || 200;
	var newheight=parseInt($WF(obj).style.height,10)+size;
	$WF(obj).style.height=newheight+'px';
}

function resizeDown(obj,size){
	size=size || 200;
	var newheight=parseInt($WF(obj).style.height,10)-size;
	if(newheight>0){
		$WF(obj).style.height=newheight+'px';
	}
}

function checkSeccode(sValue,id){
	if(!sValue){
		$('#'+id+' .input_errortips label').removeClass().addClass('error');
		$('#'+id+' .input_errortips label').text(Q.L('验证码不能为空','Common'));
		return false;
	}

	if(windsforceAjaxhtml(Q.U('public/validate_seccode?seccode='+encodeURIComponent(sValue)),-1)=='false'){
		$('#'+id+' .input_errortips label').removeClass().addClass('error');
		$('#'+id+' .input_errortips label').text(Q.L('验证码错误','Common'));
		return false;
	}

	$('#'+id+' .input_errortips label').removeClass();
	$('#'+id+' .input_errortips label').text('');
}

function updateSeccode(sFieldbox,sId){
	if(!sFieldbox){
		sFieldbox='seccodeImage';
	}
	if(!sId){
		sId='seccode';
	}

	if($WF(sFieldbox).innerHTML==''){
		$WF(sFieldbox).style.display='block';
		$WF(sFieldbox).innerHTML=Q.L('验证码正在加载中','Common');
	}

	var timenow=new Date().getTime();
	$WF(sFieldbox).innerHTML='<img id="'+sId+'" onclick="updateSeccode(\''+sFieldbox+'\',\''+sId+'\')" src="'+Q.U('public/seccode?update='+timenow)+'" style="cursor:pointer" title="'+Q.L('单击图片换个验证码','Common')+'" alt="'+Q.L('验证码正在加载中','Common')+'" />';
}

function checkAll(str,bThis){
	var i;
	var nLength;
	var inputs=$WF(str).getElementsByTagName("input");
	var nSelect=0;

	if(isUndefined(bThis)){
		var bThis=inputs[0].checked;
		i=1;
		nLength=inputs.length;
	}else{
		i=0;
		nLength=inputs.length;
	}

	for(i=0;i<nLength;i++){
		inputs[i].checked=bThis;

		if(bThis===true){
			nSelect++;
		}else{
			if(nSelect>0){
				nSelect--;
			}
		}
	}

	if(nSelect>0){
		nSelect--;
	}

	return nSelect;
}

function showDistrict(containerboxId,arrOption){
	arrOption=arrOption || [];
	
	var sUrl=_ROOT_+'/index.php?app=home&c=misc&a=district_php';
	for(key in arrOption){
		sUrl+='&'+key+'='+encodeURIComponent(arrOption[key]);
	}

	Q.Ajax.Get(sUrl,'',
		function(xhr,responseText){
			$('#'+containerboxId).html(responseText);
		}
	);
}

function getDistrict(sType,oObj,isname,issite,opensite,opensite_){
	var selectValue=$(oObj).val();// 获取选中值
	var globalDistrictbox=$(oObj).parentsUntil('.global_districtbox').parent();
	var selectCity=$(globalDistrictbox).find('.global_city select');// 获取城市列表的展示区
	var selectDistrict=$(globalDistrictbox).find('.global_district select'); // 获取区列表的展示区
	var selectCommunity=$(globalDistrictbox).find('.global_community select'); // 获取乡列表的展示区 

	if(sType=='city'){
		var currentSelect=selectCity;
		var needOperation=new Array(selectCity,selectDistrict,selectCommunity);
		var sNext='district';
	}else if(sType=='district'){
		var currentSelect=selectDistrict;
		var needOperation=new Array(selectDistrict,selectCommunity);
		var sNext='community';
	}else{
		var currentSelect=selectCommunity;
		var needOperation=new Array(selectCommunity);
		var sNext='';
	}

	var isFirst=$(currentSelect).length==0?0:$(currentSelect).attr('isfirst');
	for(var i=0,l=needOperation.length;i<l;i++){
		if(needOperation[i].length>0){
			$(needOperation[i]).html('');
			$(needOperation[i]).css('display','none');
		}
	}
	
	// 没有参数返回
	if(currentSelect.length=='' || currentSelect.length==0){
		return;
	}
	
	$.ajax({
		url:_ROOT_+'/index.php?app=home&c=misc&a=district&type='+sType,
		data:{
			'value':selectValue,
			'isname':isname
		},
		beforeSend:function(){},
		success:function(data){
			if(data){
				$(needOperation[0]).css('display','inline');
				if(isFirst==1){
					needOperation[0].append('<option value="">- '+Q.L('请选择','Common')+getDistrictType(sType)+' -</option>');
				}
				needOperation[0].append(data);
				
				if(sNext){
					getDistrict(sNext,currentSelect,isname);
				}
			}else{
				if(isFirst==1){
					$(needOperation[0]).css('display','inline');
					needOperation[0].append('<option value="">- '+Q.L('请选择','Common')+getDistrictType(sType)+' -</option>');
				}
			}
		},
		error:function(){}
	});
}

function getDistrictType(nLevel){
	sStr='';

	switch(nLevel){
		case 1:
		case 'province':
			sStr+=Q.L('省份','Common');
			break;
		case 2:
		case 'city':
			sStr+=Q.L('城市','Common');
			break;
		case 3:
		case 'district':
			sStr+=Q.L('州县','Common');
			break;
		case 4:
		case 'community':
			sStr+=Q.L('乡镇','Common');
			break;
	}

	return sStr;
}

function replaceAttachment(sStr){
	return sStr.replace(/\[attachment\]([^\[]*)\[\/attachment\]/ig,
		function(sContentSource,sContent){
			var sUrl='';
			if(sContent.indexOf('http://')>=0 || sContent.indexOf('https://')>=0){
				sUrl=sContent;
			}else{
				sUrl=_ROOT_+'/index.php?app=home&c=misc&a=thumb&editor=1&id='+encodeURIComponent(sContent);
			}

			return '<img src="'+sUrl+'" class="attachment_auto_replace" alt="'+sContent+'" border="0" />'
		}
	);
}

function replaceAttachment_(sStr){
	return sStr.replace(/<img.+?class="attachment_auto_replace".+?alt="(.+?)".*?>/ig,'[attachment]'+'$1'+'[/attachment]');
}

function loadEditor(name,isthin){
	if(isthin==1){
		var arrItemList=['source','|','formatblock','fontname','fontsize','bold','forecolor','hilitecolor','italic','underline',
		'removeformat','link','unlink','image','plainpaste','wordpaste','code','|','fullscreen'];
	}else{
		var arrItemList=['source','|','formatblock','fontname','fontsize','forecolor','hilitecolor','bold','italic','underline',
		'removeformat','justifyleft','justifycenter','justifyright','insertorderedlist',
		'insertunorderedlist','link','unlink','image','flash','plainpaste','wordpaste','code','|','fullscreen'];
	}
	
	var editor=KindEditor.create('textarea[name="'+name+'"]',{
		langType:sEditorLang,
		resizeType:1,
		autoHeightMode : true,
		afterCreate : function() {
			this.loadPlugin('autoheight');
		},
		allowPreviewEmoticons:false,
		allowImageUpload:false,
		allowFlashUpload:false,
		allowMediaUpload:false,
		allowFileManager:false,
		items:arrItemList,
		newlineTag:'<p>'
	});

	editor.html(replaceAttachment(editor.html()));
	return editor;
}

function loadEditorThin(name){
	return loadEditor(name,1);
}

function submitKeycode(e,func){
	var isie=(document.all)?true:false;
	var key;

	if(isie){
		key=window.event.keyCode;
	}else{
		key=e.which;
	}

	if(key==13) {
		func();
	}
}

/** 对话框 */
function windsforceAlert(sContent,sTitle,nTime,ok,cancel,width,height,lock,nobutton,sId){
	if(!sTitle){
		sTitle=Q.L('提示信息','Common');
	}

	if(!ok){
		ok=function(){
			return true;
		}
	}
	
	if(!cancel){
		cancel=function(){
			return true;
		}
	}

	var dialogOption={
		fixed:true,
		title:sTitle,
		content: sContent
	};
	
	if(!nobutton){
		dialogOption.okValue=Q.L('确定','Common');
		dialogOption.ok=ok,
		dialogOption.cancelValue=Q.L('取消','Common');
		dialogOption.cancel=cancel;
	}
	if(sId){
		dialogOption.id=sId;
	}

	var oDialog=$.dialog(dialogOption);

	if(width && height){
		oDialog.size(width,height);
	}
	if(lock!=1){
		oDialog.lock();
	}
	if(nTime){
		oDialog.time(nTime*1000);
	}

	return oDialog;
}

function windsforceConfirm(sContent,ok,cancel,sTitle,nTime,width,height,lock,sId){
	if(!sTitle){
		sTitle=Q.L('提示信息','Common');
	}

	if(!ok){
		ok=function(){
			return true;
		}
	}
	
	if(!cancel){
		cancel=function(){
			return true;
		}
	}

	var oDialog=$.dialog({
		id:sId?sId:'Confirm',
		fixed:true,
		title:sTitle,
		content:sContent,
		okValue: Q.L('确定','Common'),
		ok:ok,
		cancelValue: Q.L('取消','Common'),
		cancel: cancel
	});

	if(width && height){
		oDialog.size(width,height);
	}

	if(lock!=1){
		oDialog.lock();
	}

	if(nTime){
		oDialog.time(nTime*1000);
	}

	return oDialog;
}

/** 通用ajax对话框 */
function windsforceAjaxhtml(sUrl,nCheck,sExtend){
	if(nCheck!=-1){
		nCheck=1;
	}
	
	var sHtml=$.ajax({
		url:sUrl,
		data:sExtend?sExtend:'',
		async:false
	}).responseText;

	if(nCheck==1){
		try{
			arrReturn=eval('('+sHtml+')');
			Q.Message(arrReturn.info,0,2);
			return false;
		}catch(ex){
			return sHtml;
		}
	}else{
		return sHtml;
	}
}

function windsforceAjax(sUrl,sTitle,nTime,ok,cancel,width,height,sExtend,lock,nobutton,sId){
	sHtml=windsforceAjaxhtml(sUrl,1,sExtend);

	if(sHtml===false){
		return;
	}

	if(!width){
		width="400";
	}

	if(!height){
		height="100";
	}

	return windsforceAlert(sHtml,sTitle,nTime,ok,cancel,width,height,lock,nobutton,sId);
}

function windsforceFrame(sUrl,sTitle,nTime,ok,cancel,width,height,sExtend,lock,nobutton,sId){
	sUrl+=sExtend;

	if(!width){
		width="400";
	}

	if(!height){
		height="100";
	}

	var sHtml='<div style="height:'+height+'px; width: '+width+'px; overflow:hidden;"><iframe id="iframe_dialog_ajax" name="iframe_dialog_ajax" frameborder=no scrolling="no"  width="100%" height=100% src="'+sUrl+'"></iframe></div>';
	return windsforceAlert(sHtml,sTitle,nTime,ok,cancel,width,height,lock,nobutton,sId);
}

function checkForm(submitFun,checkRules,checkMessages,errorFun,succesFun){
	checkRules=checkRules || {};
	checkMessages=checkMessages || {};
	submitFun=submitFun || function(){$(".validate")(0).submit();};

	$(document).ready(function(){
		$("form.validate").validate({
			rules: checkRules,
			messages:checkMessages,
			errorPlacement: function(error,element){
				if(!errorFun){
					error.appendTo(element.parent().find('.input_errortips'));
					element.parent().find('.input_tips').css('display','none').removeClass('input_success');
				}else{
					errorFun(error,element);
				}
			},
			submitHandler: function(){
				submitFun();
			},
			success: function(label){
				if(!succesFun){
					label.parent().parent().find('.input_tips').css('display','block').addClass('input_success');
					label.remove();
				}else{
					succesFun(label);
				}
			}
		});
	});
}

/** 媒体对话框 */
var oEditNewattachment;
function globalAddattachment(sFunction,nType,nFull,nMulti){
	/* type：0 所有，1 图片，2附件*/
	if(!nType){
		nType=0;
	}
	if(!nFull){
		nFull=0;
	}
	if(!nMulti){
		nMulti=0;
	}

	var sUrl=_ROOT_+'/index.php?app=home&c=attachment&a=lists&dialog=1&function='+sFunction+'&filetype='+nType+'&full='+nFull+'&multi='+nMulti;
	var sHtml='<div style="height:480px; width: 500px; overflow:hidden;"><iframe id="iframe_dialog" name="iframe_dialog" frameborder=no scrolling="no"  width="100%" height=100% src="'+sUrl+'"></iframe></div>';

	oEditNewattachment=windsforceAlert(sHtml,Q.L('媒体管理器','Common'),'','','',500,480,1,1);
}

function closeAddattachment(){
	oEditNewattachment.close();
}

function addEditorContent(oEditor,sContent){
	if(oEditor.designMode==false){
		windsforceAlert(Q.L('请先切换到所见所得模式','Common'),'',3);
	}else{
		sContent=replaceAttachment(sContent);
		oEditor.insertHtml(sContent);
	}
}

function replaceEditorContent(oEditor,sContent){
	if(oEditor.designMode==false){
		windsforceAlert(Q.L('请先切换到所见所得模式','Common'),'',3);
	}else{
		sContent=replaceAttachment(sContent);
		oEditor.html(sContent);
	}
}

function insertAttachment(editor,nAttachmentid){
	addEditorContent(editor,'[attachment]'+nAttachmentid+'[/attachment]');
}

function insertAttachmentthumb(sId,nAttachmentid){
	$('#'+sId).val(nAttachmentid);
}

var oEditNewmusic='';
function addMusic(sFunction){
	oEditNewmusic=windsforceAjax(_ROOT_+'/index.php?app=home&c=misc&a=music&function='+sFunction,Q.L('插入音乐','Common'),'','','',500,100,'',0,1);
}

function insertMusic(editor,sContent){
	if(!sContent){
		windsforceAlert(Q.L('音乐地址不能够为空','Common'),'',3);
		return false;
	}

	sContent='[mp3]'+sContent+'[/mp3]';
	addEditorContent(editor,sContent);
	oEditNewmusic.close();
}

var oEditNewvideo='';
function addVideo(sFunction){
	oEditNewvideo=windsforceAjax(_ROOT_+'/index.php?app=home&c=misc&a=video&function='+sFunction,Q.L('插入视频','Common'),'','','',500,100,'',0,1);
}

function insertVideo(editor,sContent){
	if(!sContent){
		windsforceAlert(Q.L('视频地址不能够为空','Common'),'',3);
		return false;
	}

	sContent='[video]'+sContent+'[/video]';
	addEditorContent(editor,sContent);
	oEditNewvideo.close();
}

/** 普通文本框插入附件 && 需要Public/js/jquery/jquery.insertcontent.js */
function insertContentNormal(id,sContent){
	$('#'+id).insertAtCaret(sContent);
}

function insertAttachmentNormal(id,nAttachmentid){
	insertContentNormal(id,'[attachment]'+nAttachmentid+'[/attachment]');
}

function insertVideoNormal(id,sContent){
	if(!sContent){
		windsforceAlert(Q.L('视频地址不能够为空','Common'),'',3);
		return false;
	}

	insertContentNormal(id,'[video]'+sContent+'[/video]');
	oEditNewvideo.close();
}

function insertMusicNormal(id,sContent){
	if(!sContent){
		windsforceAlert(Q.L('音乐地址不能够为空','Common'),'',3);
		return false;
	}
	
	insertContentNormal(id,'[mp3]'+sContent+'[/mp3]');
	oEditNewmusic.close();
}

/** 通用插入 */
var sCurrentTextareaContent='';
function insertAttachment_(nAttachmentid){
	if(sCurrentTextareaContent){
		insertAttachmentNormal(sCurrentTextareaContent,nAttachmentid);
	}else{
		insertAttachment(editor,nAttachmentid);
	}
}

function insertMusic_(sContent){
	if(sCurrentTextareaContent){
		insertMusicNormal(sCurrentTextareaContent,sContent);
	}else{
		insertMusic(editor,sContent);
	}
}

function insertVideo_(sContent){
	if(sCurrentTextareaContent){
		insertVideoNormal(sCurrentTextareaContent,sContent);
	}else{
		insertVideo(editor,sContent);
	}
}

/** 通用组图插入 */
function getRandomNum(nMin,nMax){
	var nRange=nMax-nMin;
	var nRand=Math.random();
	return(nMin+Math.round(nRand*nRange));
}

function insertPics(nAttachmentid,sIdbox){
	sKey=getRandomNum(10000,50000);
	$('#'+sIdbox).append(
		'<tr id="'+sIdbox+'-'+sKey+'">'+
			'<td>'+
				'<input type="text" name="sorts[]" id="sorts_'+sKey+'" size="40" value="999" class="{required:true,digits:true,min:0,max:999}" style="width:50px;" title="序号"/>'+
			'</td>'+
			'<td>'+
				'<input type="text" name="titles[]" id="titles_'+sKey+'" size="40" value="" class="" style="width:250px;" title="标题"/>'+
			'</td>'+
			'<td>'+
				'<input type="text" name="pics[]" id="pics_'+sKey+'" size="40" value="'+nAttachmentid+'" class="{required:true}" style="width:400px;" title="文件" readonly="true" onmouseover="globalPreviewImg(\'pics_'+sKey+'\');" onmouseout="unglobalPreviewImg(\'pics_'+sKey+'\');"/>'+
				'<div id="pics_'+sKey+'-preview"></div>'+
			'</td>'+
			'<td>'+
				'<a href="javascript:void(0);" onclick="deletePics(\''+sKey+'\',\''+sIdbox+'\');" class="red">x</a>'+
			'</td>'+
		'<tr>'
	);
}

function deletePics(sKey,sIdbox){
	$('#'+sIdbox+'-'+sKey).remove();
}

/** 百度地图 */
var oEditSelectBaidumap;
function selectBaidumap(sContainerId){
	oEditSelectBaidumap=windsforceFrame(_ROOT_+'/index.php?app=home&c=misc&a=baidumap&boxid='+sContainerId+'&pointer='+encodeURIComponent($('#'+sContainerId).val()),Q.L('百度地图','Common'),'','','',620,470,'',0,1);
}

function closeBaidumap(){
	oEditSelectBaidumap.close();
}

/** 通用readonly图片预览 */
function globalPreviewImg(sId){
	var sUrl=$('#'+sId).val();
	if(sUrl){
		if(sUrl.indexOf('http://')<0 && sUrl.indexOf('https://')<0){
			sUrl=_ROOT_+'/user/attachment/'+sUrl;
		}
		$('#'+sId+'-preview').html('<img src="'+sUrl+'" />');
	}
}

function unglobalPreviewImg(sId){
	$('#'+sId+'-preview').html('');
}

/** 刷新页面缓存 */
function refreshCache(sUrl){
	$.get(sUrl,'update_cache=1',function(response,status,xhr){
		window.setTimeout(function (){window.location.reload();},1000);
	});
}
