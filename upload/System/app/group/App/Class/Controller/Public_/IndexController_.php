<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组首页控制器($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$nCid=intval(Q::G('cid','G'));
		$nType=intval(Q::G('model','G'));

		// 站点统计数据 && 其他缓存
		Core_Extend::loadCache('group_newgroup');
		Core_Extend::loadCache('group_category');
		Core_Extend::loadCache('group_site');
		if(ACTION_NAME==='index'){
			Core_Extend::loadCache('group_topic');
			$this->assign('arrGrouptopicCaches',$GLOBALS['_cache_']['group_topic']);
		}
		
		// 查询小组
		$arrWhere=array();
		$arrWhere['A.group_status']=1;

		if($nType==2){
			$sOrder='A.group_usernum DESC';
		}elseif($nType==1){
			$sOrder='A.group_totaltodaynum DESC';
		}else{
			$sOrder='A.group_isrecommend DESC,A.create_dateline DESC';
		}

		// 取得小组分类
		if($nCid){
			if(array_key_exists($nCid,$GLOBALS['_cache_']['group_category'])){
				$arrGroupcategory=$GLOBALS['_cache_']['group_category'][$nCid];
				$arrGroupcategorys=$GLOBALS['_cache_']['group_category'][$nCid]['child'];
				$arrWhere['A.groupcategory_id']=$nCid;
				$this->assign('arrParentGroupcategory',$arrGroupcategory);
			}else{
				$arrGroupcategorys=$arrGroupcategory=array();
				foreach($GLOBALS['_cache_']['group_category'] as $arrTemp){
					foreach($arrTemp['child'] as $arrTempTwo){
						if($arrTempTwo['groupcategory_id']==$nCid){
							$arrWhere['A.groupcategory_id']=$nCid;
							$this->assign('arrParentGroupcategory',$arrTempTwo);
							break;
						}
					}
				}
			}
		}else{
			$arrGroupcategorys=$GLOBALS['_cache_']['group_category'];
		}

		// 分页URL
		if(ACTION_NAME==='index'){
			$sPageurl='';
			if(Q::G('cid')){
				$sPageurl[]='cid='.intval(Q::G('cid'));
			}
			if(Q::G('model')){
				$sPageurl[]='model='.intval(Q::G('model'));
			}
			if($sPageurl){
				$sPageurl='&'.implode('&',$sPageurl);
			}

			$sPageurl='@group://list@?page={page}'.$sPageurl;
		}else{
			$sPageurl='';
		}

		$nTotalRecord=Model::F_('group','@A')->where($arrWhere)->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['group_option']['group_grouplistnum'],$sPageurl);
		$arrGroups=Model::F_('group','@A')->where($arrWhere)
			->setColumns('A.group_id,A.group_icon,A.group_totaltodaynum,A.group_isopen,A.group_topicnum,A.group_topiccomment,A.group_usernum,A.group_listdescription,A.group_nikename,A.group_name,A.group_color,A.group_isrecommend,A.groupcategory_id')
			->order($sOrder)
			->limit($oPage->S(),$oPage->N())
			->getAll();

		// 处理小组列表
		$arrGroupLists=array();
		if(isset($arrGroupcategory)){
			$arrGroupLists[$arrGroupcategory['groupcategory_id']]=array('title'=>$arrGroupcategory['groupcategory_name'],'data'=>array());
		}
		foreach($arrGroupcategorys as $arrCategory){
			$arrGroupLists[$arrCategory['groupcategory_id']]=array('title'=>$arrCategory['groupcategory_name'],'data'=>array());
		}
		$arrGroupLists[999999]=array('title'=>'其他','data'=>array());

		foreach($arrGroups as $arrGroup){
			if(array_key_exists($arrGroup['groupcategory_id'],$arrGroupLists)){
				$arrGroupLists[$arrGroup['groupcategory_id']]['data'][]=$arrGroup;
			}else{
				$arrGroupLists[999999]['data'][]=$arrGroup;
			}
		}

		// 读取我加入的小组
		if($GLOBALS['___login___']!==FALSE){
			$arrGroupuser=$arrGroupuserId=array();
			foreach($arrGroups as $arrVal){
				$arrGroupuserId[]=$arrVal['group_id'];
			}
			$arrGroupuser=Model::F_('groupuser')
				->where(array('group_id'=>$arrGroupuserId?array('in',$arrGroupuserId):0,'user_id'=>$GLOBALS['___login___']['user_id']))
				->getColumn('group_id',true);
			if(empty($arrGroupuser)){
				$arrGroupuser=array();
			}
		}else{
			$arrGroupuser=array();
		}

		// 读取今日发帖
		$this->_oParent->getOption_();

		// 初始化SEO
		$this->_oParent->getSeo($this);

		$this->assign('arrGroupLists',$arrGroupLists);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nType',$nType);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('arrNewgroups',$GLOBALS['_cache_']['group_newgroup']);
		$this->assign('arrGroupcategorys',$arrGroupcategorys);
		$this->display('public+index');
	}

}
