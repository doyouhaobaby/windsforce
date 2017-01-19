<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动控制器($$)*/

!defined('Q_PATH') && exit;

class EventController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.event_title']=array('like','%'.Q::G('event_title').'%');
		$arrMap['B.eventcategory_name']=array('like','%'.Q::G('eventcategory_name').'%');

		$nEventcategoryId=Q::G('eventcategory_id');
		if($nEventcategoryId!==null && $nEventcategoryId!=''){
			$arrMap['A.eventcategory_id']=$nEventcategoryId;
		}

		// 活动类型
		$nCid=intval(Q::G('cid','G'));
		if($nCid){
			$oEventcategory=EventcategoryModel::F('eventcategory_id=?',$nCid)->getOne();
			if(!empty($oEventcategory['eventcategory_id'])){
				$arrMap['A.eventcategory_id']=$nCid;
				$this->assign('oEventcategory',$oEventcategory);
			}
		}

		// 时间设置
		$this->getTime_('A.event_starttime',$arrMap);
		$this->getTime_('A.event_endtime',$arrMap,'start_time_2','end_time_2');
		$this->getTime_('A.create_dateline',$arrMap,'start_time_3','end_time_3');
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('event',false);
		$this->bAdd_();
		$this->display(Admin_Extend::template('event','event/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."eventcategory AS B','B.eventcategory_name','A.eventcategory_id=B.eventcategory_id')";
	}

	public function add(){
		$this->bAdd_();
		$this->display(Admin_Extend::template('event','event/add'));
	}
	
	public function bAdd_(){
		$oEventcategoryTree=Q::instance('EventcategoryModel')->getEventcategoryTree();
		$this->assign('oEventcategoryTree',$oEventcategoryTree);
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		$this->bEdit_();
		parent::edit('event',$nId,false);
		$this->display(Admin_Extend::template('event','event/add'));
	}

	public function bEdit_(){
		$this->bAdd_();
	}

	protected function checktime_(){
		// 活动时间先验证
		$nEventstarttime=strtotime(trim(Q::G('event_starttime','P')));
		$nEventendtime=strtotime(trim(Q::G('event_endtime','P')));
		$nEventdeadline=strtotime(trim(Q::G('event_deadline','P')));

		if(!$nEventstarttime){
			$this->E(Q::L('活动开始时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if(!$nEventendtime){
			$this->E(Q::L('活动结束时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if(!$nEventdeadline){
			$this->E(Q::L('活动报名截止时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if($nEventstarttime>$nEventendtime){
			$this->E(Q::L('活动结束时间不能早于活动开始时间','__APPEVENT_COMMON_LANG__@Model'));
		}
		
		if($nEventdeadline<CURRENT_TIMESTAMP){
			//$this->E(Q::L('活动报名时间不能早于当前时间','__APPEVENT_COMMON_LANG__@Model'));
		}
		
		if($nEventdeadline>$nEventendtime){
			//$this->E(Q::L('活动报名时间不能晚于活动结束时间','__APPEVENT_COMMON_LANG__@Model'));
		}
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');
		$this->checktime_();
		parent::insert('event',$nId);
	}

	public function AInsertObject_($oModel){
		$oModel->formatTime();
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		$this->checktime_();
		parent::update('event',$nId);
	}

	public function AUpdateObject_($oModel){
		$oModel->formatTime();
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		parent::foreverdelete_deep('event',$sId);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('event',$sId,true);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 解除活动相关数据
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				// 删除关联数据(评论 && 感兴趣和参加数据)
				$oEventcommentMeta=EventcommentModel::M();
				$oEventcommentMeta->deleteWhere(array('event_id'=>$nId));
				if($oEventcommentMeta->isError()){
					$this->E($oEventcommentMeta->getErrorMessage());
				}

				$oEventuserMeta=EventuserModel::M();
				$oEventuserMeta->deleteWhere(array('event_id'=>$nId));
				if($oEventuserMeta->isError()){
					$this->E($oEventuserMeta->getErrorMessage());
				}

				$oEventattentionuserMeta=EventattentionuserModel::M();
				$oEventattentionuserMeta->deleteWhere(array('event_id'=>$nId));
				if($oEventattentionuserMeta->isError()){
					$this->E($oEventattentionuserMeta->getErrorMessage());
				}
			}
		}
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::forbid('event',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::resume('event',$nId,true);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('event',$sField);
	}

}
