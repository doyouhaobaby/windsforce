<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组新帖控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入个人资料函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Newtopic_C_Controller extends InitController{

	public function index(){
		// 站点统计数据
		Core_Extend::loadCache('sociatype');
		Core_Extend::loadCache('group_hottag');
		Core_Extend::loadCache('group_site');
		if(ACTION_NAME==='index'){
			Core_Extend::loadCache('group_topic');
			$this->assign('arrGrouptopicCaches',$GLOBALS['_cache_']['group_topic']);
		}

		// 新帖列表
		$sType=Q::G('type','G'); // 排序类型
		$nDid=intval(Q::G('did','G')); // 是否为精华

		if($sType=="view"){
			$sOrderType='grouptopic_views';
		}elseif($sType=="com"){
			$sOrderType='grouptopic_comments';
		}else{
			$sOrderType='create_dateline';
		}

		// 分页URL
		if(ACTION_NAME==='index'){
			$sPageurl='';
			if(Q::G('type')){
				$sPageurl[]='type='.Q::G('type');
			}
			if(Q::G('did')==1){
				$sPageurl[]='did=1';
			}
			if($sPageurl){
				$sPageurl='&'.implode('&',$sPageurl);
			}

			$sPageurl='@group://list@?page={page}'.$sPageurl;
		}else{
			$sPageurl='';
		}

		// 读取帖子列表
		$arrWhere=array();
		$arrWhere['A.grouptopic_status']=1;
		$arrWhere['A.grouptopic_sticktopic']='0';

		if($nDid==1){
			$arrWhere['A.grouptopic_addtodigest']=array('gt',0);
		}

		$nTotalRecord=Model::F_('grouptopic','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['group_option']['group_indextopicnum'],$sPageurl);
		$arrGrouptopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order(($sType=='lastreply'?'A.grouptopic_update DESC,':'')."A.{$sOrderType} DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		// 全局置顶帖子
		if(isset($arrWhere['A.grouptopic_addtodigest'])){
			unset($arrWhere['A.grouptopic_addtodigest']);
		}
		$arrWhere['A.grouptopic_sticktopic']='3';

		$arrGlobalSticktopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order(($sType=='lastreply'?'A.grouptopic_update DESC,':'')."A.{$sOrderType} DESC")
			->getAll();
		if(!empty($arrGrouptopics) && !empty($arrGlobalSticktopics)){
			foreach($arrGlobalSticktopics as $oGlobalSticktopic){
				array_unshift($arrGrouptopics,$oGlobalSticktopic);
			}
		}

		// 读取今日发帖
		$this->_oParent->getOption_();

		// 初始化SEO
		$this->_oParent->getSeo($this);

		$this->assign('arrHottags',$GLOBALS['_cache_']['group_hottag']);
		$this->assign('arrGrouptopics',$arrGrouptopics);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('sType',$sType);
		$this->assign('nDid',$nDid);
		$this->display('public+newtopic');
	}

	public function newtopic_title_(){
		if($GLOBALS['_cache_']['group_option']['newtopic_default']==1){
			return Q::L('小组','Controller');
		}else{
			return Q::L('新帖','Controller');
		}
	}

	public function newtopic_keywords_(){
		return $this->newtopic_title_();
	}

	public function newtopic_description_(){
		return $this->newtopic_title_();
	}

	public function index_title_(){
		if($GLOBALS['_commonConfig_']['DEFAULT_APP']!='group'){
			if($GLOBALS['_cache_']['group_option']['newtopic_default']==1){
				return Q::L('新帖','Controller');
			}else{
				return Q::L('小组','Controller');
			}
		}
	}

	public function index_keywords_(){
		return $this->index_title_();
	}

	public function index_description_(){
		return $this->index_title_();
	}

}
