<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组分类控制器($$)*/

!defined('Q_PATH') && exit;

class GroupcategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.groupcategory_name']=array('like','%'.Q::G('groupcategory_name').'%');
		$arrMap['A.groupcategory_count']=array('egt',intval(Q::G('groupcategory_count')));

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('groupcategory',false);
		$this->display(Admin_Extend::template('group','groupcategory/index'));
	}
	
	public function add(){
		$this->bAdd_();
		$this->display(Admin_Extend::template('group','groupcategory/add'));
	}
	
	public function bAdd_(){
		$oGroupcategoryTree=Q::instance('GroupcategoryModel')->getGroupcategoryTree();
		$this->assign('oGroupcategoryTree',$oGroupcategoryTree);
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		$this->bEdit_();
		parent::edit('groupcategory',$nId,false);
		$this->display(Admin_Extend::template('group','groupcategory/add'));
	}

	public function bEdit_(){
		$this->bAdd_();
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('groupcategory',$nId);
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('groupcategory',$nId);
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('groupcategory',$sId);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				$nGroupcategorys=GroupcategoryModel::F('groupcategory_parentid=?',$nId)->all()->getCounts();
				$oGroupcategory=GroupcategoryModel::F('groupcategory_id=?',$nId)->query();
				if($nGroupcategorys>0){
					$this->E(Q::L('群组分类%s存在子分类，你无法删除','__APPGROUP_COMMON_LANG__@Controller',null,$oGroupcategory->groupcategory_name));
				}
			}
		}
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 将帖子的分类设置为0
		foreach($arrIds as $nId){
			Model::M_('group')->updateWhere(array('groupcategory_id'=>0),'groupcategory_id=?',$nId);
		}
		
		$this->aInsert();
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('group_category');
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function get_parent_groupcategory($nParentGroupcategoryId){
		$oGroupcategory=Q::instance('GroupcategoryModel');
		return $oGroupcategory->getParentGroupcategory($nParentGroupcategoryId);
	}

	public function input_change_ajax($sName=null){
		parent::input_change_ajax('groupcategory');
	}

}
