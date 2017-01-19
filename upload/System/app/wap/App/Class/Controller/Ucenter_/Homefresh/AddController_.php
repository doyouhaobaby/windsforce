<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加新鲜事($$)*/

!defined('Q_PATH') && exit;

/** 导入home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

class Add_C_Controller extends InitController{

	public function index(){
		try{
			$arrData=array();
			$oLasthomefresh=HomefreshModel::F('user_id=?',$GLOBALS['___login___']['user_id'])
				->order('homefresh_id DESC')
				->getOne();
			if(!empty($oLasthomefresh['homefresh_id'])){
				$arrData['lasttime']=$oLasthomefresh['create_dateline'];
			}
			
			Core_Extend::checkSpam($arrData);
		}catch(Exception $e){
			$this->_oParent->wap_mes($e->getMessage(),'',Q::U('wap://ucenter/index'),0);
		}

		$sMessage=trim(C::cleanJs(Q::G('homefresh_message','P')));
		if(empty($sMessage)){
			$this->_oParent->wap_mes(Q::L('新鲜事内容不能为空','Controller'),Q::U('wap://ucenter/index'),0);
		}

		// 新鲜事模型
		$oHomefresh=new HomefreshModel();
		$oHomefresh->homefresh_message=$sMessage;
		$oHomefresh->homefresh_status=1;
		$oHomefresh->save('create');
		if($oHomefresh->isError()){
			$this->_oParent->wap_mes($oHomefresh->getErrorMessage(),Q::U('wap://ucenter/index'));
		}else{
			// 发送feed
			$sFeedtemplate='<div class="feed_addhomefresh"><span class="feed_title">'.Q::L('发布了一条新鲜事','Controller').'&nbsp;<a href="{@homefresh_link}">'.Q::L('查看','Controller').'</a></span><div class="feed_content">{homefresh_message}</div><div class="feed_action"><a href="{@homefresh_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

			$arrFeeddata=array(
				'@homefresh_link'=>'home://fresh@?id='.$oHomefresh['homefresh_id'],
				'homefresh_message'=>Core_Extend::subString($oHomefresh['homefresh_message'],100,false,1,false),
			);

			try{
				Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
			}catch(Exception $e){
				$this->_oParent->wap_mes($e->getMessage(),Q::U('wap://ucenter/index'),0);
			}

			// 保存home今日数据
			Core_Extend::updateOption(
				array(
					'todayhomefreshnum'=>$GLOBALS['_option_']['todayhomefreshnum']+1,
					'todaytotalnum'=>$GLOBALS['_option_']['todaytotalnum']+1
				)
			);

			$this->cache_site_();

			// 更新积分
			Core_Extend::updateCreditByAction('posthomefresh',$GLOBALS['___login___']['user_id']);
		
			$this->_oParent->wap_mes(Q::L('添加新鲜事成功','Controller'),Q::U('wap://ucenter/index'));
		}
	}

	protected function cache_site_(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("site");
	}

}
