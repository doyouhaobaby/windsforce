<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息模型($$)*/

!defined('Q_PATH') && exit;

class PmModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'pm',
			'check'=>array(
				'pm_message'=>array(
					array('require',Q::L('短消息内容不能为空','__COMMON_LANG__@Common')),
					array('max_length',1000,Q::L('短消息内容最大长度为1000个字符','__COMMON_LANG__@Common')),
				),
				'pm_subject'=>array(
					array('empty'),
					array('max_length',1000,Q::L('短消息主题最大长度为225个字符','__COMMON_LANG__@Common')),
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
		$this->pm_msgfrom=C::text($this->pm_msgfrom);
		$this->pm_subject=C::text($this->pm_subject);
		$this->pm_message=C::strip($this->pm_message);
		$this->pm_fromapp=C::text($this->pm_fromapp);
		$this->pm_type=C::text($this->pm_type);
		if($this->pm_type==''){
			$this->pm_type='user';
		}
	}

	public function sendAPm($sMessageto,$nUserId,$sUserName,$sSubject='',$sApp=''){
		$oUser=UserModel::F()->getByuser_name($sMessageto);
		
		$oPm=new self();
		$oPm->pm_msgfrom=$sUserName;
		$oPm->pm_msgfromid=$nUserId;
		$oPm->pm_msgtoid=$oUser['user_id'];
		$oPm->pm_status=1;
		$oPm->pm_subject=$sSubject;
		$oPm->pm_fromapp=$sApp;
		$oPm->save();
		if($oPm->isError()){
			$this->_sErrorMessage=$oPm->getErrorMessage();
			return false;
		}else{
			return $oPm;
		}
	}

	public function readSystemmessage($nPmId){
		$oPmsystemread=PmsystemreadModel::F('user_id=? AND pm_id=?',$GLOBALS['___login___']['user_id'],$nPmId)->query();
		if(!empty($oPmsystemread['user_id'])){
			return true;
		}else{
			$oPmsystemread=new PmsystemreadModel();
			$oPmsystemread->user_id=$GLOBALS['___login___']['user_id'];
			$oPmsystemread->pm_id=$nPmId;
			$oPmsystemread->save();
			if($oPmsystemread->isError()){
				$this->_sErrorMessage=$oPmsystemread->getErrorMessage();
			}
			return $oPmsystemread;
		}
	}

	public function deleteSystemmessage($nPmId){
		$oPmsystemdelete=PmsystemdeleteModel::F('user_id=? AND pm_id=?',$GLOBALS['___login___']['user_id'],$nPmId)->query();
		if(!empty($oPmsystemdelete['user_id'])){
			return true;
		}else{
			$oPmsystemdelete=new PmsystemdeleteModel();
			$oPmsystemdelete->user_id=$GLOBALS['___login___']['user_id'];
			$oPmsystemdelete->pm_id=$nPmId;
			$oPmsystemdelete->save();
			if($oPmsystemdelete->isError()){
				$this->_sErrorMessage=$oPmsystemdelete->getErrorMessage();
			}
			return $oPmsystemdelete;
		}
	}

}
