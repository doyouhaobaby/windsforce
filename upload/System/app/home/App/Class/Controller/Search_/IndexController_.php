<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   搜索首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$sKey=urldecode(trim(Q::G('key')));

		if($sKey){
			if($GLOBALS['_option_']['show_search_result_message']){
				C::urlGo(Q::U('home://search/result?key='.urlencode($sKey),array(),true),1,nl2br($GLOBALS['_option_']['show_search_result_message']));
			}else{
				C::urlGo(Q::U('home://search/result?key='.urlencode($sKey),array(),true));
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('搜索引擎','Controller')));

		$this->assign('sKey',$sKey);
		$this->display('search+index');
	}

	public function result(){
		$sKey=urldecode(trim(Q::G('key')));
		$sKey=htmlspecialchars($sKey);

		if($sKey){
			if($GLOBALS['_option_']['search_keywords_minlength']>0 && strlen($sKey)<$GLOBALS['_option_']['search_keywords_minlength']){
				$this->E(Q::L('搜索的关键字最少为 %d 字节','Controller',null,$GLOBALS['_option_']['search_keywords_minlength']));
			}
			
			// 赞
			$sGoodCookie=Q::cookie('homefresh_goodnum');
			$arrGoodCookie=explode(',',$sGoodCookie);
			$this->assign('arrGoodCookie',$arrGoodCookie);

			$this->_sKey=$sKey;
			
			// 新鲜事列表
			$arrWhere['A.homefresh_status']=1;
			$arrWhere['A.homefresh_title']=array('like',"%{$sKey}%");

			$nTotalRecord=Model::F_('homefresh','@A')->where($arrWhere)->all()->getCounts();
			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['search_list_num']);
			$arrHomefreshs=Model::F_('homefresh','@A')->where($arrWhere)
				->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_viewnum,A.homefresh_type,A.homefresh_thumb,A.homefreshcategory_id')
				->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
				->joinLeft(Q::C('DB_PREFIX').'homefreshcategory AS E','E.homefreshcategory_name','A.homefreshcategory_id=E.homefreshcategory_id')
				->order('A.homefresh_id DESC')
				->limit($oPage->S(),$oPage->N())
				->getAll();
			
			$this->assign('arrHomefreshs',$arrHomefreshs);
			$this->assign('nTotalHomefreshnum',$nTotalRecord);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('sKey',$sKey);
			
			Core_Extend::getSeo($this,array('title'=>Q::L('搜索结果','Controller')));
			
			$this->display('search+result');
		}else{
			$this->U('home://search/index');
		}
	}

}
