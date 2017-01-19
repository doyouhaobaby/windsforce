<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点统计显示($$)*/

!defined('Q_PATH') && exit;

class OnlineController extends InitController{

	public function index(){
		if($GLOBALS['_option_']['online_on']==0){
			$this->E(Q::L('用户在线功能没有开启','Controller'));
		}

		if($GLOBALS['_option_']['online_detailon']==0){
			$this->E(Q::L('系统关闭了用户在线详细页面','Controller'));
		}
		
		// 读取在线数据
		$arrOnlinedata=Home_Extend::getOnlinedata();
		$this->assign('arrOnlinedata',$arrOnlinedata);

		// 用户列表数据
		$nTotalRecord=Model::F_('online')->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['online_list_num']);
		$arrOnlineLists=Model::F_('online')->order('`create_dateline` DESC')->limit($oPage->S(),$oPage->N())->getAll();

		$this->assign('nTotalOnline',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrOnlineLists',$arrOnlineLists);
		$this->display('online+index');
	}

	public function index_title_(){
		return Q::L('用户在线','Controller');
	}

	public function index_keywords_(){
		return $this->index_title_();
	}

	public function index_description_(){
		return $this->index_title_();
	}
	
}
