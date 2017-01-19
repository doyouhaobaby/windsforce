<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   URL自动跳转模版($$)*/

!defined('Q_PATH') && exit;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if(isset($sHeader)):?>
<?php echo $sHeader;?>
<?php endif;?>
<title>QeePHP Url Wait</title>
<style type="text/css">
body {
	background-color:#F7F7F7;
	font-family: Arial;
	font-size: 12px;
	line-height:150%;
}

.main {
	background-color:#FFFFFF;
	font-size: 12px;
	color: #666666;
	width:<?php echo $nWidth;?>px;
	margin:60px auto 0px;
	border-radius: 10px;
	padding:30px 10px;
	list-style:none;
	border:#DFDFDF 1px solid;
}

.main p {
	line-height: 18px;
	margin: 5px 20px;
}
</style>

<script type="text/javascript">
function run(){
	var s=document.getElementById("sec");
	
	if(s.innerHTML==0){
		{$url}
		return false;
	}
	s.innerHTML=s.innerHTML*1-1;
}

window.setInterval("run();", 1000);
</script>
</head>
<body>
	<div class="main">
		<p><?php echo $sMsg;?></p>
		<p>Please wait for a while...<span id="sec" style="color:blue;"><?php echo $nTime;?></span> Seconds.</p>
	</div>
</body>
</html>