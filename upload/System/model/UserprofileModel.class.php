<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户个人信息扩展模型($$)*/

!defined('Q_PATH') && exit;

class UserprofileModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'userprofile',
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
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'] as $sField){
			if(in_array($sField,array('userprofile_gender','userprofile_birthyear','userprofile_birthmonth','userprofile_birthday'))){
				$this->{$sField}=intval($this->{$sField});
			}else{
				$this->{$sField}=C::strip($this->{$sField});
			}
		}
	}

	static public function getUserprofileById($nUserId,$sField='userprofile_site'){
		$oUserprofile=UserprofileModel::F('user_id=?',$nUserId)->setColumns($sField)->query();
		if(empty($oUserprofile[$sField])){
			return null;
		}
		
		return $oUserprofile[$sField];
	}

}
