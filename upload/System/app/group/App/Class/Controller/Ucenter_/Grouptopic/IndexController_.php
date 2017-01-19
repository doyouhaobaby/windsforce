<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   话题列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 类型
		$sType=trim(Q::G('type','G'));
		if(empty($sType)){
			$sType='';
		}
		$this->assign('sType',$sType);

		// 帖子列表
		$arrWhere=array();

		switch($sType){
			case 'myself':
				$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];
				break;
			case 'friend':
				// 仅好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				if(!empty($arrUserIds)){
					$arrWhere['A.user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['A.user_id']='';
				}
				break;
			case 'all':
				// 这里可以设置用户隐私，比如用户不愿意将动态放出
				break;
			default:
				// 我和好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				$arrUserIds[]=$GLOBALS['___login___']['user_id'];
				if(!empty($arrUserIds)){
					$arrWhere['A.user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['A.user_id']='';
				}
				break;
		}

		$arrWhere['A.grouptopic_status']=1;

		$nTotalGrouptopicnum=Model::F_('grouptopic','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopicnum,$GLOBALS['_cache_']['group_option']['group_ucenter_listtopicnum']);
		$arrGrouptopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order("A.grouptopic_sticktopic DESC,A.grouptopic_update DESC,A.create_dateline DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('小组用户中心','Controller')));
		
		$this->assign('arrGrouptopics',$arrGrouptopics);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('ucentergrouptopic+index');
	}

}
