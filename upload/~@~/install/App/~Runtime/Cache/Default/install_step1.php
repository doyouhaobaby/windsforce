<?php  /* QeePHP 模板缓存文件生成时间：2014-06-26 04:46:52  */ ?>
<!--<####incl*bffd9f045349c31b262049b68e3aba73*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_header.html',__FILE__,"TEMPLATE_PATH.'/common_header.html'");?><!--</####incl*bffd9f045349c31b262049b68e3aba73*ude####/>--><ul class="breadcrumb"><li><a href="<?php echo(__APP__);?>" title="<?php echo Q::L("主页",'Template/Default/Common',null);?>"><?php echo Q::L("主页",'Template/Default/Common',null);?></a>&nbsp;<span class="divider">/</span></li><li><?php echo Q::L("阅读许可协议",'Template/Default/Install',null);?></li></ul><!--<####incl*dc6795ba8f206069eafc77b64b4a766a*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/install_process.html',__FILE__,"TEMPLATE_PATH.'/install_process.html'");?><!--</####incl*dc6795ba8f206069eafc77b64b4a766a*ude####/>--><div class="row"><div class="span12"><h2>WindsForce&nbsp;<?php echo Q::L("软件使用许可协议",'Template/Default/Install',null);?></h2><p><div style="margin-bottom:15px; padding:8px; height:280px; border:1px solid #EEE; background:#FFF; overflow:scroll; overflow-x:hidden;"><?php echo($sCopyTxt);?></div></p><form class="alert alert-success"><label class="checkbox"><input name="readpact" type="checkbox" id="readpact"><?php echo Q::L("我已经阅读并同意此协议",'Template/Default/Install',null);?></label></form></div></div><div class="row"><div class="span12"><div class="well"><p><input type="button" class="btn" value="<?php echo Q::L("后退",'Template/Default/Common',null);?>" onclick="window.location.href='<?php echo(Q::U( 'public/select' ));?>';" /><span class="pipe">|</span><input type="button" class="btn btn-success" value="<?php echo Q::L("继续",'Template/Default/Common',null);?>" onclick="$WF('readpact').checked ?window.location.href='<?php echo(Q::U( 'public/step2' ));?>' : alert('<?php echo Q::L("您必须同意软件许可协议才能安装！",'Template/Default/Install',null);?>');" /></p></div></div></div><!--<####incl*9d402c1357b8e2869584aba1763e5d48*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_footer.html',__FILE__,"TEMPLATE_PATH.'/common_footer.html'");?><!--</####incl*9d402c1357b8e2869584aba1763e5d48*ude####/>-->