<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   网站公告控制器($$)*/

!defined('Q_PATH') && exit;

class AnnouncementController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.announcement_title']=array('like',"%".Q::G('announcement_title')."%");
		$arrMap['A.announcement_message']=array('like',"%".Q::G('announcement_message')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	protected function AInsertObject_($oModel){
		$oModel->timeFormat();
	}
	
	protected function AUpdateObject_($oModel){
		$this->AInsertObject_($oModel);
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('announcement');
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}
	
	protected function aResume(){
		$this->aInsert();
	}

	public function aForeverdelete_deep($sId){
		$this->aInsert();
	}

	public function aForeverdelete($sId){
		$this->aInsert();
	}
	
	public function afterInputChangeAjax($sName=null){
		$this->aInsert();
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if(!$this->check_admin($nId)){
				$this->E(Q::L('你无法删除别人的公告','Controller'));
			}
		}
	}

	public function check_admin($nId){
		if($GLOBALS['___login___']['user_id']==1){
			return true;
		}

		$nId=intval($nId);
		$oAnnouncement=AnnouncementModel::F('announcement_id=?',$nId)->getOne();
		if(empty($oAnnouncement['announcement_id'])){
			return false;
		}

		if($GLOBALS['___login___']['user_name']!=$oAnnouncement['announcement_username']){
			return false;
		}

		return true;
	}

}
