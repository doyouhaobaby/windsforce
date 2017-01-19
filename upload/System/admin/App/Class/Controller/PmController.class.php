<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息管理控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入短消息函数 */
require_once(Core_Extend::includeFile('function/Pm_Extend'));

class PmController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.pm_msgfrom']=array('like',"%".Q::G('pm_msgfrom')."%");
		$arrMap['A.pm_message']=array('like',"%".Q::G('pm_message')."%");
		$arrMap['A.pm_fromapp']=array('like',"%".Q::G('pm_fromapp')."%");
		$arrMap['A.pm_subject']=array('like',"%".Q::G('pm_subject')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		$sType=trim(Q::G('type'));
		if(!empty($sType)){
			$arrMap['A.pm_type']=$sType;
		}

		$this->assign('sType',$sType);
	}

	public function aForeverdelete_deep($sId){
		// 删除数据相关的记录
		$oPmsystemdeleteMeta=PmsystemdeleteModel::M();
		$oPmsystemdeleteMeta->deleteWhere(array('pm_id'=>array('in',$sId)));
		if($oPmsystemdeleteMeta->isError()){
			$this->E($oPmsystemdeleteMeta->getErrorMessage());
		}

		$oPmsystemreadMeta=PmsystemreadModel::M();
		$oPmsystemreadMeta->deleteWhere(array('pm_id'=>array('in',$sId)));
		if($oPmsystemreadMeta->isError()){
			$this->E($oPmsystemreadMeta->getErrorMessage());
		}
	}

	public function show(){
		$nId=Q::G('id','G');

		if(!empty($nId)){
			$oModel=PmModel::F('pm_id=?',$nId)->query();
			if(!empty($oModel->pm_id)){
				$this->assign('oValue',$oModel);
				$this->assign('nId',$nId);
				$this->display('pm+show');
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

}
