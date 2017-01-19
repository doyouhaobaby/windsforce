<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   获取更多动态($$)*/

!defined('Q_PATH') && exit;

class Getmorefeed_C_Controller extends InitController{

	public function index(){
		$nPage=intval(Q::G('page','G'));
		if($nPage<2){
			$nPage=2;
		}
		$nPage=$nPage-1;

		// 类型
		$sType=trim(Q::G('type','G'));
		if(empty($sType)){
			$sType='';
		}

		$arrWhere=array();

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

		$arrFeeds=Model::F_('feed')->where($arrWhere)
			->setColumns('feed_id,user_id,feed_username,feed_template,create_dateline,feed_data')
			->order('feed_id DESC')
			->limit($nPage*$GLOBALS['_cache_']['home_option']['feed_list_num'],$GLOBALS['_cache_']['home_option']['feed_list_num'])
			->getAll();

		// 处理动态
		$arrFeeddatas=array();
		if(is_array($arrFeeds)){
			foreach($arrFeeds as $nKey=>$oFeed){
				$arrData=@unserialize($oFeed['feed_data']);
		
				$arrTempdata=array();
				if(is_array($arrData)){
					foreach($arrData as $nK=>$sValueTemp){
						$sTempkey='{'.$nK.'}';
						// URL转换
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

		$this->assign('arrFeeddatas',$arrFeeddatas);
		$this->assign('nPage',$nPage);
		$this->display('ucenter+getmorefeed');
	}

}
 
