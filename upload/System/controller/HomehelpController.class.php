<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台帮助信息($$)*/

!defined('Q_PATH') && exit;

class HomehelpController extends InitController{

	public function index(){
		// 分类列表
		$this->homehelpcategory_();

		if(empty($GLOBALS['_cache_']['home_option'])){
			Core_Extend::loadCache('home_option');
		}
		
		$arrWhere=array();
		$arrWhere['A.homehelp_status']=1;

		$nId=intval(Q::G('cid','G'));
		if(empty($nId)){
			$nId=0;
		}else{
			$arrWhere['A.homehelpcategory_id']=$nId;
			$arrHomehelpcategory=Model::F_('homehelpcategory','homehelpcategory_id=?',$nId)->setColumns('homehelpcategory_name')->getOne();
		}
		
		$nTotalRecord=Model::F_('homehelp','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homehelp_list_num']);
		$arrHomehelps=Model::F_('homehelp','@A')->where($arrWhere)
			->setColumns('A.homehelp_id,A.homehelp_title,A.create_dateline,A.homehelpcategory_id')
			->joinLeft(Q::C('DB_PREFIX').'homehelpcategory AS B','B.homehelpcategory_name','A.homehelpcategory_id=B.homehelpcategory_id')
			->order('A.homehelp_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('帮助中心','__COMMON_LANG__@Common').(isset($arrHomehelpcategory)?' - '.$arrHomehelpcategory['homehelpcategory_name']:'')));
		
		$this->assign('arrHomehelps',$arrHomehelps);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nCid',$nId);
		$this->display('homehelp+list');
	}

	public function show(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定帮助ID','__COMMON_LANG__@Common'));
		}

		$oHomehelp=Model::F_('homehelp','@A')->where('A.homehelp_status=1 AND A.homehelp_id=?',$nId)
			->setColumns('A.homehelp_id,A.homehelp_title,A.create_dateline,A.homehelpcategory_id,A.homehelp_content,A.update_dateline,A.homehelp_viewnum,A.user_id,A.homehelp_username,A.homehelp_updateuserid,A.homehelp_updateusername')
			->joinLeft(Q::C('DB_PREFIX').'homehelpcategory AS B','B.homehelpcategory_name','A.homehelpcategory_id=B.homehelpcategory_id')
			->getOne();
		if(!empty($oHomehelp['homehelp_id'])){
			$oHomehelp['homehelp_content']=Core_Extend::replaceSiteVar($oHomehelp['homehelp_content']);
			$oHomehelp['homehelp_viewnum']++;
			
			$this->homehelpcategory_();

			// 更新点击量
			Model::M_('homehelp')->updateWhere(array('homehelp_viewnum'=>$oHomehelp['homehelp_viewnum']),'homehelp_id=?',$nId);

			Core_Extend::getSeo($this,array(
				'title'=>$oHomehelp['homehelp_title'].($oHomehelp['homehelpcategory_id']>0?' - '.$oHomehelp['homehelpcategory_name']:''),
				'keywords'=>$oHomehelp['homehelp_title'].($oHomehelp['homehelpcategory_id']>0?','.$oHomehelp['homehelpcategory_name']:''),
				'description'=>$oHomehelp['homehelp_title'].($oHomehelp['homehelpcategory_id']>0?' - '.$oHomehelp['homehelpcategory_name']:'').' - '.Q::L('多看看帮助可以让你更好地使用本站提供的服务!','__COMMON_LANG__@Common')));
			
			$this->assign('oHomehelp',$oHomehelp);
			$this->display('homehelp+show');
		}else{
			$this->E(Q::L('你指定的帮助不存在','__COMMON_LANG__@Common'));
		}
	}

	public function homehelpcategory_(){
		Core_Extend::loadCache('helpcategory');
		$this->assign('arrHomehelpcategorys',$GLOBALS['_cache_']['helpcategory']);
	}

}
