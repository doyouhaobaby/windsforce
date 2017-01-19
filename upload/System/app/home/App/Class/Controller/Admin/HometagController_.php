<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户标签控制器($$)*/

!defined('Q_PATH') && exit;

class HometagController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.hometag_name']=array('like','%'.Q::G('hometag_name').'%');
		$arrMap['A.hometag_count']=array('egt',intval(Q::G('hometag_count')));

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('hometag',false);
		$this->display(Admin_Extend::template('home','hometag/index'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$this->E(Q::L('用户标签不允许被编辑','__APPHOME_COMMON_LANG__@Controller'));
	}
	
	public function add(){
		$this->E(Q::L('不允许添加用户标签','__APPHOME_COMMON_LANG__@Controller'));
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('hometag',$sId);
	}

	public function aForeverdelete_deep($sId){
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			// 清理标签索引
			$oHometagindexMeta=HometagindexModel::M();
			$oHometagindexMeta->deleteWhere(array('hometag_id'=>$nId));
			if($oHometagindexMeta->isError()){
				$this->E($oHometagindexMeta->getErrorMessage());
			}
		}
	}

}
