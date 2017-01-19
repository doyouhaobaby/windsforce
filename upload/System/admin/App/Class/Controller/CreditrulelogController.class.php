<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户系统奖励控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class CreditrulelogController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function filter_(&$arrMap){
		$arrMap['A.creditrulelog_total']=array('egt',"%".intval(Q::G('creditrulelog_total'))."%");
		$arrMap['A.creditrulelog_cyclenum']=array('egt',"%".intval(Q::G('creditrulelog_cyclenum'))."%");
		$arrMap['C.creditrule_name']=array('like',"%".Q::G('creditrule_name')."%");
		$arrMap['B.user_name']=array('like',"%".Q::G('user_name')."%");
		
		$nUserid=intval(Q::G('uid','G'));
		$oUser=UserModel::F('user_id=?',$nUserid)->getOne();
		if(!empty($oUser['user_id'])){
			$arrMap['A.user_id']=$nUserid;
			$this->assign('oUser',$oUser);
		}

		// 更新时间
		$this->getTime_('A.update_dateline',$arrMap);
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."user AS B','B.user_name','A.user_id=B.user_id')".
			"->joinLeft('".Q::C('DB_PREFIX')."creditrule AS C','C.creditrule_name','A.creditrule_id=C.creditrule_id')";
	}

	public function bIndex_(){
		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
	}

	public function clear(){
		$nUserid=intval(Q::G('id'));
		if(empty($nUserid)){
			$this->E(Q::L('你没有指定待清空系统奖励数据的用户','Controller'));
		}

		$oUser=UserModel::F('user_id=?',$nUserid)->getOne();
		if(empty($oUser['user_id'])){
			$this->E(Q::L('待清空系统奖励数据的用户不存在','Controller'));
		}
		
		// 执行删除
		$oCreditrulelogMeta=CreditrulelogModel::M();
		$oCreditrulelogMeta->deleteWhere(array('user_id'=>$nUserid));
		if($oCreditrulelogMeta->isError()){
			$this->E($oCreditrulelogMeta->getErrorMessage());
		}

		$this->S(Q::L('清空系统奖励数据成功','Controller'));
	}

}
