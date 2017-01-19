<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   时间设置控制器($$)*/

!defined('Q_PATH') && exit;

class DateoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$arrTimezones=array('CET','CST6CDT','Cuba','EET','Egypt','Eire','EST','EST5EDT','Etc/GMT','Etc/GMT+0',
							'Etc/GMT+1','Etc/GMT+10','Etc/GMT+11','Etc/GMT+12','Etc/GMT+2','Etc/GMT+3','Etc/GMT+4',
							'Etc/GMT+5','Etc/GMT+6','Etc/GMT+7','Etc/GMT+8','Etc/GMT+9','Etc/GMT-0','Etc/GMT-1',
							'Etc/GMT-10','Etc/GMT-11','Etc/GMT-12','Etc/GMT-13','Etc/GMT-14','Etc/GMT-2','Etc/GMT-3',
							'Etc/GMT-4','Etc/GMT-5','Etc/GMT-6','Etc/GMT-7','Etc/GMT-8','Etc/GMT-9','Etc/GMT0','Etc/Greenwich',
							'Etc/UCT','Etc/Universal','Etc/UTC','Etc/Zulu','Factory','GB','GB-Eire','GMT','GMT+0','GMT-0','GMT0',
							'Greenwich','Hongkong','HST','Iceland','Iran','Israel','Jamaica','Japan','Kwajalein','Libya','MET',
							'MST','MST7MDT','Navajo','NZ','NZ-CHAT','Poland','Portugal','PRC','PST8PDT','ROC','ROK',
							'Singapore','Turkey','UCT','Universal','UTC','W-SU','WET','Zulu','Asia/Chongqing','Asia/Shanghai',
							'Asia/Urumqi,Asia/Macao','Asia/Hong_Kong','Asia/Taipei','Asia/Singapore');

		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('arrTimezones',$arrTimezones);
		$this->display();
	}

	public function update_option(){
		if($_POST['options']['timeoffset']!=$GLOBALS['_option_']['timeoffset']){
			$sAppGlobaldefaultconfigFile=WINDSFORCE_PATH.'/~@~/Config.inc.php';
			if(!is_file($sAppGlobaldefaultconfigFile)){
				$this->E(Q::L('框架全局惯性配置文件 %s 不存在','Controller',null,$sAppGlobaldefaultconfigFile));
			}

			Core_Extend::changeAppconfig('TIME_ZONE',$_POST['options']['timeoffset']);
		}

		parent::update_option();
	}

}
