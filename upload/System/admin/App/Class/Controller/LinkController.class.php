<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   友情链接控制器($$)*/

!defined('Q_PATH') && exit;

class LinkController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.link_name']=array('like',"%".Q::G('link_name')."%");
		$arrMap['A.link_description']=array('like',"%".Q::G('link_description')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_link($nId)){
				$this->E(Q::L('系统链接无法删除','Controller'));
			}
		}
	}

	public function bEdit_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_link($nId)){
			$this->E(Q::L('系统链接无法编辑','Controller'));
		}
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('link');
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}
	
	public function aForeverdelete_deep($sId){
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

	public function is_system_link($nId){
		$nId=intval($nId);

		$oLink=LinkModel::F('link_id=?',$nId)->setColumns('link_id,link_issystem')->getOne();
		if(empty($oLink['link_id'])){
			return false;
		}

		if($oLink['link_issystem']==1){
			return true;
		}

		return false;
	}

}
