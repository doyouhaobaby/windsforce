<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动详情控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入Home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

/** 定义Home的语言包 */
define('__APPHOME_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/home/App/Lang/Admin');

class Show_C_Controller extends InitController{

	public function index(){
		$nEventid=intval(Q::G('id','G'));
		$sType=trim(Q::G('type','G'));
		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$arrEvent=Model::F_('event','@A','A.event_status=1 AND A.event_id=?',$nEventid)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'eventcategory AS B','B.eventcategory_name','A.eventcategory_id=B.eventcategory_id')
			->getOne();
		if(empty($arrEvent['event_id'])){
			$this->E(Q::L('你要浏览的活动不存在','Controller'));
		}
		
		Core_Extend::loadCache('home_option');

		// 判断邮件等外部地址过来的查找评论地址
		$nIsolationCommentid=intval(Q::G('isolation_commentid','G'));
		if($nIsolationCommentid){
			$result=EventcommentModel::getCommenturlByid($nIsolationCommentid);
			if($result===false){
				$this->E(Q::L('该条评论已被删除、屏蔽或者尚未通过审核','Controller'));
			}

			C::urlGo($result);
			exit();
		}

		if(!in_array($sType,array('user','attentionuser'))){
			$sType='';
		}

		$this->assign('arrEvent',$arrEvent);
		$this->assign('sType',$sType);

		// 判断用户是否已经参加 && 已经感兴趣过 && 参加人数是否已满
		$arrTryjoin=Model::F_('eventuser','event_id=? AND user_id=?',$arrEvent['event_id'],$GLOBALS['___login___']['user_id'])->setColumns('event_id')->getOne();
		
		$arrTryattention=Model::F_('eventattentionuser','event_id=? AND user_id=?',$arrEvent['event_id'],$GLOBALS['___login___']['user_id'])->setColumns('event_id')->getOne();

		$bLimituser=false;
		if($arrEvent['event_limitcount']){
			if($arrEvent['event_limitcount']-$arrEvent['event_joincount']<=0){
				$bLimituser=true;
			}
		}

		$this->assign('bEventend',$arrEvent['event_endtime']<CURRENT_TIMESTAMP);
		$this->assign('bJoinuser',($GLOBALS['___login___']['user_id']==$arrEvent['user_id'] || !empty($arrTryjoin['event_id']))?true:false);
		$this->assign('bAttentionuser',!empty($arrTryattention['event_id'])?true:false);
		$this->assign('bLimituser',$bLimituser);
		
		// 读取评论列表
		if(empty($sType)){
			$arrWhere=array();
			$arrWhere['A.eventcomment_status']=1;
			$arrWhere['A.event_id']=$nEventid;

			$nTotalRecord=Model::F_('eventcomment','@A')->where($arrWhere)
				->all()
				->getCounts();
			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num']);
			$arrEventcommentLists=Model::F_('eventcomment','@A')->where($arrWhere)
				->setColumns('A.eventcomment_id,A.create_dateline,A.user_id,A.eventcomment_name,A.eventcomment_content,A.event_id')
				->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
				->order('A.eventcomment_id ASC')
				->limit($oPage->S(),$oPage->N())
				->getAll();
			
			$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
			$this->assign('nTotalEventcomment',$nTotalRecord);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('arrEventcommentLists',$arrEventcommentLists);
		}

		// 读取成员
		if($sType=='user'){
			$arrWhere=array();
			$arrWhere['A.event_id']=$nEventid;

			$nTotalRecord=Model::F_('eventuser','@A')->where($arrWhere)
				->all()
				->getCounts();
			$oPage=Page::RUN($nTotalRecord,36);
			$arrEventuserLists=Model::F_('eventuser','@A')->where($arrWhere)
				->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
				->order('A.eventuser_status ASC,A.create_dateline DESC')
				->limit($oPage->S(),$oPage->N())
				->getAll();

			$this->assign('nTotalEventuser',$nTotalRecord);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('arrEventuserLists',$arrEventuserLists);
		}

		// 读取感兴趣用户
		if($sType=='attentionuser'){
			$nTotalRecord=Model::F_('eventattentionuser','@A','A.event_id=?',$nEventid)
				->all()
				->getCounts();
			$oPage=Page::RUN($nTotalRecord,36);
			$arrEventuserLists=Model::F_('eventattentionuser','@A','A.event_id=?',$nEventid)
				->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
				->order('A.create_dateline DESC')
				->limit($oPage->S(),$oPage->N())
				->getAll();

			$this->assign('nTotalEventuser',$nTotalRecord);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('arrEventuserLists',$arrEventuserLists);
		}

		// 取得最新参加的成员
		$arrNeweventusers=Model::F_('eventuser','@A','A.eventuser_status=1 AND A.event_id=?',$nEventid)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->limit(0,8)
			->getAll();
		$this->assign('arrNeweventusers',$arrNeweventusers);

		// 取得最新感兴趣的成员
		$arrNeweventattentionusers=Model::F_('eventattentionuser','@A','A.event_id=?',$nEventid)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->limit(0,8)
			->getAll();
		$this->assign('arrNeweventattentionusers',$arrNeweventattentionusers);

		Core_Extend::getSeo($this,array(
			'title'=>$arrEvent['event_title'],
			'keywords'=>$arrEvent['event_title']),true,true);
		
		if(!empty($sType)){
			$this->display('event+'.$sType);
		}else{
			$this->display('event+show');
		}
	}

}
