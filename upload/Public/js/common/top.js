/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 回到顶部($$)*/

$(function(){
	var sBackToTopTxt=Q.L('返回顶部','Common'),oBackToTopEle=$('<div style="display:none;" class="back-to" id="toolBackTop"><a title="'+sBackToTopTxt+'" onclick="window.scrollTo(0,0);return false;" href="#top" class="back-top">'+sBackToTopTxt+'</a></div>').appendTo($("body"))
		.click(function(){
			$("html, body").animate({ scrollTop: 0 },120);
	}),sBackToTopFun=function(){
		var st=$(document).scrollTop(),winh=$(window).height();
		(st>0)?oBackToTopEle.show():oBackToTopEle.hide();
			
		/* IE6下的定位 */
		if(!window.XMLHttpRequest){
			oBackToTopEle.css("top",st+winh-166);
		}
	};

	$(window).bind("scroll",sBackToTopFun);
	$(function(){
		sBackToTopFun();
	});
});
