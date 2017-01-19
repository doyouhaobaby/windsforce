/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 用户公告滚动($$)*/

function $WF_E(element){
	if(arguments.length>1){
		for(var i=0,length=arguments.length,elements=[];i<length;i++){
			elements.push($WF_E(arguments[i]));
		}
		return elements;
	}

	if(typeof element=="string"){
		return document.getElementById(element);
	}else{
		return element;
	}
}

var Class={
	create:function(){
		return function(){
			this.initialize.apply(this,arguments);
		}
	}
};

Function.prototype.bind=function(object){
	var method=this;
	return function(){
		method.apply(object,arguments);
	}
};

var Scroll=Class.create();
Scroll.prototype={
	initialize:function(element,height){
		this.element=$WF_E(element);
		this.element.innerHTML+=this.element.innerHTML;
		this.height=height;
		this.maxHeight=this.element.scrollHeight/2;
		this.counter=0;
		this.scroll();
		this.timer="";
		this.element.onmouseover=this.stop.bind(this);
		this.element.onmouseout=function(){
			this.timer=setTimeout(this.scroll.bind(this),1000);
		}.bind(this);
	},
	scroll:function(){
		if(this.element.scrollTop<this.maxHeight){
			this.element.scrollTop++;
			this.counter++;
		}else{
			this.element.scrollTop=0;
			this.counter=0;
		};

		if(this.counter<this.height){
			this.timer=setTimeout(this.scroll.bind(this),20);
		}else{
			this.counter=0;
			this.timer=setTimeout(this.scroll.bind(this),3000);
		}
	},
	stop:function(){
		clearTimeout(this.timer);
	}
};

var myscroll=new Scroll("announcement-scroll",24);
