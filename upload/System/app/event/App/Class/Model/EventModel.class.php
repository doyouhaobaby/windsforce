<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动模型($$)*/

!defined('Q_PATH') && exit;

class EventModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'event',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('event_username','userName','create','callback'),
			),
			'check'=>array(
				'event_title'=>array(
					array('require',Q::L('活动名称不能为空','__APPEVENT_COMMON_LANG__@Model')),
					array('max_length',255,Q::L('活动名称不能超过255个字符','__APPEVENT_COMMON_LANG__@Model')),
				),
				'event_address'=>array(
					array('require',Q::L('活动地点不能为空','__APPEVENT_COMMON_LANG__@Model')),
					array('max_length',255,Q::L('活动地点不能超过255个字符','__APPEVENT_COMMON_LANG__@Model')),
				),
				'eventcategory_id'=>array(
					array('require',Q::L('活动类型不能为空','__APPEVENT_COMMON_LANG__@Model')),
				),
				'event_content'=>array(
					array('require',Q::L('活动介绍不能为空','__APPEVENT_COMMON_LANG__@Model')),
				),
			),
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	protected function beforeSave_(){
		$this->event_username=C::text($this->event_username);
		$this->event_title=C::text($this->event_title);
		$this->event_content=Core_Extend::replaceAttachment(C::cleanJs($this->event_content));
		$this->event_contact=C::text($this->event_contact);
		$this->event_jointcontact=C::text($this->event_jointcontact);
		$this->event_contactsite=C::text($this->event_contactsite);
		$this->event_jointcontactsite=C::text($this->event_jointcontactsite);
		$this->event_address=C::text($this->event_address);
		$this->event_cost=C::text($this->event_cost);
		$this->event_costdescription=C::text($this->event_costdescription);
	}

	public function formatTime(){
		$this->event_starttime=strtotime($_POST['event_starttime']);
		$this->event_endtime=strtotime($_POST['event_endtime']);
		$this->event_deadline=strtotime($_POST['event_deadline']);
	}

	public function updateEventcommentnum($nEventid){
		$nEventid=intval($nEventid);
		$oEvent=EventModel::F('event_id=?',$nEventid)->getOne();
		if(!empty($oEvent['event_id'])){
			$nEventcommentnum=EventcommentModel::F('eventcomment_status=1 AND event_id=?',$nEventid)->all()->getCounts();
			$oEvent->event_commentcount=$nEventcommentnum;
			$oEvent->save('update');
			if($oEvent->isError()){
				$this->_sErrorMessage=$oEvent->getErrorMessage();
				return false;
			}
		}

		return true;
	}

	public function updateEventjoinnum($nEventid){
		$nEventid=intval($nEventid);
		$oEvent=EventModel::F('event_id=?',$nEventid)->getOne();
		if(!empty($oEvent['event_id'])){
			$nEventjoinnum=EventuserModel::F('event_id=?',$nEventid)->all()->getCounts();
			$oEvent->event_joincount=$nEventjoinnum;
			$oEvent->save('update');
			if($oEvent->isError()){
				$this->_sErrorMessage=$oEvent->getErrorMessage();
				return false;
			}
		}

		return true;
	}

	public function updateEventattentionnum($nEventid){
		$nEventid=intval($nEventid);
		$oEvent=EventModel::F('event_id=?',$nEventid)->getOne();
		if(!empty($oEvent['event_id'])){
			$nEventattentionnum=EventattentionuserModel::F('event_id=?',$nEventid)->all()->getCounts();
			$oEvent->event_attentioncount=$nEventattentionnum;
			$oEvent->save('update');
			if($oEvent->isError()){
				$this->_sErrorMessage=$oEvent->getErrorMessage();
				return false;
			}
		}

		return true;
	}

}
