<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   执行转账($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Dotransfer_C_Controller extends InitController{

	public function index(){
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$GLOBALS['___login___']['user_id']))
			->getOne();

		// 转账检测
		$nTransferamount=intval(Q::G('transfer_amount','P'));
		$sTousername=trim(Q::G('to_username','P'));
		$sUserpassword=trim(Q::G('password','P'));
		$sTransfermessage=trim(Q::G('transfer_message','P'));
		$nCreditType=intval(Q::G('credit_type','P'));

		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();// 可用积分
		if(!array_key_exists($nCreditType,$arrAvailableExtendCredits) || $nCreditType==0){
			$this->E(Q::L('转账的积分类型错误','Controller'));
		}

		if($nTransferamount<=0){
			$this->E(Q::L('你要转账的积分输入有误','Controller'));
		}elseif($arrUserInfo['usercount_extendcredit'.$nCreditType]-$nTransferamount<($nTransferminCredits=$GLOBALS['_option_']['transfermin_credits'])){
			$this->E(Q::L('转账最低余额不能小于 %d','Controller',null,$nTransferminCredits));
		}elseif(!($nNetamount=floor($nTransferamount*(1-$GLOBALS['_option_']['credit_stax'])))){
			$this->E(Q::L('扣除积分交易税后余额为0','Controller'));
		}

		if(!$sTousername){
			$this->E(Q::L('接收转账的用户不能为空','Controller'));
		}

		if($sTousername==$GLOBALS['___login___']['user_name']){
			$this->E(Q::L('你不能给自己转账','Controller'));
		}

		$arrTouser=Model::F_('user','user_name=?',$sTousername)->setColumns('user_id')->getOne();
		if(empty($arrTouser['user_id'])){
			$this->E(Q::L('接收转账的用户不存在','Controller'));
		}

		// 验证登录密码
		if(!Q::classExists('Auth')){
			require_once(Core_Extend::includeFile('class/Auth'));
		}

		Auth::checkLogin($GLOBALS['___login___']['user_name'],$sUserpassword,false,86400,true);
		if(Auth::isError()){
			$this->E(Q::L('登录密码输入失败','Controller').'<br/>'.Auth::getErrorMessage());
		}

		// 确认转账
		try{
			Credit_Extend::updateUsercount($GLOBALS['___login___']['user_id'],array('extcredits'.$nCreditType=>-$nTransferamount),'transferout',$arrTouser['user_id']);
			Credit_Extend::updateUsercount($arrTouser['user_id'],array('extcredits'.$nCreditType=>$nNetamount),'transferin',$GLOBALS['___login___']['user_id']);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 发送提醒
		$sCreditchange=$arrAvailableExtendCredits[$nCreditType]['title'].'&nbsp;+'.$nNetamount.'&nbsp;&nbsp;';
		
		$sNoticetemplate='<div class="notice_credit"><span class="notice_title">'.Q::L('您收到一笔来自','Controller').'&nbsp;<a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('的积分转账','Controller').'&nbsp;'.$sCreditchange.'</span>';
		
		if($sTransfermessage){
			$sNoticetemplate.='<div class="notice_content"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('说','Controller').':&nbsp;<span class="notice_quote"><span class="notice_quoteinfo">'.$sTransfermessage.'</span></span></div>';
		};
		
		$sNoticetemplate.='<div class="notice_action"><a href="{@creditlog_link}">'.Q::L('查看','Controller').'</a></div></div>';

		$arrNoticedata=array(
			'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
			'user_name'=>$GLOBALS['___login___']['user_name'],
			'@creditlog_link'=>'home://spaceadmin/creditlog',
		);

		try{
			Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$arrTouser['user_id'],'credit');
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$this->assign('__JumpUrl__',Q::U('home://spaceadmin/transfer'));
		$this->S(Q::L('转账成功','Controller'));
	}

}
