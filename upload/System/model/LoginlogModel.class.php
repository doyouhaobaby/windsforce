<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   登录状态记录模型($$)*/

!defined('Q_PATH') && exit;

class LoginlogModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'loginlog',
			'autofill'=>array(
				array('loginlog_ip','getIp','create','callback'),
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
		$this->loginlog_username=C::text($this->loginlog_username);
		$this->login_application=C::text($this->login_application);
	}

	public function deleteAll($nTime){
		$oDb=Db::RUN();
		return $oDb->query("DELETE FROM {$GLOBALS['_commonConfig_']['DB_PREFIX']}loginlog WHERE create_dateline<".(CURRENT_TIMESTAMP-$nTime));
	}

}
