/**
 * 声明QEEPHP包
 */
var Q,QEEPHP=Q=QEEPHP || function(){}; 
QEEPHP.Version='20130127';
QEEPHP.Guid="%QEEPHP%";
window[QEEPHP.Guid]=window[QEEPHP.Guid] || {};

/**
 * 浏览器判断
 */
QEEPHP.Browser=QEEPHP.Browser || {};
QEEPHP.Browser.UserAgent=navigator.userAgent.toLowerCase();
QEEPHP.Browser.Version=(QEEPHP.Browser.UserAgent.match(/.+(?:rv|it|ra|ie)[\/:]([\d.]+)/) || [])[1];
QEEPHP.Browser.IsStrict=document.compatMode=="CSS1Compat";

/** 判断具体的浏览器 */
QEEPHP.Browser.IsGecko=/gecko/i.test(QEEPHP.Browser.UserAgent) && !/like gecko/i.test(QEEPHP.Browser.UserAgent);/* gecko内核 */
QEEPHP.Browser.IsWebkit=/webkit/i.test(QEEPHP.Browser.UserAgent);/* webkit内核 */
try{QEEPHP.Browser.Maxthon=/(\d+\.\d)/.test(external.max_version);}catch(e){}/* maxthon浏览器 */
QEEPHP.Browser.Opera=/opera\/(\d+\.\d)/i.test(Q.Browser.UserAgent);/* opera浏览器 */
(function(){QEEPHP.Browser.Safari=/(\d+\.\d)?(?:\.\d)?\s+safari\/?(\d+\.\d+)?/i.test(QEEPHP.Browser.UserAgent) && !/chrome/i.test(QEEPHP.Browser.UserAgent);})();/* safari浏览器 */
QEEPHP.Browser.Ie=QEEPHP.Ie=/msie/.test(QEEPHP.Browser.UserAgent) && !/opera/.test(QEEPHP.Browser.UserAgent);/* IE浏览器 */
QEEPHP.Browser.Firefox= /firefox\/(\d+\.\d)/i.test(QEEPHP.Browser.UserAgent);/* 火狐 */
QEEPHP.Browser.Chrome=/chrome\/(\d+\.\d)/i.test(QEEPHP.Browser.UserAgent);/* chrome浏览器 */

/**
 * 框架入口路径
 * 需要在框架Js库使用前定义
 */
if(typeof(__QEEPHP_JS_ENTER__)!='undefined' && __QEEPHP_JS_ENTER__!=''){
	QEEPHP.AppPath=__QEEPHP_JS_ENTER__;
}else{
	QEEPHP.AppPath='/';
}

/**
 * URL相关操作
 */
QEEPHP.Url=QEEPHP.Url || {};

/**
 * 模拟框架URL 生成函数
 *
 * < 本函数用于特殊请求,用于框架加载数据
 *   请在模板中预先调用框架中的PHP代码 App::U()初始化相关参数
 *   Q.U('app://admin.php*module/action?param1=1&param1=2',new Array('extraparam=hello','extraparam2=hello2','extraparam3=hello3'));
 *   Q.U('@://module/action?param1=1&param1=2',new Array('extraparam=hello','extraparam2=hello2','extraparam3=hello3'));
 *   Q.U('module/action?param1=1&param1=2',new Array('extraparam=hello','extraparam2=hello2','extraparam3=hello3'));
 * >
 * 
 * @param string sUrl 初始化URL   
 * @param arrParams arrParams 附件参数
 * @param bool bRedirect 是否返回
 * @param bool bSuffix 是否加上静态化后缀
 * @returns string
 */
QEEPHP.Url.U=function(sUrl,arrParams,bRedirect,bSuffix){
	var sWebsite=QEEPHP.AppPath;

	/* 初始化参数 */
	arrParams=arrParams || new Array();
	bRedirect=bRedirect || false;
	bSuffix=bSuffix || false;

	var sLeave=sExtra='';

	/* 提取URL中的 额外参数 */
	arrUrl=sUrl.split('?');
	if(arrUrl[1]){
		sExtra=arrUrl[1]; 
	}
	sLeave=arrUrl[0];

	/* 提取项目 */
	var sApp='';
	if(sLeave.indexOf('://')<0){
		sApp=_APP_NAME_;
	}else{
		arrUrl=sLeave.split('://');
		if(arrUrl[0]=='@'){
			sApp=_APP_NAME_;
			sLeave=arrUrl[1];
		}else if(!arrUrl[1]){
			sApp=_APP_NAME_;
			sLeave=arrUrl[0];
		}else{
			sApp=arrUrl[0];
			sLeave=arrUrl[1];
		}
	}

	/* 提取入口 */
	var sEnter='';
	if(sLeave.indexOf('*')<0){
		sEnter=_ENTER_;
	}else{
		arrUrl=sLeave.split('*');
		if(arrUrl[0]=='@'){
			sEnter=_ENTER_;
			sLeave=arrUrl[1];
		}else if(!arrUrl[1]){
			sEnter=_ENTER_;
			sLeave=arrUrl[0];
		}else{
			sEnter=arrUrl[0];
			sLeave=arrUrl[1];
		}
	}

	/* 提取模块和方法 */
	arrUrl=sLeave.split('/');
	var sModule=sAction='';
	if(!arrUrl[1]){
		if(arrUrl[0]){
			sModule=_MODULE_NAME_; 
			sAction=arrUrl[0];
		}else{
			sModule=_MODULE_NAME_;
			sAction=_ACTION_NAME_;
		}
		
	}else{
		if(arrUrl[0]=='@'){
			sModule=_MODULE_NAME_;
		}else{
			sModule=arrUrl[0];
		}

		if(arrUrl[1]=='@'){
			sAction=_ACTION_NAME_;
		}else{
			sAction=arrUrl[1];
		}
	}

	sWebsite=sWebsite+'?'+_APP_VAR_NAME_+'='+sApp+'&'+_CONTROL_VAR_NAME_+'='+sModule+'&'+_ACTION_VAR_NAME_+'='+sAction;

	/* sUrl 额外参数*/
	if(sExtra){
		sWebsite+='&'+sExtra;
	}

	/* 数组 额外参数*/
	if(arrParams&&arrParams.length>0){
		arrParams=arrParams.join('&');
		sWebsite+='&'+arrParams;
	}

	if(bSuffix){
		sWebsite+=_URL_HTML_SUFFIX_;
	}

	if(bRedirect){
		window.location.href=sWebsite;
	}else{
		return sWebsite;
	}
};

QEEPHP.U=QEEPHP.Url.U;

/**
 * 操作字符串的方法
 */
QEEPHP.String=QEEPHP.String || {};

/**
 * 格式化语句
 *
 * @param format
 * @param args
 * @returns string
 */
QEEPHP.String.Format=function(format,args){
	/* http://kevin.vanzonneveld.net
	// +   original by: Ash Searle(http://hexmen.com/blog/)
	// + namespaced by: Michael White(http://crestidg.com)
	// *   example 1: sprintf("%01.2f",123.1);
	// *   returns 1: 123.10 */
	var regex=/%%|%(\d+\$)?([-+#0]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuidfegEG])/g;
	/* var a=arguments,i=0,format=a[i++]; */
	var i=0;
	var a=args;
	var pad=function(str,len,chr,leftJustify){/* pad()*/
		var padding=(str.length>=len)?'':Array(1+len-str.length>>>0).join(chr);
		return leftJustify?str+padding:padding+str;
	};

	var justify=function(value,prefix,leftJustify,minWidth,zeroPad){/* justify()*/
		var diff=minWidth-value.length;
		if(diff>0){
			if(leftJustify || !zeroPad){
				value=pad(value,minWidth,' ',leftJustify);
			}else{
				value=value.slice(0,prefix.length)+pad('',diff,'0',true)+value.slice(prefix.length);
			}
		}
		return value;
	};

	var formatBaseX=function(value,base,prefix,leftJustify,minWidth,precision,zeroPad){/* formatBaseX()*/
		/* Note: casts negative numbers to positive ones */
		var number=value>>>0;
		prefix=prefix && number && {'2':'0b','8':'0','16':'0x'}[base] || '';
		value=prefix + pad(number.toString(base),precision || 0,'0',false);
		return justify(value,prefix,leftJustify,minWidth,zeroPad);
	};

	var formatString=function(value,leftJustify,minWidth,precision,zeroPad){/* formatString()*/
		if(precision !=null){
			value=value.slice(0,precision);
		}
		return justify(value,'',leftJustify,minWidth,zeroPad);
	};

	var doFormat=function(substring,valueIndex,flags,minWidth,_,precision,type){/* finalFormat()*/
		if(substring=='%%')return '%';

		/* parse flags */
		var leftJustify=false,positivePrefix='',zeroPad=false,prefixBaseX=false;
		for(var j=0;flags && j<flags.length;j++)switch(flags.charAt(j)){
			case ' ': positivePrefix=' '; break;
			case '+': positivePrefix='+'; break;
			case '-': leftJustify=true; break;
			case '0': zeroPad=true; break;
			case '#': prefixBaseX=true; break;
		}

		/* parameters may be null,undefined,empty-string or real valued
		   we want to ignore null,undefined and empty-string values */
		if(!minWidth){
			minWidth=0;
		}else if(minWidth=='*'){
			minWidth=+a[i++];
		}else if(minWidth.charAt(0)=='*'){
			minWidth=+a[minWidth.slice(1,-1)];
		}else {
			minWidth=+minWidth;
		}

		/* Note: undocumented perl feature: */
		if(minWidth<0){
			minWidth=-minWidth;
			leftJustify=true;
		}

		if(!isFinite(minWidth)){
			throw new Error('QEEPHP.Lang.Format:(minimum-)width must be finite');
		}

		if(!precision){
			precision='fFeE'.indexOf(type)>-1?6:(type=='d')?0:void(0);
		}else if(precision=='*'){
			precision=+a[i++];
		}else if(precision.charAt(0)=='*'){
			precision=+a[precision.slice(1,-1)];
		}else{
			precision=+precision;
		}

		var value=valueIndex?a[valueIndex.slice(0,-1)]:a[i++];/* grab value using valueIndex if required? */
		switch(type){
			case 's': return formatString(String(value),leftJustify,minWidth,precision,zeroPad);
			case 'c': return formatString(String.fromCharCode(+value),leftJustify,minWidth,precision,zeroPad);
			case 'b': return formatBaseX(value,2,prefixBaseX,leftJustify,minWidth,precision,zeroPad);
			case 'o': return formatBaseX(value,8,prefixBaseX,leftJustify,minWidth,precision,zeroPad);
			case 'x': return formatBaseX(value,16,prefixBaseX,leftJustify,minWidth,precision,zeroPad);
			case 'X': return formatBaseX(value,16,prefixBaseX,leftJustify,minWidth,precision,zeroPad).toUpperCase();
			case 'u': return formatBaseX(value,10,prefixBaseX,leftJustify,minWidth,precision,zeroPad);
			case 'i':
			case 'd':{
						var number=parseInt(+value);
						var prefix=number<0?'-':positivePrefix;
						value=prefix+pad(String(Math.abs(number)),precision,'0',false);
						return justify(value,prefix,leftJustify,minWidth,zeroPad);
					}
			case 'e':
			case 'E':
			case 'f':
			case 'F':
			case 'g':
			case 'G':
					{
						var number=+value;
						var prefix=number<0?'-':positivePrefix;
						var method=['toExponential','toFixed','toPrecision']['efg'.indexOf(type.toLowerCase())];
						var textTransform=['toString','toUpperCase']['eEfFgG'.indexOf(type)%2];
						value=prefix + Math.abs(number)[method](precision);
						return justify(value,prefix,leftJustify,minWidth,zeroPad)[textTransform]();
					}
			default:return substring;
		}
	};

	return format.replace(regex,doFormat);
};

/**
 * Ajax请求
 *
 * < 对XMLHttpRequest请求的封装。>
 * < onfailure 请求失败的全局事件，function(XMLHttpRequest oXmlHttp) >
 * < onbeforerequest 请求发送前触发的全局事件，function(XMLHttpRequest oXmlHttp) >
 * < onStatusCode 状态码触发的全局事件，function( XMLHttpRequest oXmlHttp ),注意：onStatusCode中的StatusCode需用404,320等状态码替换。如on404 >
 */
QEEPHP.Ajax=QEEPHP.Ajax || {};

/**
 * 对方法的操作，解决内存泄露问题
 */
QEEPHP.Fn=QEEPHP.Fn || {};

/**
 * 这是一个空函数，用于需要排除函数作用域链干扰的情况.
 */
QEEPHP.Fn.Blank=function(){};

/**
 * 发送一个ajax请求
 *
 * <!-- 说明 -->
 * < Function [onsuccess] 请求成功时触发，function(XMLHttpRequest oXmlHttp, string responseText)。
 *   Function [onfailure] 请求失败时触发，function(XMLHttpRequest oXmlHttp)。
 *   Function [onbeforerequest] 发送请求之前触发，function(XMLHttpRequest oXmlHttp)。
 *   Function [ on{STATUS_CODE}] 当请求为相应状态码时触发的事件，如on302、on404、on500，function(XMLHttpRequest xhr)。3XX的状态码浏览器无法获取，4xx的，可能因为未知问题导致获取失败。>
 *
 * @param string sUrl 发送请求的url
 * @param Object arrOptions 发送请求的选项参数
 * @returns {XMLHttpRequest} 发送请求的XMLHttpRequest对象
 */
QEEPHP.Ajax.Request=function(sUrl,arrOptions){
	/* Ajax请求配置 */
	arrOptions=arrOptions || {};
	var sData=arrOptions.data || "",/* 需要发送的数据。如果是GET请求的话，不需要这个属性 | string */
		bAsync=!(arrOptions.async===false),/* 是否异步请求。默认为true（异步）| boolean */
		sUsername=arrOptions.username || "",/* 用户名 */
		sPassword=arrOptions.password || "",/* 密码 */
		sMethod=(arrOptions.method || "GET").toUpperCase(),/* 发送方式 ：POST,GET */
		arrHeaders=arrOptions.headers || {},/* 要设置的http request header(HTTP 请求头)*/
		arrEventHandlers={},/* 事件句柄 */
		sKey,oXmlHttp; /* 参数中的键值，XMLHttpRequest对象 */

	/**
	 * readyState发生变更时调用
	 * 
	 * <!--readyState 详解-->
	 * < readyState表示XMLHttpRequest对象的处理状态：
	 *   0:XMLHttpRequest对象还没有完成初始化。
	 *   1:XMLHttpRequest对象开始发送请求。
	 *   2:XMLHttpRequest对象的请求发送完成。
	 *   3:XMLHttpRequest对象开始读取服务器的响应。
	 *   4:XMLHttpRequest对象读取服务器响应结束。
	 *   另：在IE(即Internet Explorer)浏览器中可以不区分大小写，但在其他浏览器中将严格区分大小写。所以为了保证更好的跨浏览器效果，建议采用严格区分大小写的形式。>
	 *
	 * @return void
	 */
	function stateChangeHandler(){
		if(oXmlHttp.readyState==4){
			try{
				var nStat=oXmlHttp.status;
			}catch(ex){
				fire('failure');/* 在请求时，如果网络中断，Firefox会无法取得status */
				return;
			}

			fire(nStat);
			if((nStat >=200 && nStat < 300)|| nStat==304 || nStat==1223){
				fire('success');
			}else{
				fire('failure');
			}
			
			/*
			 * NOTE: Testing discovered that for some bizarre reason,on Mozilla,the
			 * JavaScript <code>XmlHttpRequest.onreadystatechange</code> handler
			 * function maybe still be called after it is deleted. The theory is that the
			 * callback is cached somewhere. Setting it to null or an empty function does
			 * seem to work properly,though.
			 * 
			 * On IE,there are two problems: Setting onreadystatechange to null(as
			 * opposed to an empty function)sometimes throws an exception. With
			 * particular(rare)versions of jscript.dll,setting onreadystatechange from
			 * within onreadystatechange causes a crash. Setting it from within a timeout
			 * fixes this bug(see issue 1610).
			 * 
			 * End result: *always* set onreadystatechange to an empty function(never to
			 * null). Never set onreadystatechange from within onreadystatechange(always
			 * in a setTimeout()).
			 */
			window.setTimeout(
				function(){
					/* 避免内存泄露. 
					// 由new Function改成不含此作用域链的Q.Fn.Blank 函数,
					// 以避免作用域链带来的隐性循环引用导致的IE下内存泄露 */
					oXmlHttp.onreadystatechange=QEEPHP.Fn.Blank; 
					if(bAsync){
						oXmlHttp=null;
					}
				}
			,0);
		}
	}

	/**
	 * 获取XMLHttpRequest对象
	 * 
	 * @return {XMLHttpRequest} XMLHttpRequest对象
	 */
	function getXmlHttpRequest(){
		/* XMLHttpRequest 请求对象 */
		var oXmlHttp=null; 

		if(window.XMLHttpRequest){ 
			oXmlHttp=new XMLHttpRequest(); 
		}

		if(!oXmlHttp&&window.ActiveXObject){ 
			try {
				oXmlHttp=new ActiveXObject("Msxml2.XMLHTTP.5.0")
			}catch(e){
				try{
					oXmlHttp=new ActiveXObject("Msxml2.XMLHTTP.4.0")
				}catch(e){
					try{
						oXmlHttp=new ActiveXObject("Msxml2.XMLHTTP")
					}catch(e){
						try{
							oXmlHttp=new ActiveXObject("Microsoft.XMLHTTP")
						}catch(e){
							alert("The Browser Not Support XMLHttp。");
						}
					}
				}
			}
		}

		/* 返回生成的对象 */
		return oXmlHttp;
	}

	/**
	 * 触发事件
	 * 
	 * @param string sType 事件类型
	 * @return void
	 */
	function fire(sType){
		/* 初始化参数 */
		sType='on' + sType;

		var sHandler=arrEventHandlers[sType],
			sGlobelHandler=QEEPHP.Ajax[sType];

		/* 不对事件类型进行验证 */
		if(sHandler){
			if(sType!='onsuccess'){
				sHandler(oXmlHttp);
			}else{
				try{/* 处理获取oXmlHttp.responseText导致出错的情况,比如请求图片地址. */
					oXmlHttp.responseText;
				}catch(error){
					return sHandler(oXmlHttp);
				}

				sHandler(oXmlHttp,oXmlHttp.responseText);
			}
		}else if(sGlobelHandler){
			if(sType=='onsuccess'){/* onsuccess不支持全局事件 */
				return;
			}

			sGlobelHandler(oXmlHttp);
		}
	}

	for(sKey in arrOptions){/* 将arrOptions参数中的事件参数复制到 eventHandlers 对象中 */
		 if(sKey!=='data' && sKey!=='async' && sKey!=='username' && sKey!=='password' && sKey!=='headers')
			 arrEventHandlers[ sKey ]=arrOptions[ sKey ];
	}

	/* 标识XMLHttpRequest */
	arrHeaders['X-Requested-With']='XMLHttpRequest';

	try{
		oXmlHttp=getXmlHttpRequest();/* 获取XMLHttp对象 */

		/* GET 方式 */
		if(sMethod=='GET'){
			if(sData){/* 如果设置了发送数据，那么在URL添加上 */
				/* indexOf()方法可返回某个指定的字符串值在字符串中首次出现的位置。
				// stringObject.indexOf(searchvalue,fromindex)
				// indexOf()方法对大小写敏感！
				// 如果要检索的字符串值没有出现，则该方法返回 -1。 */
				sUrl+=(sUrl.indexOf('?')>=0?'&':'?')+sData;
				sData=null;
			}

			/* 如果设置了不需要缓存，默认需要缓存 */
			if(arrOptions['noCache']){
				sUrl+=(sUrl.indexOf('?')>=0?'&': '?')+'b'+(+ new Date)+'=1';
			}
		}

		/* 创建一个新的http请求，并指定此请求的方法、URL以及验证信息
		// 语法
		// oXMLHttpRequest.open(bstrMethod,bstrUrl,varAsync,bstrUser,bstrPassword);
		// 参数
		// bstrMethod
		// http方法，例如：POST、GET、PUT及PROPFIND。大小写不敏感。
		// bstrUrl
		// 请求的URL地址，可以为绝对地址也可以为相对地址。
		// varAsync[可选]
		// 布尔型，指定此请求是否为异步方式，默认为true。如果为真，当状态改变时会调用onreadystatechange属性指定的回调函数。
		// 1. 当该boolean值为true时，服务器请求是异步进行的，也就是脚本执行send（）方法后不等待服务器的执行结果，而是继续执行脚本代码；
		// 2. 当该boolean值为false时，服务器请求是同步进行的，也就是脚本执行send（）方法后等待服务器的执行结果的返回，若在等待过程中超时，则不再等待，继续执行后面的脚本代码！
		// bstrUser[可选]
		// 如果服务器需要验证，此处指定用户名，如果未指定，当服务器需要验证时，会弹出验证窗口。
		// bstrPassword[可选]
		// 验证信息中的密码部分，如果用户名为空，则此值将被忽略。
		// 如果设置了用户名，那么加密发送 */
		if(sUsername){
			oXmlHttp.open(sMethod,sUrl,bAsync,sUsername,sPassword);
		}else{
			oXmlHttp.open(sMethod,sUrl,bAsync);
		}

		if(bAsync){/* 异步执行，不用等待 */
			oXmlHttp.onreadystatechange=stateChangeHandler;
		}

		if(sMethod=='POST'){/* 在open之后再进行http请求头设定 */
			oXmlHttp.setRequestHeader("Method","POST " + sUrl + " HTTP/1.1");
			oXmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}

		for(sKey in arrHeaders){
			/* hasOwnProperty
			// 此方法无法检查该对象的原型链中是否具有该属性；该属性必须是对象本身的一个成员。 
			// in 操作检查对象中是否有名为 property 的属性。也可以检查对象的原型，判断该属性是否为原型链的一部分。 
			// 代码如下:
			//  function Test(){ 
			//     this. a='abc'; 
			//  } 
			// Test.prototype.b='efg'; 
			// var test=new Test; 
			// alert(test.hasOwnProperty('a'));//输出 true 
			// alert(test.hasOwnProperty('b'));//输出 false 
			// alert('a' in test);//输出 true 
			// alert('b' in test);//输出 true */
			if(arrHeaders.hasOwnProperty(sKey)){
				oXmlHttp.setRequestHeader(sKey,arrHeaders[sKey]);
			}
		}

		fire('beforerequest');/* 触发数据发送前事件 */

		oXmlHttp.send(sData);/* 正式发送数据 */

		if(!bAsync){/* 如果是同步，返回成功执行结果 */
			stateChangeHandler();
		}
	}catch(ex){
		fire('failure');/* 触发失败事件 */
	}

	/* 返回XMLHttp 对象 */
	return oXmlHttp;
};

/**
 * 发送一个post请求
 * 
 * @param string sUrl 发送请求的url地址
 * @param string sData 发送的数据
 * @param Function [onsuccess] 请求成功之后的回调函数，function(XMLHttpRequest oXmlHttp, string responseText)
 * @meta standard
 * @returns {XMLHttpRequest} 发送请求的XMLHttpRequest对象
 */
QEEPHP.Ajax.Post=function(sUrl,sData,onsuccess){
	return QEEPHP.Ajax.Request(
		sUrl,{
			'onsuccess':onsuccess,
			'method':'POST',
			'data':sData
		}
	);
};

/**
 * 发送一个get请求
 *
 * @param string sUrl 发送请求的url地址
 * @param string sData 发送的数据
 * @param Function [onsuccess] 请求成功之后的回调函数，function(XMLHttpRequest oXmlHttp, string responseText)
 * @returns {XMLHttpRequest} 发送请求的XMLHttpRequest对象
 */
QEEPHP.Ajax.Get=function(sUrl,sData,onsuccess){
	return QEEPHP.Ajax.Request(sUrl,{'onsuccess':onsuccess,'data':sData});
};

/**
 * 将一个表单用ajax方式提交
 * 
 * <!-- 说明 -->
 * < Function [replacer] 对参数值特殊处理的函数,replacer(string value, string key)
 *   Function [onbeforerequest] 发送请求之前触发，function(XMLHttpRequest oXmlHttp)。
 *   Function [onsuccess] 请求成功时触发，function(XMLHttpRequest oXmlHttp, string responseText)。
 *   Function [onfailure] 请求失败时触发，function(XMLHttpRequest oXmlHttp)。
 *   Function [on{STATUS_CODE}] 当请求为相应状态码时触发的事件，如on302、on404、on500，function(XMLHttpRequest oXmlHttp)。3XX的状态码浏览器无法获取，4xx的，可能因为未知问题导致获取失败。 >
 *
 * @param HTMLFormElement oForm 需要提交的表单元素
 * @param Object [arrOptions] 发送请求的选项参数
 * @returns {XMLHttpRequest} 发送请求的XMLHttpRequest对象
 */
QEEPHP.Ajax.Form=function(oForm,arrOptions){
	/* 初始化配置参数 */
	arrOptions=arrOptions || {};
	var arrElements=oForm.elements,
		nLen=arrElements.length,
		sMethod=oForm.getAttribute('method'),
		sUrl=oForm.getAttribute('action'),
		replacer=arrOptions.replacer || function(sValue,sName){
			return sValue;
		},
		arrSendOptions={},
		arrData=[],
		nI,oItem,sItemType,sItemName,sItemValue,
		arrOpts,oOI,nOLen,oOItem;

	/* 向缓冲区添加参数数据 */
	function addData(sName,sValue){
		arrData.push(encodeURIComponent(sName)+'='+encodeURIComponent(sValue));
	}

	/* 复制发送参数选项对象 */
	for(nI in arrOptions){
		if(arrOptions.hasOwnProperty(nI)){
			arrSendOptions[nI]=arrOptions[nI];
		}
	}

	for(nI=0;nI<nLen;nI++){
		oItem=arrElements[nI];
		sItemName=oItem.name;

		/* 处理：可用并包含表单name的表单项 */
		if(!oItem.disabled && sItemName){
			sItemType=oItem.type;
			sItemValue=oItem.value;
			switch(sItemType){
				/* radio和checkbox被选中时，拼装queryString数据 */
				case 'radio':
				case 'checkbox':
					if(!oItem.checked){
						break;
					}
				/* 默认类型，拼装queryString数据 */
				case 'textarea':
				case 'text':
				case 'password':
				case 'hidden':
				case 'select-one':
					addData(sItemName,replacer(sItemValue,sItemName));
				break;
				/* 多行选中select，拼装所有选中的数据 */
				case 'select-multiple':
					arrOpts=oTtem.arrOptions;
					nOLen=arrOpts.length;
					for(nOI=0; nOI<nOLen;nOI++){
						oOItem=arrOpts[nOI];
						if(oOItem.selected){
							addData(sItemName,replacer(oOItem.value,sItemName));
						}
				}
				break;
			}
		}
	}

	/* 完善发送请求的参数选项 */
	arrSendOptions.data=arrData.join('&');
	arrSendOptions.method=oForm.getAttribute('method')|| 'GET';
	
	/* 发送请求 */
	return QEEPHP.Ajax.Request(sUrl,arrSendOptions);
};

/**
 * QeePHP专用Ajax格式
 */
var m={
	'\b': '\\b',
	'\t': '\\t',
	'\n': '\\n',
	'\f': '\\f',
	'\r': '\\r'
};

QEEPHP.Ajax.Q=QEEPHP.Ajax.Q || {};

/* 程序版本 */
QEEPHP.Ajax.Q.Version='1.1(2011-11-27)';

/* ajax方法 */
QEEPHP.Ajax.Q.Method="POST";

/* 提示信息对象 */
QEEPHP.Ajax.Q.TipTarget='QAjaxResult';

/* 是否显示提示信息 */
QEEPHP.Ajax.Q.ShowTip=true;

/* 提示信息 */
QEEPHP.Ajax.Q.UpdateTips='loading...';

/* 返回状态码 */
QEEPHP.Ajax.Q.Status=0; 

/* 返回信息 */
QEEPHP.Ajax.Q.Info='';

/* 返回数据 */
QEEPHP.Ajax.Q.Data='';

/* 依次是处理中 成功 和错误 显示的图片 */
QEEPHP.Ajax.Q.Image=['','',''];

/* 是否显示提示信息，默认开启 */
QEEPHP.Ajax.Q.ShowTip=true;

/* JSON EVAL XML */
QEEPHP.Ajax.Q.Type='';

/* 是否完成 */
QEEPHP.Ajax.Q.Complete=false;
QEEPHP.Ajax.Q.Debug=false;

/* ajax回调函数 */
QEEPHP.Ajax.Q.Response;
QEEPHP.Ajax.Q.Options={};

/**
 * Ajax 消息DIV ID
 *
 * @param  string sTarget
 */
QEEPHP.Ajax.Q.Target=function(sTarget){
	QEEPHP.Ajax.Q.Options['target']=sTarget;
	return QEEPHP.Ajax.QEEPHP;
};

/**
 * Ajax加载消息
 *
 * @param  string sTips  Load消息
 */
QEEPHP.Ajax.Q.Tips=function(sTips){
	QEEPHP.Ajax.Q.Options['tips']=sTips;
	return QEEPHP.Ajax.QEEPHP;
};

/**
 * Ajax 请求URL
 *
 * @param string sUrl
 */
QEEPHP.Ajax.Q.Url=function(sUrl){
	QEEPHP.Ajax.Q.Options['url']=sUrl;
	return QEEPHP.Ajax.QEEPHP;
};

/**
 * Ajax 请求URL
 *
 * @param string sVars
 */
QEEPHP.Ajax.Q.Params=function(sVars){
	QEEPHP.Ajax.Q.Options['var']=sVars;
	return QEEPHP.Ajax.QEEPHP;
};

/**
 * ajax返回消息表格容器
 *
 * @param string sInfo 消息内容
 * @param string sImages 消息图标
 */
QEEPHP.Ajax.Q.MessageTable=function(sInfo,sImages){
	var sContent='<table width="100%" border="0" align="left" valign="middle" cellpadding="0" cellspacing="0"><tr>';
	if(sImages){
		sContent+='<td width="40px" valign="middle">'+sImages+'</td>';
	}
	sContent+='<td valign="middle">'+sInfo+'</span></td></tr></table>';

	return sContent;
};

/**
 * Js消息提示函数
 *
 * @param string sInfo 消息内容
 * @param int nStatus 消息状态
 * @param int nDisplay消息显示时间(s)，0表示不显示
 * @param string sTarget 目标DIV ID
 * @param bool bShowTip 是否显示消息
 */
QEEPHP.Ajax.Q.Message=function(sInfo,nStatus,nDisplay,sTarget,bShowTip){
	if(typeof(sInfo)=='undefined'){
		sInfo=QEEPHP.Ajax.Q.Info;
	}

	if(typeof(nDisplay)=='undefined'){
		nDisplay=1;
	}

	if(typeof(sTarget)=='undefined'){
		sTarget=QEEPHP.Ajax.Q.TipTarget;
	}

	if(typeof(bShowTip)=='undefined'){
		bShowTip=QEEPHP.Ajax.Q.ShowTip;
	}

	if(typeof(nStatus)=='undefined'){
		nStatus==1;
	}

	if(document.getElementById(sTarget)){
		if(nDisplay && bShowTip && typeof(sInfo)!='undefined' && sInfo!=''){
			var sOldTarget=sTarget;
			sTarget=document.getElementById(sTarget);
			sTarget.style.display="block";

			if(nStatus==1){
				if(''!=QEEPHP.Ajax.Q.Image[1]){
					sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span style="color:blue">'+sInfo+'</span>','<img src="'+QEEPHP.Ajax.Q.Image[1]+'" class="'+sOldTarget+'Success" border="0" alt="success..." align="absmiddle">');
				}else{
					sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span style="color:blue">'+sInfo+'</span>');
				}
			}else{
				if(''!=QEEPHP.Ajax.Q.Image[2]){
					sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span style="color:red">'+sInfo+'</span>','<img src="'+QEEPHP.Ajax.Q.Image[2]+'" class="'+sOldTarget+'Error" border="0" alt="error..." align="absmiddle">');
				}else{
					sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span style="color:red">'+sInfo+'</span>');
				}
			}
		}

		/* 提示信息停留QEEPHP.Ajax.Q.Display 秒 */
		if(nDisplay&& bShowTip && typeof(sInfo)!='undefined' && sInfo!=''){
			setTimeout(function(){sTarget.style.display="none";},nDisplay*1000);
		}
	}
};

QEEPHP.Message=QEEPHP.Ajax.Q.Message;

/**
 * QeePHP专用Ajax格式
 *
 * @param string sTarget 消息DIV ID
 * @param string sTips Ajax加载消息
 */
QEEPHP.Ajax.Q.Loading=function(sTarget,sTips){
	if(sTarget){
		sTarget=document.getElementById(sTarget);
		sTarget.style.display="block";

		if(''!=QEEPHP.Ajax.Q.Image[0]){
			sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span>'+sTips+'</span>','<img src="'+QEEPHP.Ajax.Q.Image[0]+'" border="0" alt="loading..." align="absmiddle">');
		}else{
			sTarget.innerHTML=QEEPHP.Ajax.Q.MessageTable('<span>'+sTips+'</span>');
		}
	}
};

/**
 * ajax消息显示
 *
 * @param oRequest xmlHttp对象
 * @param sTarget DIV ID
 * @param Response 请求函数
 * @param sTips 加载消息
 */
QEEPHP.Ajax.Q.AjaxResponse=function(oRequest,sTarget,Response,sTips){
	var sStr=oRequest.responseText;

	sStr=sStr.replace(/([\x00-\x1f\\"])/g, function(a, b){
		var c=m[b];
		if(c){
			return c;
		}else{
			return b;
		}
	});

	try{
		arrReturn=eval('('+sStr+')');
		if(QEEPHP.Ajax.Q.Debug){
			alert(sStr);
		}
	}catch(ex){
		if(QEEPHP.Ajax.Q.Debug){
			alert("The server returns data in non-JS:\n\n"+sStr);
		}
		alert('The server returns data error!'+sStr);
		return;
	};

	/* 服务器返回数据格式 */
	QEEPHP.Ajax.Q.Status=arrReturn.status;
	QEEPHP.Ajax.Q.Info=arrReturn.info;
	QEEPHP.Ajax.Q.Data=arrReturn.data;
	QEEPHP.Ajax.Q.Type=arrReturn.type;
	QEEPHP.Ajax.Q.Display=arrReturn.display;

	if(QEEPHP.Ajax.Q.Display &&QEEPHP.Ajax.Q.ShowTip){
		QEEPHP.Ajax.Q.Loading(sTarget,sTips);
	}

	if(QEEPHP.Ajax.Q.Type=='EVAL'){
		eval(QEEPHP.Ajax.Q.Data);/* 直接执行返回的脚本 */
	}else{
		if(typeof(Response)=='undefined'){/* 需要在客户端定义ajaxReturn方法 */
			try{(ajaxReturn).apply(this,[QEEPHP.Ajax.Q.Data,QEEPHP.Ajax.Q.Status,QEEPHP.Ajax.Q.Info,QEEPHP.Ajax.Q.Type]);}
			catch(e){}
		}else{
			try{(Response).apply(this,[QEEPHP.Ajax.Q.Data,QEEPHP.Ajax.Q.Status,QEEPHP.Ajax.Q.Info,QEEPHP.Ajax.Q.Type]);}
			catch(e){}
		}
	}

	/* 显示提示信息 */
	QEEPHP.Ajax.Q.Message(QEEPHP.Ajax.Q.Info,QEEPHP.Ajax.Q.Status,QEEPHP.Ajax.Q.Display,sTarget,QEEPHP.Ajax.Q.ShowTip);
};

/**
 * 表单提交ajax
 *
 * @param string form 表单ID
 * @param string url Ajax请求url
 * @param string target 返回的消息的div id值
 * @param function response 回调函数
 * @param string sTips Ajax加载消息
 */
QEEPHP.Ajax.Q.AjaxSubmit=function(sForm,sUrl,sTarget,Response,sTips){
	/* 请求URL */
	sUrl=(typeof(sUrl)=='undefined' || sUrl=='' || sUrl===null)?QEEPHP.Ajax.Q.Options['url']:sUrl;

	/* 消息DIV id */
	if(typeof(sTarget)=='undefined' || sTarget=='' || sTarget===null){
		QEEPHP.Ajax.Q.TipTarget=(QEEPHP.Ajax.Q.Options['target'])?QEEPHP.Ajax.Q.Options['target']:QEEPHP.Ajax.Q.TipTarget;
	}else{
		QEEPHP.Ajax.Q.TipTarget=sTarget;
	}

	/* 成功后回调函数 */
	QEEPHP.Ajax.Q.Response=(typeof(Response)=='undefined' || Response==''||Response===null)?QEEPHP.Ajax.Q.Response:Response;
	if(typeof(sTips)=='undefined' || sTips==''|| sTips===null){
		sTips=(QEEPHP.Ajax.Q.Options['tips'])?QEEPHP.Ajax.Q.Options['tips']:QEEPHP.Ajax.Q.UpdateTips;
	}

	var oSubmitFrom=document.getElementById(sForm);
	oSubmitFrom.action=sUrl;
	arrAjaxOption={
		async:true,
		onsuccess:function(xhr,responseText){
			QEEPHP.Ajax.Q.AjaxResponse(xhr,QEEPHP.Ajax.Q.TipTarget,Response,sTips);
		},
		onfailure:function(xhr){
			alert('Request Error!');
		}
	};

	/* 提交Ajax */
	QEEPHP.Ajax.Form(oSubmitFrom,arrAjaxOption);
};

QEEPHP.AjaxSubmit=QEEPHP.Ajax.Q.AjaxSubmit;

/**
 * 表单提交ajax
 *
 * @param string url Ajax请求url
 * @param string Params Ajax请求剩余参数
 * @param string target 返回的消息的div id值
 * @param function response 回调函数 
 * @param string sTips  Ajax加载消息
 * @param string sType Ajax发送类型[get/post,默认为get]
 */
QEEPHP.Ajax.Q.AjaxSend=function(sUrl,Params,sTarget,Response,sTips,sType){
	/* 请求URL */
	sUrl=(typeof(sUrl)=='undefined' || sUrl=='' || sUrl===null)?QEEPHP.Ajax.Q.Options['url']:sUrl;

	/* 消息DIV id */
	if(typeof(sTarget)=='undefined' || sTarget =='' || sTarget===null){
		QEEPHP.Ajax.Q.TipTarget=(QEEPHP.Ajax.Q.Options['target'])?QEEPHP.Ajax.Q.Options['target']:QEEPHP.Ajax.Q.TipTarget;
	}else{
		QEEPHP.Ajax.Q.TipTarget=sTarget;
	}

	/* 成功后回调函数 */
	QEEPHP.Ajax.Q.Response=(typeof(Response)=='undefined' || Response=='' || Response===null)?QEEPHP.Ajax.Q.Response:Response;
	if(typeof(sTips)=='undefined' || sTips=='' || sTips===null){
		sTips=(QEEPHP.Ajax.Q.Options['tips'])?QEEPHP.Ajax.Q.Options['tips']:QEEPHP.Ajax.Q.UpdateTips;
	}

	if(typeof(Params)=='undefined' || Params=='' || Params===null){
		Params=(QEEPHP.Ajax.Q.Options['var'])?QEEPHP.Ajax.Q.Options['var']:'ajax=1';
	}

	if(sType=='post'){
		QEEPHP.Ajax.Post(sUrl,
			Params,
			function(xhr,responseText){
				QEEPHP.Ajax.Q.AjaxResponse(xhr,QEEPHP.Ajax.Q.TipTarget,Response,sTips);
			}
		);
 	}else{
		QEEPHP.Ajax.Get(sUrl,
			Params,
			function(xhr,responseText){
				QEEPHP.Ajax.Q.AjaxResponse(xhr,QEEPHP.Ajax.Q.TipTarget,Response,sTips);
			}
		);
	}
};

QEEPHP.AjaxSend=QEEPHP.Ajax.Q.AjaxSend;

/**
 * 语言包的方法
 */
QEEPHP.Lang=QEEPHP.Lang || {};

/**
 * 发送一条语句[AJAX请求PHP太耗性能，重新改进了机制]
 *
 * @param string sSentence 语句
 * @param string sPackageName 语言包名字
 * @returns
 */
QEEPHP.Lang.L=function(sSentence,sPackageName /*=null*/){
	if(_LANG_NAME_!='Zh-cn'){
		eval("if(typeof(arr"+sPackageName+")!='undefined'){var arrLang=arr"+sPackageName+';}');
		if(typeof(arrLang)!='undefined' && Object.prototype.toString.call(arrLang)=='[object Array]'){
			if(typeof(arrLang[sSentence])!='undefined'){
				sSentence=arrLang[sSentence];
			}
		}
	}

	/* 带入参数 */
	arrFormatArgs=[];
	for(nIdx=2; nIdx<arguments.length; nIdx++){
		arrFormatArgs.push(arguments[nIdx]);
	}

	if(arrFormatArgs.length){
		return Q.String.Format(sSentence,arrFormatArgs);
	}else{
		return sSentence;
	}
};

QEEPHP.L=QEEPHP.Lang.L;
