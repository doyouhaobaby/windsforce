<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事查看($$)*/

!defined('Q_PATH') && exit;

/** 导入home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

class View_C_Controller extends InitController{

	public function index(){
		if(!Core_Extend::checkRbac('home@ucenter@view')){
			$this->_oParent->wap_mes(Q::L('你没有权限查看新鲜事','Controller'),'',0);
		}
		
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->_oParent->wap_mes(Q::L('你没有指定要阅读的新鲜事','Controller'),'',0);
		}

		$arrHomefresh=Model::F_('homefresh','@A','A.homefresh_id=? AND A.homefresh_status=1',$nId)
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_type,A.homefresh_viewnum')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name,B.user_sign','A.user_id=B.user_id')
			->getOne();
		if(empty($arrHomefresh['homefresh_id'])){
			$this->_oParent->wap_mes(Q::L('新鲜事不存在或者被屏蔽了','Controller'),'',0);
		}

		Core_Extend::getSeo($this,array('title'=>$arrHomefresh['homefresh_title']));

		$this->assign('arrHomefresh',$arrHomefresh);
		$this->display('homefresh+view');
	}

}
