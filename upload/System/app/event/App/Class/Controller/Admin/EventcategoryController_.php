<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动类型控制器($$)*/

!defined('Q_PATH') && exit;

class EventcategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.eventcategory_name']=array('like','%'.Q::G('eventcategory_name').'%');
		$arrMap['A.eventcategory_count']=array('egt',intval(Q::G('eventcategory_count')));

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('eventcategory',false);
		$this->display(Admin_Extend::template('event','eventcategory/index'));
	}
	
	public function add(){
		$this->bAdd_();
		$this->display(Admin_Extend::template('event','eventcategory/add'));
	}
	
	public function bAdd_(){
		$oEventcategoryTree=Q::instance('EventcategoryModel')->getEventcategoryTree();
		$this->assign('oEventcategoryTree',$oEventcategoryTree);
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		$this->bEdit_();
		parent::edit('eventcategory',$nId,false);
		$this->display(Admin_Extend::template('event','eventcategory/add'));
	}

	public function bEdit_(){
		$this->bAdd_();
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('eventcategory',$nId);
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('eventcategory',$nId);
	}

	public function foreverdelete_deep($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('eventcategory',$sId);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				$nEventcategorys=EventcategoryModel::F('eventcategory_parentid=?',$nId)->all()->getCounts();
				$oEventcategory=EventcategoryModel::F('eventcategory_id=?',$nId)->query();
				if($nEventcategorys>0){
					$this->E(Q::L('活动类型%s存在子类型，你无法删除','__APPEVENT_COMMON_LANG__@Controller',null,$oEventcategory->eventcategory_name));
				}
			}
		}
	}

	public function get_parent_eventcategory($nParentEventcategoryId){
		$oEventcategory=Q::instance('EventcategoryModel');
		return $oEventcategory->getParentEventcategory($nParentEventcategoryId);
	}

	public function input_change_ajax($sName=null){
		parent::input_change_ajax('eventcategory');
	}

}
