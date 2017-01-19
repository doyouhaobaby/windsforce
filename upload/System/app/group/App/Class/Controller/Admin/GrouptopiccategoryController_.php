<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子分类控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopiccategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.grouptopiccategory_name']=array('like','%'.Q::G('grouptopiccategory_name').'%');
		$arrMap['A.grouptopiccategory_topicnum']=array('egt',intval(Q::G('grouptopiccategory_topicnum')));
		$arrMap['B.group_nikename']=array('like','%'.Q::G('group_nikename').'%');

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('grouptopiccategory',false);
		$this->display(Admin_Extend::template('group','grouptopiccategory/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')";
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('grouptopiccategory',$nId,false);
		$this->display(Admin_Extend::template('group','grouptopiccategory/add'));
	}

	public function get_group($nGroupId){
		return GroupModel::F('group_id=?',$nGroupId)->getOne();
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('grouptopiccategory',$nId);
	}

	public function foreverdelete_deep($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete_deep('grouptopiccategory',$sId,true);
	}
	
	public function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 将帖子的分类设置为0
		foreach($arrIds as $nId){
			Model::M_('grouptopic')->updateWhere(array('grouptopiccategory_id'=>0),'grouptopiccategory_id=?',$nId);
		}
	}

	public function input_change_ajax($sName=null){
		parent::input_change_ajax('grouptopiccategory');
	}

}
