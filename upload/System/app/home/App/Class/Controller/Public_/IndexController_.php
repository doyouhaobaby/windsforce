<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台首页显示($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 站点常用统计数据
		Core_Extend::loadCache('site');
		Core_Extend::loadCache('slide');
		Core_Extend::loadCache('sociatype');
		Core_Extend::loadCache('announcement');
		Core_Extend::loadCache('activeuser');
		Core_Extend::loadCache('newuser');
		Core_Extend::loadCache('category');

		$this->assign('arrSite',$GLOBALS['_cache_']['site']);
		$this->assign('arrSlides',$GLOBALS['_cache_']['slide']);
		$this->assign('arrBindeds',$GLOBALS['_cache_']['sociatype']);
		$this->assign('sHomeDescription',Core_Extend::replaceSiteVar($GLOBALS['_option_']['home_description']));
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_login_status']);
		$this->assign('nRememberTime',$GLOBALS['_option_']['remember_time']);
		$this->assign('arrActiveusers',$GLOBALS['_cache_']['activeuser']);// 取得活跃会员
		$this->assign('arrNewusers',$GLOBALS['_cache_']['newuser']);// 取得最新用户
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);
		
		// 首页新鲜事
		$this->get_homefresh_();

		// 取得在线用户数据
		if($GLOBALS['_option_']['online_on']==1 && $GLOBALS['_option_']['online_indexon']==1){
			$this->get_online_();
		}

		// 读取今日发布
		$this->get_option_();

		// 初始化SEO
		Core_Extend::getSeo($this,array('title'=>'新鲜事'),true);

		$this->display('public+index');
	}

	protected function get_homefresh_(){
		$nType=intval(Q::G('model','G'));
		$nCid=intval(Q::G('cid','G'));

		$nHomenewhomefreshnum=intval($GLOBALS['_option_']['home_newhomefresh_num']);
		if($nHomenewhomefreshnum<2){
			$nHomenewhomefreshnum=2;
		}

		$arrWhere=array();
		$arrWhere['A.homefresh_status']=1;
		if($nType && in_array($nType,array(1,2,3,4,5))){
			$arrWhere['A.homefresh_type']=$nType;
		}
		if($nCid){
			$arrWhere['A.homefreshcategory_id']=$nCid;
		}

		$nTotal=Model::F_('homefresh','@A')->where($arrWhere)->getCounts();

		// 分页显示
		if($GLOBALS['_option_']['index_display_page']==1){
			$sPageurl='';
			if(Q::G('from')){
				$sPageurl[]='from='.Q::G('from');
			}
			if(Q::G('model')==1){
				$sPageurl[]='model=1';
			}
			if($nCid){
				$sPageurl[]='cid='.$nCid;
			}
			if($sPageurl){
				$sPageurl='&'.implode('&',$sPageurl);
			}

			$oPage=Page::RUN($nTotal,$nHomenewhomefreshnum,'@home://list@?page={page}'.$sPageurl);
		}else{
			$nTotalpage=ceil($nTotal/$nHomenewhomefreshnum);
		}

		$arrHomefreshs=Model::F_('homefresh','@A')
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_viewnum,A.homefresh_type,A.homefresh_thumb,A.homefreshcategory_id')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->joinLeft(Q::C('DB_PREFIX').'homefreshcategory AS C','C.homefreshcategory_name','A.homefreshcategory_id=C.homefreshcategory_id')
			->where($arrWhere)
			->order('A.homefresh_id DESC')
			->limit($GLOBALS['_option_']['index_display_page']==1?$oPage->S():0,$GLOBALS['_option_']['index_display_page']==1?$oPage->N():$nHomenewhomefreshnum)
			->getAll();

		$sGoodCookie=Q::cookie('homefresh_goodnum');
		$arrGoodCookie=explode(',',$sGoodCookie);
		
		if($GLOBALS['_option_']['index_display_page']==1){
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		}else{
			$this->assign('nTotalpage',$nTotalpage);
		}

		$this->assign('arrGoodCookie',$arrGoodCookie);
		$this->assign('arrHomefreshs',$arrHomefreshs);
		$this->assign('nType',$nType);
		$this->assign('nCid',$nCid);
	}

	protected function get_online_(){
		// 读取在线数据
		$arrOnlinedata=Home_Extend::getOnlinedata();
		$this->assign('arrOnlinedata',$arrOnlinedata);

		// 用户在线列表
		if($GLOBALS['_option_']['online_indexmost']>0){
			if($GLOBALS['_option_']['online_indexgueston']==1){
				$arrOnlines=Model::F_('online','online_isstealth=?',0)->order('create_dateline DESC')->limit(0,$GLOBALS['_option_']['online_indexmost'])->getAll();
			}else{
				$arrOnlines=Model::F_('online','user_id>? AND online_isstealth=0',0)->order('create_dateline DESC')->limit(0,$GLOBALS['_option_']['online_indexmost'])->getAll();
			}

			$this->assign('arrOnlines',$arrOnlines);
		}
	}

	protected function get_option_(){
		$arrOption=array(
			$GLOBALS['_option_']['todaytotalnum'],
			$GLOBALS['_option_']['todayhomefreshnum'],
			$GLOBALS['_option_']['todayusernum']
		);

		$this->assign('arrHomeOption',$arrOption);
	}

}
