/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事发布公用JS($$)*/

(function($) {
	$.fn.disable = function() {
		return $(this).find("*").each(function() {
			$(this).attr("disabled", "disabled");
		});
	};
	$.fn.enable = function() {
		return $(this).find("*").each(function() {
			$(this).removeAttr("disabled");
		});
	};
})(jQuery);

function loadSwf(type,bid,i,title,url){
	shootClose();

	idname = bid+'_'+i;
	obj = $('#swf_play_'+idname);
	obj2 = $('#swf_cover_'+idname);
	var player = '<div class="video_windows"><embed src="'+url+'" quality="high" width="510" height="355" align="middle"  allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed></div>';
	if(obj.is(":hidden")){
		obj.html('<div class="video_info"><div class="video_cover"><a href="javascript:;" onclick="hideSwf(\''+idname+'\')"><span class="away">'+Q.L('收起','App')+'</span></a><a onclick="shootSwf(\'video\',\''+idname+'\',\''+title+'\',\''+url+'\')" href="javascript:;"><span class="eject">'+Q.L('弹出播放','App')+'</span></a></div><div class="video_title">'+title+'</div></div>'+player).show();
		obj2.hide();
	}else{
		obj.html('').hide();
		obj2.show();
	}
}

function hideSwf(idname){
	obj = $('#swf_play_'+idname);
	obj2 = $('#swf_cover_'+idname);
	obj.html('').hide();
	obj2.show();
}

function shootSwf(type,idname,title,url){
	shootClose();

	$('#swf_play_'+idname).html('').hide();
	$('#swf_cover_'+idname).show();
	
	if(type == 'video'){
		swf = '<embed src="'+url+'" quality="high" width="510" height="355" align="middle"  allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>';
	}
	if(type == 'xiami'){
		swf = '<embed width="340" height="33" wmode="transparent" type="application/x-shockwave-flash" src="'+url+'"></embed>';
	}

	$('body').append('<div class="btn_bottom" id="shoot_swf"><div class="shoot_title">'+title+'</div><div class="shoot_close"><a href="javascript:;" onclick="shootClose()"></a></div><a class="btn5 ie6png" id="eD1" >'+swf+'</a></div>');
}

function shootClose(){
	$('#shoot_swf').html('').remove();
}
