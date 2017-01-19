<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台首页显示($$)*/

!defined('Q_PATH') && exit;

/** 导入home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		$arrWhere['A.homefresh_status']=1;
		
		// 读取新鲜事
		$nTotalRecord=Model::F_('homefresh','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['wap_baselist_num']);
		$arrHomefreshs=Model::F_('homefresh','@A')->where($arrWhere)
			->order('A.homefresh_id DESC')
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_viewnum,A.homefresh_type')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('手机版','Controller')));

		$this->assign('nTotalRecord',$nTotalRecord);
		$this->assign('arrHomefreshs',$arrHomefreshs);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'page')));
		$this->display('public+index');
	}

}
