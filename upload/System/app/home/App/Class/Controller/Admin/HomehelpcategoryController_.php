<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点帮组分类控制器($$)*/

!defined('Q_PATH') && exit;

class HomehelpcategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homehelpcategory_name']=array('like','%'.Q::G('homehelpcategory_name').'%');
		$arrMap['A.homehelpcategory_count']=array('egt',intval(Q::G('homehelpcategory_count')));
		
		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homehelpcategory',false);
		$this->display(Admin_Extend::template('home','homehelpcategory/index'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('homehelpcategory',$nId,false);
		$this->display(Admin_Extend::template('home','homehelpcategory/add'));
	}
	
	public function add(){
		$this->display(Admin_Extend::template('home','homehelpcategory/add'));
	}
	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homehelpcategory',$nId);
	}
	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('homehelpcategory',$nId);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_homehelpcategory($nId)){
				$this->E(Q::L('系统站点帮助分类无法删除','__APPHOME_COMMON_LANG__@Controller'));
			}

			$nHomehelps=HomehelpModel::F('homehelpcategory_id=?',$nId)->all()->getCounts();
			$oHomehelpcategory=HomehelpcategoryModel::F('homehelpcategory_id=?',$nId)->query();
			if($nHomehelps>0){
				$this->E(Q::L('站点帮助分类%s存在帮助内容，你无法删除','__APPHOME_COMMON_LANG__@Controller',null,$oHomehelpcategory->homehelpcategory_name));
			}
		}
	}
	
	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete('homehelpcategory',$sId,true);
	}
		
	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('homehelpcategory',$sId);
	}	

	public function input_change_ajax($sName=null){
		parent::input_change_ajax('homehelpcategory');
	}

	public function is_system_homehelpcategory($nId){
		$nId=intval($nId);
	
		$oHomehelpcategory=HomehelpcategoryModel::F('homehelpcategory_id=?',$nId)->setColumns('homehelpcategory_id,homehelpcategory_issystem')->getOne();
		if(empty($oHomehelpcategory['homehelpcategory_id'])){
			return false;
		}

		if($oHomehelpcategory['homehelpcategory_issystem']==1){
			return true;
		}

		return false;
	}
	
}
