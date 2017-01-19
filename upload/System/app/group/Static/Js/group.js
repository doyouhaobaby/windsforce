/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Group应用基础Javascript($$)*/

/** 加入和退出小组 */
function joinGroup(gid,id){
	var sUrl=Q.U('group://group/joingroup');
	Q.AjaxSend(sUrl,'gid='+gid,'',function(data,status){
		if(status==1){
			if(id){
				$('#'+id).html(Q.L('加入成功','App'));
			}
			/*setTimeout("window.location.replace(_SELF_);",1000);*/
		}
	});
}

function leaveGroup(gid,id){
	windsforceConfirm(Q.L('你确定要退出小组吗？如果，你是小组的管理人员包括创始人，退出小组意味着你的职务将会被自动解除，请慎重！','App'),function(){
		var sUrl=Q.U('group://group/leavegroup');
		Q.AjaxSend(sUrl,'gid='+gid,'',function(data,status){
			if(status==1){
				if(id){
					$('#'+id).html(Q.L('退出成功','App'));
				}
				/*setTimeout("window.location.replace(_SELF_);",1000);*/
			}
		});
	});
}
