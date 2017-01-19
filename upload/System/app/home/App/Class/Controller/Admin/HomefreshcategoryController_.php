<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事类型控制器($$)*/

!defined('Q_PATH') && exit;

class HomefreshcategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homefreshcategory_name']=array('like','%'.Q::G('homefreshcategory_name').'%');
		$arrMap['A.homefreshcategory_count']=array('egt',intval(Q::G('homefreshcategory_count')));

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homefreshcategory',false);
		$this->display(Admin_Extend::template('home','homefreshcategory/index'));
	}
	
	public function add(){
		$this->display(Admin_Extend::template('home','homefreshcategory/add'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('homefreshcategory',$nId,false);
		$this->display(Admin_Extend::template('home','homefreshcategory/add'));
	}

	public function bEdit_(){
		$this->bAdd_();
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('homefreshcategory',$nId);
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homefreshcategory',$nId);
	}

	public function foreverdelete_deep($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete_deep('homefreshcategory',$sId);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 将新鲜事的分类设置为0
		foreach($arrIds as $nId){
			Model::M_('homefresh')->updateWhere(array('homefreshcategory_id'=>0),'homefreshcategory_id=?',$nId);
		}

		$this->aInsert();
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('category');
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function input_change_ajax($sName=null){
		parent::input_change_ajax('homefreshcategory');
	}

}
