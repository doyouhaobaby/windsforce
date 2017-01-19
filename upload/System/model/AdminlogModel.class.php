<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台管理员操作记录模型($$)*/

!defined('Q_PATH') && exit;

class AdminlogModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'adminlog',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('adminlog_username','userName','create','callback'),
				array('adminlog_ip','getIp','create','callback'),
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
		$this->adminlog_username=C::text($this->adminlog_username);
		$this->adminlog_info=C::strip($this->adminlog_info);
	}

	public function deleteAll($nTime){
		$oDb=Db::RUN();
		return $oDb->query("DELETE FROM {$GLOBALS['_commonConfig_']['DB_PREFIX']}adminlog WHERE create_dateline<".(CURRENT_TIMESTAMP-$nTime));
	}

}
