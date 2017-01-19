<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组浏览控制器($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('id','G')); // 小组ID
		$nCid=intval(Q::G('cid','G')); // 帖子分类ID
		$nDid=intval(Q::G('did','G')); // 是否为精华
		$nRecommend=intval(Q::G('recommend','G')); // 是否推荐
		$sType=Q::G('type','G'); // 排序类型
		$nNew=intval(Q::G('new','G')); // 是否读取新帖，针对登录用户
		$nMytopic=intval(Q::G('mytopic','G')); // 是否读取我在这个小组发布的新帖，针对登录用户

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGroup);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		if($sType=="view"){
			$sOrderType='grouptopic_views';
		}elseif($sType=="com"){
			$sOrderType='grouptopic_comments';
		}else{
			$sOrderType='create_dateline';
		}

		// 读取帖子列表
		$arrWhere=array();

		if($nCid>0){
			$arrCurrentcategory=Model::F_('grouptopiccategory','grouptopiccategory_id=?',$nCid)->setColumns('grouptopiccategory_id,grouptopiccategory_name')->getOne();
			if(!empty($arrCurrentcategory['grouptopiccategory_id'])){
				$arrWhere['A.grouptopiccategory_id']=$nCid;
				$sGroupcategory=$arrCurrentcategory['grouptopiccategory_name'];
				$this->assign('arrCurrentcategory',$arrCurrentcategory);
			}
		}

		if($nCid==-1){
			$sGroupcategory=Q::L('默认分类','Controller');
			$arrWhere['A.grouptopiccategory_id']='0';
		}

		if($nDid==1){
			$arrWhere['A.grouptopic_addtodigest']=array('gt',0);
		}

		if($nRecommend==1){
			$arrWhere['A.grouptopic_isrecommend']=array('gt',0);
		}

		$arrWhere['A.grouptopic_status']=1;
		$arrWhere['A.group_id']=$arrGroup['group_id'];
		$arrWhere['A.grouptopic_sticktopic']=array('lt',3);

		// 登录用户相关处理
		if($GLOBALS['___login___']!==false){
			if($nNew==1){
				$arrWhere['A.create_dateline']=array('gt',CURRENT_TIMESTAMP-86400);
			}
			if($nMytopic==1){
				$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];
			}
		}

		if(Groupadmin_Extend::checkTopicadminRbac($arrGroup,array('group@grouptopicadmin@hidetopic'))){
			$sOrderextends='A.grouptopic_status ASC,';
			$arrWhere['A.grouptopic_status']=array('neq',CommonModel::STATUS_RECYLE);
		}else{
			$sOrderextends='';
		}

		// 分页URL
		$sPageurl='';
		if($sType){
			$sPageurl[]='type='.$sType;
		}
		if($nCid>0 || $nCid==-1){
			$sPageurl[]='cid='.$nCid;
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
		$sPageurl='@'.'group://gid@?id='.(!empty($arrGroup['group_name'])?$arrGroup['group_name']:$arrGroup['group_id']).'&page={page}'.$sPageurl;

		$nTotalGrouptopicnum=Model::F_('grouptopic','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopicnum,$GLOBALS['_cache_']['group_option']['group_listtopicnum'],$sPageurl);
		$arrGrouptopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order($sOrderextends."A.grouptopic_sticktopic DESC,A.grouptopic_update DESC,A.{$sOrderType} DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();
		
		// 全局置顶帖子
		if(isset($arrWhere['A.grouptopic_addtodigest'])){
			unset($arrWhere['A.grouptopic_addtodigest']);
		}

		if(isset($arrWhere['A.grouptopiccategory_id'])){
			unset($arrWhere['A.grouptopiccategory_id']);
		}

		if(isset($arrWhere['A.group_id'])){
			unset($arrWhere['A.group_id']);
		}

		$arrWhere['A.grouptopic_sticktopic']='3';

		$arrGlobalSticktopics=Model::F_('grouptopic','@A')->where($arrWhere)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->order(($sType=='lastreply'?'A.update_dateline DESC,':'')."A.{$sOrderType} DESC")
			->getAll();
		if(!empty($arrGrouptopics) && !empty($arrGlobalSticktopics)){
			foreach($arrGlobalSticktopics as $oGlobalSticktopic){
				array_unshift($arrGrouptopics,$oGlobalSticktopic);
			}
		}
		
		// 小组分类
		$this->get_grouptopiccategory_($arrGroup['group_id']);

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGroup['group_id']);

		// 取得缩略图帖子
		$arrGroupthumbtopics=$this->hot_thumb_($arrGroup['group_id'],$GLOBALS['_cache_']['group_option']['onegroup_thumbtopic_num']);

		// 读取小组回收站帖子数量
		if(Core_Extend::isAdmin()){
			$nTotalRecyclebinGrouptopic=Model::F_('grouptopic')->where(array('group_id'=>$arrGroup['group_id'],'grouptopic_status'=>CommonModel::STATUS_RECYLE))
				->all()
				->getCounts();
			$this->assign('nTotalRecyclebinGrouptopic',$nTotalRecyclebinGrouptopic);
		}

		Core_Extend::getSeo($this,array(
			'title'=>(isset($sGroupcategory)?$sGroupcategory.' - ':'').$arrGroup['group_nikename'].' - '.Q::L('小组','Controller'),
			'keywords'=>$arrGroup['group_nikename'].' - '.Q::L('小组','Controller').(isset($sGroupcategory)?','.$sGroupcategory:''),
			'description'=>(isset($sGroupcategory)?$sGroupcategory.' - ':'').$arrGroup['group_nikename'].' - '.Q::L('小组','Controller').Q::L('为你搭建一个交流平台。','Controller')
		));

		$this->assign('sType',$sType);
		$this->assign('arrGroup',$arrGroup);
		$this->assign('nCid',$nCid);
		$this->assign('nDid',$nDid);
		$this->assign('nRecommend',$nRecommend);
		$this->assign('arrGrouptopics',$arrGrouptopics);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('arrGroupthumbtopics',$arrGroupthumbtopics);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('group+show');
	}

	protected function get_grouptopiccategory_($nGroupid){
		$arrCids=array();
		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$nGroupid)
			->order('grouptopiccategory_sort ASC')
			->getAll();
		if(!empty($arrGrouptopiccategorys)){
			foreach($arrGrouptopiccategorys as $arrValue){
				array_push($arrCids,$arrValue['grouptopiccategory_id']);
			}
		}

		$this->assign('arrCids',$arrCids);
		$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
	}

	protected function hot_thumb_($nGroupId,$nNum=0,$nDate=0){
		// 幻灯片帖子时间
		if($nDate==0){
			$nDate=$GLOBALS['_cache_']['group_option']['group_thumbtopic_date'];
			if($nDate<3600){
				$nDate=3600;
			}
		}

		// 首页幻灯片帖子数量
		if($nNum==0){
			$nNum=$GLOBALS['_cache_']['group_option']['group_thumbtopic_num'];
			if($nNum<2){
				$nNum=2;
			}
		}

		$arrGroupthumbtopics=Model::F_('grouptopic','@A','A.grouptopic_status=1 AND A.grouptopic_thumb!=\'\' AND A.create_dateline>? AND A.group_id=?',CURRENT_TIMESTAMP-$nDate,$nGroupId)
			->setColumns('A.grouptopic_id,A.grouptopic_title,A.user_id,A.grouptopic_username,A.grouptopic_comments,A.grouptopic_views,A.grouptopic_loves,A.grouptopic_color,A.create_dateline,A.grouptopic_thumb')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')
			->order('A.create_dateline DESC')
			->limit(0,$nNum)
			->getAll();

		return $arrGroupthumbtopics;
	}

}