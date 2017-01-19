<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   计划任务模型($$)*/

!defined('Q_PATH') && exit;

class CronModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'cron',
			'check'=>array(
				'cron_name'=>array(
					array('require',Q::L('计划任务名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('计划任务名最大长度为50个字符','__COMMON_LANG__@Common')),
				),
				'on_update'=>array(
					'cron_filename'=>array(
						array('require',Q::L('计划任务脚本不能为空','__COMMON_LANG__@Common')),
						array('max_length',50,Q::L('计划任务脚本最大长度为50个字符','__COMMON_LANG__@Common')),
					),
					'cron_minute'=>array(
						array('max_length',36,Q::L('计划任务分钟最大长度为36个字符','__COMMON_LANG__@Common')),
					),
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
		$this->cron_type=C::text($this->cron_type);
		$this->cron_name=C::text($this->cron_name);
		$this->cron_filename=C::strip($this->cron_filename);
	}

	public function fetchNextrun($nTimestamp){
		$nTimestamp=intval($nTimestamp);
		return self::F('cron_status=1 AND cron_nextrun<=?',$nTimestamp)->order('cron_nextrun')->getOne();
	}

	public function fetchNextcron(){
		return self::F('cron_status=?',1)->order('cron_nextrun')->getOne();
	}

}
