<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   最新用户Api控制器($$)*/

!defined('Q_PATH') && exit;
!defined('IN_API') && !defined('IN_APISELF') && exit;

class Newuser_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nNum=intval(Q::G('num','G'));
		$sType=strtolower(trim(Q::G('type','G')));

		// 基本处理
		if($nNum<2){
			$nNum=2;
		}

		if($nNum>USER_NEW_MAXNUM){
			$nNum=USER_NEW_MAXNUM;
		}

		if(empty($sType) || !in_array($sType,array('xml','json'))){
			$sType=USER_NEW_DEFAULTRETURNTYPE;
		}

		// 获取最新用户
		$arrData=array();
		
		$arrUsers=Model::F_('user','user_status=?',1)->order('user_id DESC')
			->limit(0,$nNum)
			->setColumns('user_id,user_name,user_nikename,user_email')
			->getAll();
		if(!empty($arrUsers)){
			foreach($arrUsers as $arrUser){
				// 基本信息
				$arrData['k_'.$arrUser['user_id']]['user_name']=$arrUser['user_name'];
				$arrData['k_'.$arrUser['user_id']]['user_nikename']=$arrUser['user_nikename'];
				$arrData['k_'.$arrUser['user_id']]['user_email']=$arrUser['user_email'];
				
				// 用户头像
				$arrData['k_'.$arrUser['user_id']]['user_avatarbig']=Core_Extend::avatar($arrUser['user_id'],'big',true);
				$arrData['k_'.$arrUser['user_id']]['user_avatarmiddle']=Core_Extend::avatar($arrUser['user_id'],'middle',true);
				$arrData['k_'.$arrUser['user_id']]['user_avatarsmall']=Core_Extend::avatar($arrUser['user_id'],'small',true);
			}
		}

		Core_Extend::api($arrData,$sType,true);
		exit();
	}

}
