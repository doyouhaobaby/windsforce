<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分记录模型($$)*/

!defined('Q_PATH') && exit;

class CreditlogModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'creditlog',
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
		$this->creditlog_operation=C::text($this->creditlog_operation);
	}

	public function insert($arrData){
		$oCreditlog=new self($arrData);
		$oCreditlog->save();
		if($oCreditlog->isError()){
			$this->_sErrorMessage($oCreditlog->getErrorMessage());
			return false;
		}
		return true;
	}

}
