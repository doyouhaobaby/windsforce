<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   系统调试模版($$)*/

!defined('Q_PATH') && exit;
?>
<script type="text/javascript">
function setCookie(name,value,expireHours){
	var cookieString=name+"="+escape(value);
	
	if(expireHours>0){
		var date=new Date();
		date.setTime(date.getTime+expireHours*3600*1000);
		cookieString=cookieString+";expire="+date.toGMTString();
	}
	document.cookie=cookieString;
}

function getCookie(name){
	var strcookie=document.cookie;
	var arrcookie=strcookie.split("; ");
	
	for(var i=0;i<arrcookie.length;i++){
		var arr=arrcookie[i].split("=");
		if(arr[0]==name){
			return arr[1];
		}
	}
	return '';
}

sCookiePagetrace=getCookie('__qeephp_pagetrace_open__');
sCookiePagetrace=sCookiePagetrace=='on'?'on':'off';

function initPagetrace(){
	if(sCookiePagetrace=='on'){
		document.getElementById('qeephp_page_trace').style.display='block';document.getElementById('qeephp_pagetrace_button').style.display='none';
	}else{
		document.getElementById('qeephp_page_trace').style.display='none';document.getElementById('qeephp_pagetrace_button').style.display='block';
	}
}

if(typeof jQuery=='undefined'){
	window.onload=initPagetrace;
}else{
	$(document).ready(function(){
		initPagetrace();
	});
}
</script>

<div id="qeephp_page_trace" style="display:none;position:fixed;bottom:0;left:0;background:white;font-size:14px;border-top:2px solid #000;padding:2px;width:100%;">
	<fieldset id="querybox" style="margin:10px;">
		<legend style="color:gray;font-weight:bold;margin-bottom:5px;"><?php echo Q::L('页面Trace信息','__QEEPHP__@Q');?>&nbsp;<a href="javascript:void(0);" onclick="document.getElementById('qeephp_page_trace').style.display='none';document.getElementById('qeephp_pagetrace_button').style.display='block';setCookie('__qeephp_pagetrace_open__','off',24);" style="position:fixed;right:20px;color:#000;">x</a></legend>
		<div style="overflow-x:hidden;height:200px;text-align:left;word-break:break-all;border:1px dashed silver;padding:10px;">
			<?php foreach ($arrTrace as $sKey=>$sInfo){
			echo '<b>'.$sKey.'</b> : '.$sInfo.'<br/>';
			}?>
		</div>
	</fieldset>
</div>
<div id="qeephp_pagetrace_button" style="display:none;position:fixed;bottom:0;right:0;background:#000;font-size:14px;margin:6px;color:#fff;font-weight:bold;border-radius:10px;width:20px;height:20px;text-align:center;cursor:pointer;" onclick="document.getElementById('qeephp_page_trace').style.display='block';document.getElementById('qeephp_pagetrace_button').style.display='none';setCookie('__qeephp_pagetrace_open__','on',24);">T
</div>