<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   隐身状态控制器($$)*/

!defined('Q_PATH') && exit;

class Stealthonline_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']===false){
			$this->E(Q::L('未登录用户无法转换在线状态','Controller'));
		}

		if($GLOBALS['_option_']['online_on']==0){
			$this->E(Q::L('用户在线功能没有开启','Controller'));
		}

		$nUserid=intval(Q::G('uid'));
		$nUn=intval(Q::G('un'));

		if($GLOBALS['___login___']['user_id']!=$nUserid){
			$this->E(Q::L('你只可以转换自己的在线状态','Controller'));
		}

		// 保存用户配置
		$oUser=UserModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
		if($oUser->user_isstealth==($nUn==1?0:1)){
			$this->E(Q::L('当前在线状态不需要转换','Controller'));
		}

		$oUser->user_isstealth=$nUn==1?0:1;
		$oUser->setAutofill(false);
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}

		// 更新在线表的数据
		$oOnline=OnlineModel::F('user_id=?',$nUserid)->getOne();
		if(!empty($oOnline['user_id'])){
			$oOnline->online_isstealth=($nUn==1?0:1);
			$oOnline->save('update');
		}

		$this->S($nUn==1?Q::L('设置状态为在线成功','Controller'):Q::L('设置状态为隐身成功','Controller'));
	}

}
