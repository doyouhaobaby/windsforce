<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   推荐小组Api控制器($$)*/

!defined('Q_PATH') && !defined('IN_API') && !defined('IN_APISELF') && exit;

class Recommendgroup_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nNum=intval(Q::G('num','G'));
		$nCutNum=intval(Q::G('cnum','G'));
		$sType=strtolower(trim(Q::G('type','G')));

		// 基本处理
		if($nNum<2){
			$nNum=2;
		}

		if($nNum>30){
			$nNum=30;
		}

		if(empty($nCutNum)){
			$nCutNum=30;
		}

		// 获取小组
		$arrGroups=Model::F_('group','group_isrecommend=? AND group_status=1',1)
			->setColumns('group_id,group_icon,group_totaltodaynum,group_isopen,group_topicnum,group_topiccomment,group_usernum,group_listdescription,group_nikename,group_name,group_color,group_isrecommend')
			->order('group_id DESC')
			->limit(0,$nNum)
			->getAll();

		$arrData=array();
		if(!empty($arrGroups)){
			foreach($arrGroups as $arrGroup){
				$arrGroup['url']=Core_Extend::getSiteurl().Q::U('group://gid@?id='.($arrGroup['group_name']?$arrGroup['group_name']:$arrGroup['group_id']));
				if($arrGroup['group_icon']){
					$arrGroup['group_icon']=Core_Extend::getSiteurl().__ROOT__.'/user/attachment/app/group/icon/'.$arrGroup['group_icon'];
				}else{
					$arrGroup['group_icon']=Core_Extend::getSiteurl().__ROOT__.'/System/app/group/Theme/Default/Public/Images/group_icon.gif';
				}
				$arrData['k_'.$arrGroup['group_id']]=$arrGroup;
			}
		}

		Core_Extend::api($arrData,$sType);
		exit();
	}

}
