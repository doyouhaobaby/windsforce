$(function(){
	bindHoverCard();
});
bindHoverCard=function () {
    var isHover = false;
    var showHoverCard,removeHoverCard,CurrentCard;
	var selector=$(".bind_hover_card");
	
    selector.live("mouseover", function () {
        if (CurrentCard) CurrentCard.remove();
        if (removeHoverCard) clearTimeout(removeHoverCard);
        if (showHoverCard) clearTimeout(showHoverCard);
		/**当前卡对象**/
        showHoverCard = setTimeout(hoverCardBuilder($(this)), 300);
    });
    selector.live("mouseout", function () {
        if (!isHover) {
            clearTimeout(showHoverCard);
        } else if(CurrentCard) {
			removeHoverCard = setTimeout(function () { CurrentCard.remove() }, 600);
			CurrentCard.hover(function () {
				clearTimeout(removeHoverCard);
			}, function () {
				removeHoverCard = setTimeout(function () { CurrentCard.remove() }, 600);
			});
        }
        isHover = false;
    });
	
	hoverCardBuilder=function (hoverObject) {
		if (!isHover) {
			isHover = true;
			var bmHoverCard = $("<div>").addClass("bm_hover_card").css({ 
														top: hoverObject.offset().top + hoverObject.height()-6,
														left: hoverObject.offset().left-104+ hoverObject.width()/2});
			var bmHoverCardArrow = $("<div>").addClass("bm_hover_card_arrow");
			var bmHoverCardBorder = $("<div>").addClass("bm_hover_card_border");
			var bmLoading = $("<img>").attr({ "border": "0", "src": "images/transparent.gif" }).addClass("loading")
			var bmHoverCardBefore = $("<div>").addClass("bm_hover_card_before");
			var bmHoverCardContainer = $("<div>").addClass("bm_hover_card_container").html(bmHoverCardBefore);
			bmHoverCard.append(bmHoverCardArrow).append(bmHoverCardBorder).append(bmHoverCardContainer);				
			/**加入DOM**/
			$("body").prepend(bmHoverCard);
			CurrentCard=$(".bm_hover_card");

			/**获取数据**/
			if (hoverObject.attr("bm_user_id")) {
				bmHoverCardContainer.html(strHtml);
				/*
				*
				*在服务器中运行时，使用ajax动态取数据
				$.ajax({
					url:"./hoverCard.html",
					type:"get",
					data:{id:hoverObject.attr("bm_user_id")},
					dataType:"html",
					timeout:8000,
					beforeSend:function(){
						bmHoverCardBefore.html(bmLoading);
					},
					success:function(data){
						bmHoverCardContainer.html(data);
					},
					error:function(){
						bmHoverCardBefore.html("读取错误");
					}
				});
				*
				*/
			} else {
				bmHoverCardBefore.html("缺少查询参数");
			}
		}
	}
};
