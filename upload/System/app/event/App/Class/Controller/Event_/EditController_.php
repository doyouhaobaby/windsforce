<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑活动控制器($$)*/

!defined('Q_PATH') && exit;

class Edit_C_Controller extends InitController{

	public function index(){
		$nEventid=intval(Q::G('id','G'));
		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nEventid)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要编辑的活动不存在','Controller'));
		}

		// 判断权限
		if(!Eventadmin_Extend::checkEvent($oEvent)){
			$this->E(Q::L('你没有权限编辑活动','Controller'));
		}

		$this->assign('oEvent',$oEvent);
		
		// 活动类型
		Core_Extend::loadCache('event_category');
		$this->assign('arrEventcategorys',$GLOBALS['_cache_']['event_category']);

		Core_Extend::getSeo($this,array('title'=>$oEvent['event_title'].' - '.Q::L('编辑活动','Controller')));

		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('event+add');
	}

}
