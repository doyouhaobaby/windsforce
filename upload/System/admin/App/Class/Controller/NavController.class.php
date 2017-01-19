<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   菜单控制器($$)*/

!defined('Q_PATH') && exit;

class NavController extends AController{
	public function filter_(&$arrMap){
		$arrMap['A.nav_name']=array('like',"%".Q::G('nav_name')."%");
		$arrMap['A.nav_identifier']=array('like',"%".Q::G('nav_identifier')."%");
		$arrMap['A.nav_url']=array('like',"%".Q::G('nav_url')."%");

		$nLocation=intval(Q::G('location','G'));
		if(!in_array($nLocation,array(0,1,2))){
			$nLocation=0;
		}
		$arrMap['A.nav_location']=$nLocation;

		$this->assign('nLocation',$nLocation);
	}

	public function AEditObject_($oModel){
		$arrStyle=array();

		if(!empty($oModel->nav_style)){
			$arrStyle=unserialize($oModel->nav_style);
		}else{
			$arrStyle=array(0=>0,1=>0,2=>0);
		}

		$this->assign('arrStyle',$arrStyle);
	}

	public function AInsertObject_($oModel){
		$oModel->nav_type=1;
		$oModel->customIdentifier();
	}

	protected function aInsert($nId=null){
		// 需要删除导航缓存
		$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND'];
		$bAllowMem=Core_Extend::memory('check');

		$bAllowMem && self::memory('delete','nav');

		$sCachefile=WINDSFORCE_PATH.'/~@~/data/~@nav.php';
		$bIsFilecache && (is_file($sCachefile) && @unlink($sCachefile));
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function aForeverdelete_deep($sId){
		$this->aInsert();
	}

	protected function aForeverdelete($sId){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}

	protected function aResume(){
		$this->aInsert();
	}

	public function afterInputChangeAjax($sName=null){
		$this->aInsert();
	}

	public function AUpdateObject_($oModel){
		$arrStyle=Q::G('style');

		if(!isset($arrStyle[0])){
			$arrStyle[0]=0;
		}

		if(!isset($arrStyle[1])){
			$arrStyle[1]=0;
		}

		if(!isset($arrStyle[2])){
			$arrStyle[2]=0;
		}

		$oModel->nav_style=serialize($arrStyle);
	}

	public function bAdd_(){
		$this->bEdit_();
	}

	public function bEdit_(){
		$arrNavs=$this->nar_parent();
		$this->assign('arrNavs',$arrNavs);

		$nLocation=intval(Q::G('location','G'));
		if(!in_array($nLocation,array(0,1,2))){
			$nLocation=0;
		}
		$this->assign('nLocation',$nLocation);
	}
	
	public function bForeverdelete_(){
		$this->bForeverdelete_deep_();
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('id','G');

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			// 系统链接不能够被删除
			$oNavModel=NavModel::F('nav_id=?',$nId)->getOne();
			if(!$oNavModel['nav_type']){
				$this->E(Q::L('内置的菜单不能够被删除','Controller'));
			}
		}
	}

	public function nar_parent(){
		$arrNavs=NavModel::F()->where(array('nav_parentid'=>0,'nav_location'=>0))->order('nav_sort ASC')->getAll();
		return $arrNavs;
	}
	
}
