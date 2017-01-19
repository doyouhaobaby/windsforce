<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点帮助控制器($$)*/

!defined('Q_PATH') && exit;

class HomehelpController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homehelp_title']=array('like','%'.Q::G('homehelp_title').'%');
		$arrMap['B.homehelpcategory_name']=array('like','%'.Q::G('homehelpcategory_name').'%');

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bIndex_(){
		$this->bAdd_();
	}
	
	public function index($sModel=null,$bDisplay=true){
		$this->bIndex_();
		parent::index('homehelp',false);
		$this->display(Admin_Extend::template('home','homehelp/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."homehelpcategory AS B','B.homehelpcategory_name','A.homehelpcategory_id=B.homehelpcategory_id')";
	}

	public function bEdit_(){
		$this->bAdd_();
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		$this->bEdit_();
		parent::edit('homehelp',$nId,false);
		$this->display(Admin_Extend::template('home','homehelp/add'));
	}
	
	public function bAdd_(){
		$oHomehelpcategory=new HomehelpcategoryModel();
		$arrHomehelpcategorys=$oHomehelpcategory->getHomehelpcategory();
		$this->assign('arrHomehelpcategorys',$arrHomehelpcategorys);
	}
	
	public function add(){
		$this->bAdd_();
		$this->display(Admin_Extend::template('home','homehelp/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homehelp',$nId);
	}

	protected function aUpdate($nId=null){
		$this->aForbid();
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::insert('homehelp',$nId);
	}

	protected function aInsert($nId=null){
		$this->aForbid();
	}
	
	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete('homehelp',$sId,true);
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::forbid('homehelp',$nId,true);
	}

	protected function aForbid(){
		$nId=intval(Q::G('value','G'));
		$oHomehelp=HomehelpModel::F('homehelp_id=?',$nId)->getOne();
		if(!empty($oHomehelp['homehelpcategory_id'])){
			$this->homehelpcategory_count($oHomehelp['homehelpcategory_id']);
		}
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::resume('homehelp',$nId,true);
	}

	protected function aResume(){
		$this->aForbid();
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_homehelp($nId)){
				$this->E(Q::L('系统站点帮助无法删除','__APPHOME_COMMON_LANG__@Controller'));
			}
		}
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('homehelp',$sId);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('homehelp',$sField);
	}

	public function change_homehelpcategory(){
		$sId=trim(Q::G('value','G'));
		$nHomehelpcategoryId=intval(Q::G('homehelpcategory_id','G'));
	
		if(!empty($sId)){
			if($nHomehelpcategoryId){
				// 判断帮组分类是否存在
				$oHomehelpcategory=HomehelpcategoryModel::F('homehelpcategory_id=?',$nHomehelpcategoryId)->getOne();
				if(empty($oHomehelpcategory['homehelpcategory_id'])){
					$this->E(Q::L('你要移动的站点帮助分类不存在','__APPHOME_COMMON_LANG__@Controller'));
				}
			}
			
			$arrIds=explode(',', $sId);
			foreach($arrIds as $nId){
				$oHomehelp=HomehelpModel::F('homehelp_id=?',$nId)->getOne();
				$oHomehelp->homehelpcategory_id=$nHomehelpcategoryId;
				$oHomehelp->save('update');
				if($oHomehelp->isError()){
					$this->E($oHomehelp->getErrorMessage());
				}

				$this->homehelpcategory_count($nId,$nHomehelpcategoryId);
			}

			$this->S(Q::L('移动站点帮助分类成功','__APPHOME_COMMON_LANG__@Controller'));
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function homehelpcategory_count($nOld,$nNow=null){
		$nOld=intval($nOld);
		$nNow=intval($nNow);
		
		$oHomehelpcategory=new HomehelpcategoryModel();
		$oHomehelpcategory->homehelpcategoryCount($nOld);
		if(!empty($nNow)){
			$oHomehelpcategory->homehelpcategoryCount($nNow);
		}
	}

	public function is_system_homehelp($nId){
		$nId=intval($nId);
		$oHomehelp=HomehelpModel::F('homehelp_id=?',$nId)->setColumns('homehelp_id,homehelp_issystem')->getOne();
		if(empty($oHomehelp['homehelp_id'])){
			return false;
		}

		if($oHomehelp['homehelp_issystem']==1){
			return true;
		}

		return false;
	}
	
}
