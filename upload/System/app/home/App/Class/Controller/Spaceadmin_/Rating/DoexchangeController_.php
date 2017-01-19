<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分兑换处理($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Doexchange_C_Controller extends InitController{

	public function index(){
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$GLOBALS['___login___']['user_id']))
			->getOne();

		// 兑换检测
		$nFromcredits=intval(Q::G('from_credits','P'));
		$nTocredits=intval(Q::G('to_credits','P'));
		$nExchangeamount=intval(Q::G('exchange_amount','P'));
		$sUserpassword=trim(Q::G('password','P'));
		
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();// 可用积分

		if(!$nFromcredits){
			$this->E(Q::L('待兑换的积分不能为空','Controller'));
		}elseif(!array_key_exists($nFromcredits,$arrAvailableExtendCredits)){
			$this->E(Q::L('待兑换的积分不存在','Controller'));
		}elseif($arrAvailableExtendCredits[$nFromcredits]['allowexchangeout']==0){
			$this->E(Q::L('待兑换的积分不允许兑出','Controller'));
		}

		if(!$nTocredits){
			 $this->E(Q::L('兑换成的积分不能为空','Controller'));
		}elseif(!array_key_exists($nTocredits,$arrAvailableExtendCredits)){
			$this->E(Q::L('待兑换的积分不存在','Controller'));
		}elseif($arrAvailableExtendCredits[$nTocredits]['allowexchangein']==0){
			$this->E(Q::L('兑换成的积分不允许兑入','Controller'));
		}

		if($nFromcredits==$nTocredits){
			$this->E(Q::L('同种积分之间无法兑换','Controller'));
		}

		if(!$arrAvailableExtendCredits[$nFromcredits]['ratio']){
			$this->E(Q::L('待兑换的积分的兑换比率必须大于0','Controller'));
		}
		
		if(!$arrAvailableExtendCredits[$nTocredits]['ratio']){
			$this->E(Q::L('兑换成的积分的兑换比率必须大于0','Controller'));
		}
		
		if($arrAvailableExtendCredits[$nTocredits]['ratio']<$arrAvailableExtendCredits[$nFromcredits]['ratio']){
			$nNetamount=ceil($nExchangeamount*$arrAvailableExtendCredits[$nTocredits]['ratio']/$arrAvailableExtendCredits[$nFromcredits]['ratio']*(1+$GLOBALS['_option_']['credit_stax']));
		}else{
			$nNetamount=floor($nExchangeamount*$arrAvailableExtendCredits[$nTocredits]['ratio']/$arrAvailableExtendCredits[$nFromcredits]['ratio']*(1+$GLOBALS['_option_']['credit_stax']));
		}

		if(!$nNetamount){
			$this->E(Q::L('兑换成的积分的数量必须大于0','Controller'));
		}elseif($nExchangeamount<=0){
			$this->E(Q::L('待兑换的积分的数量必须大于0','Controller'));
		}elseif($arrUserInfo['usercount_extendcredit'.$nFromcredits]-$nNetamount<($nExchangemincredits=$GLOBALS['_option_']['exchange_mincredits'])){
			$this->E(Q::L('兑换最低余额不能小于 %d','Controller',null,$nExchangemincredits));
		}
		
		// 验证登录密码
		if(!Q::classExists('Auth')){
			require_once(Core_Extend::includeFile('class/Auth'));
		}

		Auth::checkLogin($GLOBALS['___login___']['user_name'],$sUserpassword,false,86400,true);
		if(Auth::isError()){
			$this->E(Q::L('登录密码输入失败','Controller').'<br/>'.Auth::getErrorMessage());
		}

		// 确认兑换
		try{
			Credit_Extend::updateUsercount($GLOBALS['___login___']['user_id'],array('extcredits'.$nFromcredits=>-$nNetamount,'extcredits'.$nTocredits=>$nExchangeamount),'exchange',$GLOBALS['___login___']['user_id']);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$this->assign('__JumpUrl__',Q::U('home://spaceadmin/exchange'));
		$this->S(Q::L('兑换成功','Controller'));
	}

}
