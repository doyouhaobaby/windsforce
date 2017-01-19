<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点信息控制器($$)*/

!defined('Q_PATH') && exit;

class HomesiteController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homesite_name']=array('like','%'.Q::G('homesite_name').'%');
		$arrMap['A.homesite_nikename']=array('like','%'.Q::G('homesite_nikename').'%');
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homesite',false);
		$this->display(Admin_Extend::template('home','homesite/index'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('homesite',$nId,false);
		$this->display(Admin_Extend::template('home','homesite/add'));
	}
	
	public function add(){
		$this->display(Admin_Extend::template('home','homesite/add'));
	}
	
	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homesite',$nId);
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('homesite',$nId);
	}

	public function check_name(){
		$sHomesiteName=trim(Q::G('homesite_name'));
		$nId=intval(Q::G('value'));

		if(!$sHomesiteName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['homesite_name']=$sHomesiteName;
		if($nId){
			$arrWhere['homesite_id']=array('neq',$nId);
		}

		$oHomesite=HomesiteModel::F()->where($arrWhere)->setColumns('homesite_id')->getOne();
		if(empty($oHomesite['homesite_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_homesite($nId)){
				$this->E(Q::L('系统站点信息无法删除','__APPHOME_COMMON_LANG__@Controller'));
			}
		}
	}
	
	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('homesite',$sId);
	}
	
	public function is_system_homesite($nId){
		$nId=intval($nId);
	
		$oHomesite=HomesiteModel::F('homesite_id=?',$nId)->setColumns('homesite_id,homesite_issystem')->getOne();
		if(empty($oHomesite['homesite_id'])){
			return false;
		}

		if($oHomesite['homesite_issystem']==1){
			return true;
		}

		return false;
	}

}
