<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动成员审核($$)*/

!defined('Q_PATH') && exit;

class Audit_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		$nUserid=intval(Q::G('uid','G'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nId)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要浏览的活动不存在','Controller'));
		}

		// 处理审核数据
		$oEventuser=EventuserModel::F('event_id=? AND user_id=? AND eventuser_status=0',$nId,$nUserid)->getOne();
		if(empty($oEventuser['event_id'])){
			$this->E(Q::L('没有带审核的用户或者你已经通过了审核','Controller'));
		}
		$oEventuser->eventuser_status='1';
		$oEventuser->save('update');
		if($oEventuser->isError()){
			$this->E($oEventuser->getErrorMessage());
		}

		$this->S(Q::L('活动成员审核成功','Controller'));
	}

}
