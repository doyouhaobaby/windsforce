<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户动态列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();

		// 类型
		$sType=trim(Q::G('type','G'));
		if(empty($sType)){
			$sType='';
		}
		$this->assign('sType',$sType);

		switch($sType){
			case 'myself':
				$arrWhere['user_id']=$GLOBALS['___login___']['user_id'];
				break;
			case 'friend':
				// 仅好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				if(!empty($arrUserIds)){
					$arrWhere['user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['user_id']='';
				}
				break;
			case 'all':
				// 这里可以设置用户隐私，比如用户不愿意将动态放出
				break;
			default:
				// 我和好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				$arrUserIds[]=$GLOBALS['___login___']['user_id'];
				if(!empty($arrUserIds)){
					$arrWhere['user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['user_id']='';
				}
				break;
		}

		// 动态列表
		$nTotalRecord=Model::F_('feed')->where($arrWhere)->all()->getCounts();
		$nTotalpage=ceil($nTotalRecord/$GLOBALS['_cache_']['home_option']['feed_list_num']); 
		if($nTotalpage<1){
			$nTotalpage=1;
		}
		
		$arrFeeds=Model::F_('feed')->where($arrWhere)
			->setColumns('feed_id,user_id,feed_username,feed_template,create_dateline,feed_data')
			->order('create_dateline DESC')
			->limit(0,$GLOBALS['_cache_']['home_option']['feed_list_num'])
			->getAll();

		// 最后处理结果
		$arrFeeddatas=array();
		if(is_array($arrFeeds)){
			foreach($arrFeeds as $nKey=>$oFeed){
				$arrData=@unserialize($oFeed['feed_data']);
		
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

		Core_Extend::getSeo($this,array('title'=>Q::L('用户动态','Controller')));

		$this->assign('arrFeeddatas',$arrFeeddatas);
		$this->assign('nTotalFeednum',$nTotalRecord);
		$this->assign('nTotalpage',$nTotalpage);
		$this->display('feed+index');
	}

}
