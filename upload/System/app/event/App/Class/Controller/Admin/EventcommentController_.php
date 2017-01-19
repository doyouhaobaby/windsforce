<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动评论控制器($$)*/

!defined('Q_PATH') && exit;

class EventcommentController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.eventcomment_name']=array('like','%'.Q::G('eventcomment_name').'%');
		$arrMap['B.user_name']=array('like','%'.Q::G('user_name').'%');
		$arrMap['A.eventcomment_content']=array('like','%'.Q::G('eventcomment_content').'%');

		// 活动检索
		$nEid=intval(Q::G('eid','G'));
		if($nEid){
			$oEvent=EventModel::F('event_id=?',$nEid)->getOne();
			if(!empty($oEvent['event_id'])){
				$arrMap['A.event_id']=$nEid;
				$this->assign('oEvent',$oEvent);
			}
		}

		// 用户检索
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('eventcomment',false);
		$this->display(Admin_Extend::template('event','eventcomment/index'));
	}
	
	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."user AS B','B.user_name','A.user_id=B.user_id')";
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::forbid('eventcomment',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::resume('eventcomment',$nId,true);
	}

	public function add(){
		$this->E(Q::L('后台无法添加活动评论','__APPEVENT_COMMON_LANG__@Controller'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('eventcomment',$nId,false);
		$this->display(Admin_Extend::template('event','eventcomment/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('eventcomment',$nId);
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('eventcomment',$sId);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('eventcomment',$sId,true);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 更新活动评论数量
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				// 更新评论数量
				$oEvent=Q::instance('EventModel');
				$oEvent->updateEventcommentnum($nId);
				if($oEvent->isError()){
					$oEvent->getErrorMessage();
				}
			}
		}
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('eventcomment',$sField);
	}

}
