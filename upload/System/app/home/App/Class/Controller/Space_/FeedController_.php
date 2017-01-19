<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人动态($$)*/

!defined('Q_PATH') && exit;

class Feed_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));

		$arrUserInfo=Model::F_('user')
			->setColumns('user_id,user_name')
			->where(array('user_status'=>1,'user_id'=>$nId))
			->getOne();

		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
			$this->_arrUserInfo=$arrUserInfo;
		}

		// 动态列表
		$arrWhere['user_id']=$nId;
		$nTotalRecord=Model::F_('feed')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['feed_list_num'],'@space@?id='.$nId.'&type=feed');
		$arrFeeds=Model::F_('feed')->where($arrWhere)
			->setColumns('feed_id,user_id,feed_username,feed_template,create_dateline,feed_data')
			->order('feed_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		// 最后处理结果
		$arrFeeddatas=array();
		if(is_array($arrFeeds)){
			foreach($arrFeeds as $nKey=>$oFeed){
				$arrData=unserialize($oFeed['feed_data']);
		
				$arrTempdata=array();
				if(is_array($arrData)){
					foreach($arrData as $nK=>$sValueTemp){
						$sTempkey='{'.$nK.'}';

						// @开头表示URL，调用Q::U来生成地址
						if(strpos($nK,'@')===0){
							$sValueTemp=Q::U($sValueTemp);
						}

						$arrTempdata[$sTempkey]=$sValueTemp;
					}
				}

				$arrFeeddatas[]=array(
					'feed_id'=>$oFeed['feed_id'],
					'user_id'=>$oFeed['user_id'],
					'feed_username'=>$oFeed['feed_username'],
					'feed_content'=>strtr($oFeed['feed_template'],$arrTempdata),
					'create_dateline'=>$oFeed['create_dateline'],
				);
			}
		}

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('用户动态','Controller')));
		
		$this->assign('nId',$nId);
		$this->assign('arrFeeddatas',$arrFeeddatas);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('space+feed');
	}

	public function index_title_(){
		return $this->_arrUserInfo['user_name'].' - '.Q::L('用户动态','Controller');
	}

	public function index_keywords_(){
		return $this->index_title_();
	}

	public function index_description_(){
		return $this->index_title_();
	}

}
