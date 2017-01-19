<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组个人空间基本资料($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息处理函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Base_C_Controller extends InitController{
	
	public function index(){
		$nId=intval(Q::G('id','G'));
		$nDid=intval(Q::G('did','G')); // 是否为精华
		$nRecommend=intval(Q::G('recommend','G')); // 是否推荐
		$sType=trim(Q::G('t','G')); // 排序类型
		
		$arrUserInfo=Model::F_('user','user_status=1 AND user_id=?',$nId)->setColumns('user_id,user_name')->getOne();
		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->assign('nId',$nId);

		if($sType=="view"){
			$sOrderType='grouptopic_views';
		}elseif($sType=="com"){
			$sOrderType='grouptopic_comments';
		}elseif($sType=="lastreply"){
			$sOrderType='grouptopic_update';
		}else{
			$sOrderType='create_dateline';
		}

		$this->assign('sT',$sType);

		// 读取帖子列表
		$arrWhere=array();
		$arrWhere['A.user_id']=$nId;
		if($nDid==1){
			$arrWhere['A.grouptopic_addtodigest']=array('gt',0);
		}
		if($nRecommend==1){
			$arrWhere['A.grouptopic_isrecommend']=array('gt',0);
		}

		if($GLOBALS['___login___']!==false && $GLOBALS['___login___']['user_id']==$nId){
			$sOrderextends='A.grouptopic_status ASC,A.grouptopic_isanonymous ASC,';
			$nYouself=1;
			$arrWhere['A.grouptopic_status']=array('neq',CommonModel::STATUS_RECYLE);
		}else{
			$sOrderextends='';
			$nYouself=0;
			$arrWhere['A.grouptopic_status']=1;
			$arrWhere['A.grouptopic_isanonymous']='0';
		}

		// 分页URL
		$sPageurl='';
		if($sType){
			$sPageurl[]='t='.$sType;
		}
		if($nDid==1){
			$sPageurl[]='did=1';
		}
		if($nRecommend==1){
			$sPageurl[]='recommend=1';
		}
		if($sPageurl){
			$sPageurl='&'.implode('&',$sPageurl);
		}
		$sPageurl='@group://space@?id='.$nId.'&page={page}'.$sPageurl;

		$nTotalGrouptopicnum=Model::F_('grouptopic','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopicnum,$GLOBALS['_cache_']['group_option']['group_space_listtopicnum'],$sPageurl);
		$arrGrouptopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order($sOrderextends."A.grouptopic_sticktopic DESC,A.grouptopic_update DESC,A.{$sOrderType} DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('小组个人空间','Controller')));
		
		$this->assign('arrGrouptopics',$arrGrouptopics);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nYouself',$nYouself);
		$this->display('space+index');
	}

}
