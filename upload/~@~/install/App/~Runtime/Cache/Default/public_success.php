<?php  /* QeePHP 模板缓存文件生成时间：2014-05-19 16:29:08  */ ?>
<!--<####incl*bffd9f045349c31b262049b68e3aba73*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_header.html',__FILE__,"TEMPLATE_PATH.'/common_header.html'");?><!--</####incl*bffd9f045349c31b262049b68e3aba73*ude####/>--><ul class="breadcrumb"><li><a href="<?php echo(__APP__);?>" title="<?php echo Q::L("主页",'Template/Default/Common',null);?>"><?php echo Q::L("主页",'Template/Default/Common',null);?></a>&nbsp;<span class="divider">/</span></li><li><?php echo($__MessageTitle__);?></li></ul><div class="row"><div class="span2">&nbsp;</div><div class="span8"><div class="well"><h3><?php echo($__MessageTitle__);?></h3><?php if(isset( $__Message__ ) AND !empty( $__Message__ )):?><p><img src="<?php echo($__InfobigImg__);?>" style="margin-right:10px;"/><?php echo($__Message__);?></p><?php endif;?><?php if(isset( $__ErrorMessage__ ) AND !empty( $__ErrorMessage__ )):?><p><img src="<?php echo($__ErrorbigImg__);?>" style="margin-right:10px;"/><?php echo($__ErrorMessage__);?></p><?php endif;?><p><div id="__Loader__"><img src="<?php echo($__LoadingImg__);?>"/></div></p><p id="__JumpUrl__"><?php if(isset( $__CloseWindow__ ) AND !empty( $__CloseWindow__ )):?><span class="red" id="__Seconds__"><?php echo($__WaitSecond__);?></span>&nbsp;<?php echo Q::L("系统即将自动关闭",'Template/Default/Common',null);?><?php echo Q::L("如果不想等待,直接点击",'Template/Default/Common',null);?>&nbsp;<a href="<?php echo($__JumpUrl__);?>" class="lightlink"><?php echo Q::L("这里",'Template/Default/Common',null);?></a>&nbsp;<?php echo Q::L("关闭",'Template/Default/Common',null);?><?php endif;?><?php if(!isset( $__CloseWindow__ ) OR empty( $__CloseWindow__ )):?><span class="red" id="__Seconds__"><?php echo($__WaitSecond__);?></span>&nbsp;<?php echo Q::L("系统即将自动跳转",'Template/Default/Common',null);?><?php echo Q::L("如果不想等待,直接点击",'Template/Default/Common',null);?>&nbsp;<a href="<?php echo($__JumpUrl__);?>" class="lightlink "><?php echo Q::L("这里",'Template/Default/Common',null);?></a>&nbsp;<?php echo Q::L("跳转",'Template/Default/Common',null);?><?php endif;?></p></div></div><div class="span2">&nbsp;</div></div><?php echo($__JavaScript__);?><!--<####incl*9d402c1357b8e2869584aba1763e5d48*ude####>--><?php $this->includeChildTemplate(TEMPLATE_PATH.'/common_footer.html',__FILE__,"TEMPLATE_PATH.'/common_footer.html'");?><!--</####incl*9d402c1357b8e2869584aba1763e5d48*ude####/>-->