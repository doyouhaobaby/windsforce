<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Ubb控制器($$)*/
   
!defined('Q_PATH') && exit;

class Ubb_C_Controller extends InitController{
	
	public function index(){
		$sType=trim(Q::G('type','G'));
		if(!in_array($sType,array('','sign'))){
			$sType='sign';
		}
		
		if($sType=='sign'){
			$this->sign();
			return;
		}
		
		$arrUbbbases=array(
			'b'=>array('[b]'.Q::L('粗体文字','__COMMON_LANG__@Common').' Abc[/b]'),
			'i'=>array('[i]'.Q::L('斜体文字','__COMMON_LANG__@Common').' Abc[/i]'),
			'u'=>array('[u]'.Q::L('下划线文字','__COMMON_LANG__@Common').' Abc[/u]'),
			'strike'=>array('[strike]'.Q::L('删除线文字','__COMMON_LANG__@Common').' Abc[/strike]'),
			'color'=>array('[color=red]'.Q::L('红颜色','__COMMON_LANG__@Common').'[/color]'),
			'size'=>array('[size=3]'.Q::L('文字大小为','__COMMON_LANG__@Common').' 3[/size]'),
			'font'=>array('[font='.Q::L('仿宋','__COMMON_LANG__@Common').']'.Q::L('字体为仿宋','__COMMON_LANG__@Common').'[/font]'),
			'strong'=>array('[strong]'.Q::L('强调文字','__COMMON_LANG__@Common').' Abc[/strong]'),
			'align'=>array('[p align=center]'.Q::L('内容居中','__COMMON_LANG__@Common').'[/p]'),
			'url'=>array('[url]'.$GLOBALS['_option_']['windsforce_program_url'].'[/url]',Q::L('超级链接','__COMMON_LANG__@Common')),			'url2'=>array('[url='.$GLOBALS['_option_']['windsforce_program_url'].']$'.$GLOBALS['_option_']['windsforce_program_name'].' '.Q::L('开发框架','__COMMON_LANG__@Common').'[/url]',Q::L('超级链接','__COMMON_LANG__@Common').''),
			'url3'=>array(' '.$GLOBALS['_option_']['windsforce_program_url'],Q::L('自动添加链接','__COMMON_LANG__@Common').''),
			'email'=>array('[email]'.$GLOBALS['_option_']['admin_email'].'[/email]',''.Q::L('Email链接','__COMMON_LANG__@Common').''),
			'quote'=>array('[quote]$'.$GLOBALS['_option_']['windsforce_program_name'].' App Framework | '.Q::L('由WindsForce TEAM开发的APP框架软件','__COMMON_LANG__@Common').'[/quote]'),
			'blockquote'=>array('[blockquote]Hello world![/blockquote]'),
			'code'=>array('[code]$'.$GLOBALS['_option_']['windsforce_program_name'].' App Framework | '.Q::L('由WindsForce TEAM开发的APP框架软件','__COMMON_LANG__@Common').'[/code]'),
			'php'=>array('[php]<?php echo \'Hello world!\';?>[/php]',Q::L('源代码因为标签特殊的原因不可见色','__COMMON_LANG__@Common').''),
			'hide'=>array('[hide]'.Q::L('隐藏内容','__COMMON_LANG__@Common').' Abc[/hide]',Q::L('仅登陆用户才能看到','__COMMON_LANG__@Common')),
			'img'=>array('[img]'.Core_Extend::getSiteurl().'/Public/images/common/iconlogo/logo.gif[/img]'),
			'sup'=>array('X[sup]2[/sup]'),
			'sub'=>array('X[sub]2[/sub]'),
			'hr'=>array('Hello [hr] world!'),
			'br'=>array('Hello [br]world!'),			'acronym'=>array('[acronym=12]'.strtoupper($GLOBALS['_option_']['windsforce_program_name']).'[/acronym]',Q::L('首字母的缩写词','__COMMON_LANG__@Common')),
			'fly'=>array('[fly]'.Q::L('飞行效果','__COMMON_LANG__@Common').'[/fly]'),
		);
		
		$arrUbbadvs=array(
			'attachment'=>array('[attachment]1[/attachment]',Q::L('解析内部附件,如果附件不存在将显示为空','__COMMON_LANG__@Common')),
			'mp3'=>array('[mp3]'.Core_Extend::getSiteurl().'/Public/images/common/sound/pm_1.mp3[/mp3]',Q::L('仅支持MP3格式音乐','__COMMON_LANG__@Common')),			'video'=>array('[video]'.Core_Extend::getSiteurl().'/Public/images/common/ubb_flash.swf[/video]',Q::L('支持swf,asf,wmv,avi,rm,rmvb,flv,mp4','__COMMON_LANG__@Common')),
			'tag'=>array('[TAG]#'.Q::L('新鲜事话题','__COMMON_LANG__@Common').'#[/TAG]'),
			'message'=>array('[MESSAGE]@admin[/MESSAGE]',Q::L('新鲜事中的@功能，仅UBB过滤函数参数不同','__COMMON_LANG__@Common')),
			'message2'=>array('[MESSAGE]@admin[/MESSAGE]',Q::L('其它地方中的@功能，仅UBB过滤函数参数不同','__COMMON_LANG__@Common')),
		);

		Core_Extend::getSeo($this,array('title'=>Q::L('UBB代码','Controller')));
		
		$this->assign('arrUbbbases',$arrUbbbases);
		$this->assign('arrUbbadvs',$arrUbbadvs);
		$this->assign('sType',$sType);
		$this->display('misc+ubb');
	}
	

	public function sign(){
		$arrUbbs=array(
			'b'=>array('[b]'.Q::L('粗体文字','__COMMON_LANG__@Common').' Abc[/b]'),
			'i'=>array('[i]'.Q::L('斜体文字','__COMMON_LANG__@Common').' Abc[/i]'),
			'u'=>array('[u]'.Q::L('下划线文字','__COMMON_LANG__@Common').' Abc[/u]'),
			'strike'=>array('[strike]'.Q::L('删除线文字','__COMMON_LANG__@Common').' Abc[/strike]'),
			'color'=>array('[color=red]'.Q::L('红颜色','__COMMON_LANG__@Common').'[/color]'),
			'size'=>array('[size=3]'.Q::L('文字大小为','__COMMON_LANG__@Common').' 3[/size]'),
			'url'=>array('[url]'.$GLOBALS['_option_']['windsforce_program_url'].'[/url]',Q::L('超级链接','__COMMON_LANG__@Common')),			'url2'=>array('[url='.$GLOBALS['_option_']['windsforce_program_url'].']$'.$GLOBALS['_option_']['windsforce_program_name'].' '.Q::L('开发框架','__COMMON_LANG__@Common').'[/url]',Q::L('超级链接','__COMMON_LANG__@Common')),
			'url3'=>array(' '.$GLOBALS['_option_']['windsforce_program_url'],Q::L('自动添加链接','__COMMON_LANG__@Common')),
			'email'=>array('[email]'.$GLOBALS['_option_']['admin_email'].'[/email]',Q::L('Email链接','__COMMON_LANG__@Common')),
			'img'=>array('[img]'.Core_Extend::getSiteurl().'/Public/images/common/iconlogo/logo.gif[/img]'),
			'sup'=>array('X[sup]2[/sup]'),
			'sub'=>array('X[sub]2[/sub]'),
			'hr'=>array('Hello [hr] world!'),
			'br'=>array('Hello [br]world!'),
		);

		Core_Extend::getSeo($this,array('title'=>Q::L('签名UBB代码','Controller')));
		
		$this->assign('arrUbbs',$arrUbbs);
		$this->assign('sType','sign');
		$this->display('misc+ubbsign');
	}
	

}
