<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动首页控制器($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$nCid=intval(Q::G('cid','G'));
		
		// 读取活动列表
		$arrWhere=array();
		$arrWhere['A.event_status']=1;

		// 活动归档
		if($nCid){
			$arrEventcategory=Model::F_('eventcategory','eventcategory_id=?',$nCid)->setColumns('eventcategory_id,eventcategory_name')->getOne();
			if(!empty($arrEventcategory['eventcategory_id'])){
				$this->assign('arrCurEventcategory',$arrEventcategory);
				$this->_arrEventcategory=$arrEventcategory;
				$arrWhere['A.eventcategory_id']=$nCid;
			}else{
				$this->U('event://public/index');
			}
		}

		$nTotalRecord=Model::F_('event','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,16,!empty($this->_arrEventcategory['eventcategory_id'])?'@event://category@?cid='.$nCid:'');
		$arrEvents=Model::F_('event','@A')->where($arrWhere)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'eventcategory AS B','B.eventcategory_name','A.eventcategory_id=B.eventcategory_id')
			->order('A.event_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();
		
		// 活动类型
		Core_Extend::loadCache('event_category');
		$this->assign('arrEventcategorys',$GLOBALS['_cache_']['event_category']);

		// 初始化SEO
		Core_Extend::getSeo($this,array(
			'title'=>(!empty($arrEventcategory['eventcategory_id'])?$arrEventcategory['eventcategory_name'].' - ':'').Q::L('活动','Controller'),
			'keywords'=>Q::L('活动','Controller').(!empty($arrEventcategory['eventcategory_id'])?','.$arrEventcategory['eventcategory_name']:''),
		),true);

		$this->assign('arrEvents',$arrEvents);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('public+index');
	}

}
