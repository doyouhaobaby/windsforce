<?php  /* QeePHP 模板缓存文件生成时间：2014-05-19 18:58:03  */ ?>
<!--<####incl*bffd9f045349c31b262049b68e3aba73*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_header.html',__FILE__,"TEMPLATE_PATH.'/common_header.html'");?><!--</####incl*bffd9f045349c31b262049b68e3aba73*ude####/>--><ul class="breadcrumb"><li><a href="<?php echo(__APP__);?>" title="<?php echo Q::L("主页",'Template/Default/Common',null);?>"><?php echo Q::L("主页",'Template/Default/Common',null);?></a>&nbsp;<span class="divider">/</span></li><li><?php echo Q::L("安装成功",'Template/Default/Install',null);?></li></ul><!--<####incl*dc6795ba8f206069eafc77b64b4a766a*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/install_process.html',__FILE__,"TEMPLATE_PATH.'/install_process.html'");?><!--</####incl*dc6795ba8f206069eafc77b64b4a766a*ude####/>--><div class="row"><div class="span12"><h2><?php echo Q::L("恭喜你安装成功",'Template/Default/Install',null);?></h2><p><?php echo Q::L("尊敬的用户，你已经成功安装了WindsForce",'Template/Default/Install',null);?>-<?php echo(WINDSFORCE_SERVER_VERSION);?>，<?php echo Q::L("系统将进入首页",'Template/Default/Install',null);?><br/><blockquote><em><?php echo Q::L("浏览器会在3秒后自动跳转页面，无需人工干预",'Template/Default/Install',null);?></em></blockquote></p></div></div><script type="text/javascript">setTimeout(function(){window.location='<?php echo($sBaseurl);?>/index.php';},3000);</script><!--<####incl*9d402c1357b8e2869584aba1763e5d48*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_footer.html',__FILE__,"TEMPLATE_PATH.'/common_footer.html'");?><!--</####incl*9d402c1357b8e2869584aba1763e5d48*ude####/>-->