/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Group应用帖子操作($$)*/

/** 公用代码 */
function replaceIdcontent(id,content){
	$('#'+id).val(content);
}

function dataidChecked(oObj){
	if(oObj.checked){
		try{
			var oInput=document.createElement('<input name="topiclist[]" />');
		}catch(e){
			try{
				var oInput=document.createElement('input');
				oInput.name='topiclist[]';
			}catch(e){
				return;
			}
		}

		oInput.id='topiclist_'+oObj.value;
		oInput.value=oObj.value;
		oInput.type='hidden';
		$WF('modActionsBox').appendChild(oInput);
	}else{
		$WF('modActionsBox').removeChild($WF('topiclist_'+oObj.value));
	}
}

var nModclickcount=0;
function modClick(oObj,nDataid){
	if(oObj.checked){
		nModclickcount++;
	}else{
		nModclickcount--;
	}

	$WF('modActionSelectnum').innerHTML=nModclickcount;

	if(nModclickcount>0){
		var arrOffset=fetchOffset(oObj);
		$WF('modActionSelect').style.top=arrOffset['top']-65+'px';
		$WF('modActionSelect').style.left=arrOffset['left']-330+'px';
		$WF('modActionSelect').style.display='';
	}else{
		$WF('modActionSelect').style.display='none';
	}
}

function tmodClick(oObj){
	if(oObj.checked){
		nModclickcount++;
	}else{
		nModclickcount--;
	}

	$WF('modActionSelectnum').innerHTML=nModclickcount;

	if(nModclickcount>0){
		var nTopoffset=oObj.offsetTop;
		while((oObj=oObj.offsetParent).id!='topic_list_box') {
			nTopoffset+=oObj.offsetTop;
		}

		$WF('modActionSelect').style.top=nTopoffset-7+'px';
		$WF('modActionSelect').style.display='';
	}else{
		$WF('modActionSelect').style.display='none';
	}
}

function getTopiclist(){
	var arrTopiclist=new Array();
	var nCount=0;

	for(var nI=0;nI<$WF('modActionsBox').elements.length;nI++){
		if($WF('modActionsBox').elements[nI].name.match('topiclist')){
			arrTopiclist[nCount]=$WF('modActionsBox').elements[nI].value;
			nCount++;
		}
	}

	return arrTopiclist;
}

/** 主题批量管理 */
function tmodTopics(sOperation){
	var arrTopiclist=new Array();
	var nCount=0;
	var sOperation=!sOperation?'':sOperation;

	for(var nI=0;nI<$WF('modActionsBox').elements.length;nI++){
		if($WF('modActionsBox').elements[nI].name.match('moderate') && $WF('modActionsBox').elements[nI].checked){
			arrTopiclist[nCount]=$WF('modActionsBox').elements[nI].value;
			nCount++;
		}
	}
	
	nGrouptopicid=arrTopiclist;
	modTopiccommon(sOperation);
}

/** 帖子管理通用操作 */
var sCurrentAction='';
function modTopiccommon(sAction,sExtra){
	var sUrl=Q.U('group://grouptopicadmin/'+sAction+'_dialog?groupid='+nGroupid+'&grouptopicid='+nGrouptopicid+(!sExtra?'':'&'+sExtra));
	sCurrentAction=sAction;

	var nTempnum=1;
	if(isArray(nGrouptopicid)){
		nTempnum=nGrouptopicid.length;
	}

	oCommonNewmodtopics=windsforceAjax(sUrl,Q.L('你选择了 %d 篇帖子','App',nTempnum),'',modTopiccommontopic,'',400,100);
}

function modTopiccommontopic(){
	Q.AjaxSubmit('moderateform',Q.U('group://grouptopicadmin/'+sCurrentAction),'result',commonComplete);
	return false;
}

function commonComplete(data,status){
	if(status==1){
		if(sCurrentAction=='deletetopic'){
			setTimeout(function(){window.location.href=data.group_url;},1000);
		}else{
			setTimeout("window.location.replace(_SELF_);",1000);
		}
	}

	return false;
}

/** 删除主题 */
function modTopicdelete(){
	modTopiccommon('deletetopic');
}

/** 关闭或者打开主题 */
function modTopicclose(nStatus){
	modTopiccommon('closetopic','status='+nStatus);
}

/** 置顶或者取消置顶主题 */
function modTopicstick(nStatus){
	modTopiccommon('sticktopic','status='+nStatus);
}

/** 精华或者取消精华主题 */
function modTopicdigest(nStatus){
	modTopiccommon('digesttopic','status='+nStatus);
}

/** 推荐或者取消推荐主题 */
function modTopicrecommend(nStatus){
	modTopiccommon('recommendtopic','status='+nStatus);
}

/** 隐藏或者显示主题 */
function modTopicstatus(nStatus){
	modTopiccommon('hidetopic','status='+nStatus);
}

/** 设置主题分类 */
function modTopiccategory(nCategoryid){
	modTopiccommon('categorytopic','category_id='+nCategoryid);
}

/** 设置主题标签 */
function modTopictag(){
	modTopiccommon('tagtopic');
}

/** 设置主题高亮 */
function modTopiccolor(){
	modTopiccommon('colortopic');
}

/** 提升或者下沉主题 */
function modTopicup(){
	modTopiccommon('uptopic');
}

/** 移动主题 */
function modTopicmove(){
	modTopiccommon('movetopic');
}

/** 主题回帖通用代码管理 */
var sCurrentActionComment='';
function modCommentcommon(sAction,sExtra){
	var arrTopiclist=getTopiclist();
	var sUrl=Q.U('group://grouptopicadmin/'+sAction+'_dialog?groupid='+nGroupid+'&grouptopicid='+nGrouptopicid+'&commentids='+arrTopiclist+(!sExtra?'':'&'+sExtra));
	sCurrentActionComment=sAction;
	oCommonNewmodcomments=windsforceAjax(sUrl,Q.L('你选择了 %d 篇帖子','App',nModclickcount),'',modCommentcommontopic,'',400,100);
}

function modCommentcommontopic(){
	Q.AjaxSubmit('moderateform',Q.U('group://grouptopicadmin/'+sCurrentActionComment),'result',commonComplete);
	return false;
}

/** 删除回帖 */
function modCommentdelete(){
	modCommentcommon('deletecomment');
}

/** 屏蔽或者显示回帖 */
function modCommenthide(){
	modCommentcommon('hidecomment');
}

/** 置顶或者取消置顶回帖 */
function modCommentstickreply(){
	modCommentcommon('stickreplycomment');
}

/** 审核回帖 */
function modCommentaudit(){
	modCommentcommon('auditcomment');
}
