<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事话题控制器($$)*/

!defined('Q_PATH') && exit;

class HomefreshtagController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homefreshtag_name']=array('like','%'.Q::G('homefreshtag_name').'%');
		$arrMap['A.homefreshtag_username']=array('like','%'.Q::G('homefreshtag_username').'%');
		$arrMap['A.homefreshtag_totalcount']=array('egt',intval(Q::G('homefreshtag_totalcount')));

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homefreshtag',false);
		$this->display(Admin_Extend::template('home','homefreshtag/index'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$this->E(Q::L('新鲜事话题不允许被编辑','__APPHOME_COMMON_LANG__@Controller'));
	}
	
	public function add(){
		$this->E(Q::L('不允许添加新鲜事话题','__APPHOME_COMMON_LANG__@Controller'));
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('homefreshtag',$sId,true);
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('homefreshtag',$sId);
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::forbid('homefreshtag',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::resume('homefreshtag',$nId,true);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('homefreshtag',$sField);
	}

}
