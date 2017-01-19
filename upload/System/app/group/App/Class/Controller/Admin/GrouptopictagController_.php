<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组标签控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopictagController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.grouptopictag_name']=array('like','%'.Q::G('grouptopic_name').'%');
		$arrMap['A.grouptopictag_count']=array('egt',intval(Q::G('grouptopictag_count')));

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('grouptopictag',false);
		$this->display(Admin_Extend::template('group','grouptopictag/index'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$this->E(Q::L('帖子标签不允许被编辑','__APPGROUP_COMMON_LANG__@Controller'));
	}
	
	public function add(){
		$this->E(Q::L('不允许添加帖子标签','__APPGROUP_COMMON_LANG__@Controller'));
	}

	public function aForeverdelete_deep($sId){
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			// 清理标签索引
			$oGrouptopictagindexMeta=GrouptopictagindexModel::M();
			$oGrouptopictagindexMeta->deleteWhere(array('grouptopictag_id'=>$nId));
			if($oGrouptopictagindexMeta->isError()){
				$this->E($oGrouptopictagindexMeta->getErrorMessage());
			}
		}
	}
	
	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('grouptopictag',$sId);
	}

}
