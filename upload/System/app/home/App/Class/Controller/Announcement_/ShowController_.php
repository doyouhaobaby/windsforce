<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   公告显示($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	protected $_arrAnnouncement=null;
	
	public function index(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定公告ID','Controller'));
		}

		$arrAnnouncement=Model::F_('announcement','announcement_id=?',$nId)->getOne();
		if(!empty($arrAnnouncement['announcement_id'])){
			Core_Extend::getSeo($this,array('title'=>$arrAnnouncement['announcement_title'].' - '.Q::L('公告中心','Controller'),'keywords'=>$arrAnnouncement['announcement_title'],'description'=>$arrAnnouncement['announcement_title'].' - '.Q::L('通过公告你们可以了解我们最新动向！','Controller')));
			
			$this->assign('arrAnnouncement',$arrAnnouncement);
			$this->display('announcement+show');
		}else{
			$this->E(Q::L('你指定的公告不存在','Controller'));
		}
	}

}
