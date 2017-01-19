function getStatusImg(nember){
	switch (nember){
		case 0 :
			return _ROOT_ + "/Public/js/swfupload/images/wait.png";
		case 1 :
			return _ROOT_ + "/Public/js/swfupload/images/underway.png";
		case 2 :
			return _ROOT_ + "/Public/js/swfupload/images/save.png";
		case 3 :
			return _ROOT_ + "/Public/js/swfupload/images/success.png";
		default : 
			return _ROOT_ + "/Public/js/swfupload/images/wait.png";
	}	
}

function getFormatSize(size){	
	if(size > 1024000000)
		return getFloat(parseFloat((size / 1024000000).toString()).toString()) + "G";
	if(size > 1024000)
		return getFloat(parseFloat((size / 1024000).toString()).toString()) + "M";
	if(size > 1024)
		return getFloat(parseFloat((size / 1024).toString()).toString()) + "K";
	return size.toString() + "B";
}

function getPercentage(bytes,total){
	var width = parseFloat(bytes / total * 100);
	return getFloat(width.toString());
}

function getPercentageimg(bytes,total){
	var width = parseFloat(bytes / total * 100);
	width = width / 100 * 105;
	return getFloat(width.toString());
}

function getFloat(Float){
	if(Float.indexOf(".") == -1)
		return 	Float;
	var any = Float.split(".");
		return any[0].toString() +"."+ any[1].substr(0,1);
}

function setBotton(name, state){
	switch (state){
		case 0 :
			$("#" + name).css({"backgroundPosition":"left top","cursor":"pointer"});
			$("#" + name).attr("disabled", false);
			break;
		case 1 :
			$("#" + name).css({"backgroundPosition":"0 -26px","cursor":"pointer"});
			$("#" + name).attr("disabled", false);
			break;
		case 2 :
			$("#" + name).css({"backgroundPosition":"0 -52px","cursor":"pointer"});
			$("#" + name).attr("disabled", false);
			break;
		case 3 :
			$("#" + name).css({"backgroundPosition":"left bottom","cursor":"default"});
			$("#" + name).attr("disabled", true);
			break;
	}
}

function setMessage(message,error){
	$("#messageinfo").show();
	$("#messageinfo").html(message);
	if(error==1){
		$("#messageinfo").css({"color":"red"});
	}else{
		$("#messageinfo").css({"color":"blue"});
	}
	setTimeout(function(){$("#messageinfo").hide();},2000);
}

function getbyte(str){
	if(str.toLowerCase().indexOf('kb') > -1)
		return parseInt(str) * 1024;
	if(str.toLowerCase().indexOf('mb') > -1)
		return parseInt(str) * 1024 * 1024;
	if(str.toLowerCase().indexOf('gb') > -1)
		return parseInt(str) * 1024 * 1024 * 1024;
	
	return str;
}
