<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组搜索($$)*/

!defined('Q_PATH') && exit;

class Group_C_Controller extends InitController{

	public function index(){
		$sKey=urldecode(trim(Q::G('key')));

		if($sKey){
			if($GLOBALS['_option_']['show_search_result_message']){
				C::urlGo(Q::U('group://search/groupresult?key='.urlencode($sKey),array(),true),1,nl2br($GLOBALS['_option_']['show_search_result_message']));
			}else{
				C::urlGo(Q::U('group://search/groupresult?key='.urlencode($sKey),array(),true));
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('小组搜索','Controller')));

		$this->assign('sKey',$sKey);
		$this->display('search+group');
	}

	public function result(){
		$sKey=urldecode(trim(Q::G('key')));
		$sKey=htmlspecialchars($sKey);
		$sKey=str_replace('%','\%',$sKey);
		$sKey=str_replace('_','\_',$sKey);

		if($sKey){
			if($GLOBALS['_option_']['search_keywords_minlength']>0 && strlen($sKey)<$GLOBALS['_option_']['search_keywords_minlength']){
				$this->E(Q::L('搜索的关键字最少为 %d 字节','Controller',null,$GLOBALS['_option_']['search_keywords_minlength']));
			}
			
			// 小组列表
			$arrWhere=array();
			$arrWhere['A.group_status']=1;
			$arrWhere['A.group_nikename']=array('like',"%{$sKey}%");

			$nTotalRecord=Model::F_('group','@A')->where($arrWhere)
				->all()
				->getCounts();
			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['search_list_num']);
			$arrGroups=Model::F_('group','@A')->where($arrWhere)
				->setColumns('A.group_id,A.group_icon,A.group_totaltodaynum,A.group_isopen,A.group_topicnum,A.group_topiccomment,A.group_usernum,A.group_listdescription,A.group_nikename,A.group_name,A.group_color,A.group_isrecommend')
				->order('A.group_id DESC')
				->limit($oPage->S(),$oPage->N())
				->getAll();

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

			Core_Extend::getSeo($this,array('title'=>$sKey.' - '.Q::L('搜索结果','Controller')));
			
			$this->assign('arrGroups',$arrGroups);
			$this->assign('nTotalGroupnum',$nTotalRecord);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('sKey',$sKey);
			$this->assign('arrGroupuser',$arrGroupuser);
			$this->display('search+groupresult');
		}else{
			$this->U('group://search/group');
		}
	}

}
