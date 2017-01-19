<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点新鲜事控制器($$)*/

!defined('Q_PATH') && exit;

class HomefreshController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homefresh_title']=array('like','%'.Q::G('homefresh_title').'%');
		$arrMap['A.homefresh_commentnum']=array('egt',intval(Q::G('homefresh_commentnum')));
		$arrMap['A.homefresh_viewnum']=array('egt',intval(Q::G('homefresh_viewnum')));
		$arrMap['A.homefresh_username']=array('like','%'.Q::G('homefresh_username').'%');

		// 作者
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homefresh',false);

		// 文章分类
		Core_Extend::loadCache('category');
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);

		$this->display(Admin_Extend::template('home','homefresh/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."homefreshcategory AS D','D.homefreshcategory_name','A.homefreshcategory_id=D.homefreshcategory_id')";
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::forbid('homefresh',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::resume('homefresh',$nId,true);
	}
	
	public function add(){
		$this->E(Q::L('后台无法添加新鲜事','__APPHOME_COMMON_LANG__@Controller'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('homefresh',$nId,false);

		// 文章分类
		Core_Extend::loadCache('category');
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);

		$this->display(Admin_Extend::template('home','homefresh/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homefresh',$nId);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('homefresh',$sField);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('homefresh',$sId,true);
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('homefresh',$sId);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		$oHomefreshcommentMeta=HomefreshcommentModel::M();
		foreach($arrIds as $nId){
			$oHomefreshcommentMeta->deleteWhere(array('homefresh_id'=>$nId));
		}
		
		$this->aInsert();
	}

	protected function aInsert($nId=null){
		Q::instance('HomefreshcategoryModel')->rebuildHomefreshnum();
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function aForeverdelete($sId){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}
	
	protected function aResume(){
		$this->aInsert();
	}
	
}
