<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户积分收益控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class CreditlogController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function filter_(&$arrMap){
		$arrMap['D.user_name']=array('like',"%".Q::G('user_name_2')."%");
		$arrMap['C.creditoperation_title']=array('like',"%".Q::G('creditlog_operation')."%");
		$arrMap['B.user_name']=array('like',"%".Q::G('user_name')."%");

		$nUserid=intval(Q::G('uid','G'));
		$oUser=UserModel::F('user_id=?',$nUserid)->getOne();
		if(!empty($oUser['user_id'])){
			$arrMap['A.user_id']=$nUserid;
			$this->assign('oUser',$oUser);
		}

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."user AS B','B.user_name','A.user_id=B.user_id')".
			"->joinLeft('".Q::C('DB_PREFIX')."creditoperation AS C','C.creditoperation_title','A.creditlog_operation=C.creditoperation_name')".
			"->joinLeft('".Q::C('DB_PREFIX')."user AS D','D.user_name AS to_username','A.creditlog_relatedid=D.user_id')";
	}

	public function bIndex_(){
		$sOrder=Q::G('order_');
		if(empty($sOrder)){
			$this->U('creditlog/index?order_=create_dateline');
		}

		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
	}

	public function clear(){
		$nUserid=intval(Q::G('id'));
		if(empty($nUserid)){
			$this->E(Q::L('你没有指定待清空积分收益数据的用户','Controller'));
		}

		$oUser=UserModel::F('user_id=?',$nUserid)->getOne();
		if(empty($oUser['user_id'])){
			$this->E(Q::L('待清空积分收益数据的用户不存在','Controller'));
		}

		// 执行删除
		$oCreditlogMeta=CreditlogModel::M();
		$oCreditlogMeta->deleteWhere(array('user_id'=>$nUserid));
		if($oCreditlogMeta->isError()){
			$this->E($oCreditlogMeta->getErrorMessage());
		}

		$this->S(Q::L('清空积分收益数据成功','Controller'));
	}

	public function system(){
		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
		$this->display();
	}

	public function systemset(){
		$sUserName=trim(Q::G('user_name'));
		$nCreditNum=intval(Q::G('credit_num'));
		$nCreditType=intval(Q::G('credit_type'));
		$sTransfermessage=trim(Q::G('credit_remark'));

		if(!$sUserName){
			$this->S(Q::L('用户不能为空','Controller'));
		}

		$oUser=UserModel::F('user_name=? OR user_id=?',$sUserName,$sUserName)->setColumns('user_id')->getOne();
		if(empty($oUser['user_id'])){
			$this->S(Q::L('用户不存在','Controller'));
		}

		if($nCreditNum=='' || $nCreditNum==0){
			$this->S(Q::L('积分数量为空','Controller'));
		}

		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		if(!$nCreditType){
			$this->S(Q::L('变更积分类型不能为空','Controller'));
		}
		if(!array_key_exists($nCreditType,$arrAvailableExtendCredits)){
			$this->S(Q::L('变更积分类型不可用','Controller'));
		}

		// 确认转账
		try{
			Credit_Extend::updateUsercount($GLOBALS['___login___']['user_id'],array('extcredits'.$nCreditType=>$nCreditNum),($nCreditNum>0?'systemin':'systemout'),$oUser['user_id']);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 发送提醒
		$sCreditchange=$arrAvailableExtendCredits[$nCreditType]['title'].'&nbsp;+'.$nCreditNum.'&nbsp;&nbsp;';
		
		$sNoticetemplate='<div class="notice_credit"><span class="notice_title">'.Q::L('您收到一笔来自','Controller').'&nbsp;<a href="{@space_link}">{user_name}('.Q::L('系统','Controller').')</a>&nbsp;'.Q::L('的积分转账','Controller').'&nbsp;'.$sCreditchange.'</span>';
		
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
			Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oUser['user_id'],'credit');
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
		
		$this->S(Q::L('积分变更成功','Controller'));
	}

	public function foreverdelcreditlog(){
		$nUserId=intval(Q::G('id'));
		$nCreditlogRelatedid=intval(Q::G('creditlog_relatedid'));
		$nCreateDateline=intval(Q::G('create_dateline'));

		if(!$nUserId || !$nCreditlogRelatedid || !$nCreateDateline){
			$this->S(Q::L('参数错误','Controller'));
		}

		// 执行删除
		$oCreditlogMeta=CreditlogModel::M();
		$oCreditlogMeta->deleteWhere(array('user_id'=>$nUserId,'creditlog_relatedid'=>$nCreditlogRelatedid,'create_dateline'=>$nCreateDateline));
		if($oCreditlogMeta->isError()){
			$this->E($oCreditlogMeta->getErrorMessage());
		}

		$this->S(Q::L('删除积分收益数据成功','Controller'));
	}

}
