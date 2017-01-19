<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   公告模型($$)*/

!defined('Q_PATH') && exit;

class AnnouncementModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'announcement',
			'autofill'=>array(
				array('announcement_username','userName','create','callback'),
			),
			'check'=>array(
				'announcement_sort'=>array(
					array('number',Q::L('序号只能是数字','__COMMON_LANG__@Common')),
				),
				'announcement_title'=>array(
					array('require',Q::L('公告标题不能为空','__COMMON_LANG__@Common')),
					array('max_length',225,Q::L('公告标题最大长度为225','__COMMON_LANG__@Common'))
				),
				'announcement_message'=>array(
					array('require',Q::L('公告内容不能为空','__COMMON_LANG__@Common')),
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

	public function timeFormat(){
		$_POST['create_dateline']=strtotime($_POST['create_dateline']);
		$_POST['announcement_endtime']=strtotime($_POST['announcement_endtime']);
	}

	protected function beforeSave_(){
		$this->announcement_title=C::text($this->announcement_title);
		$this->announcement_message=C::cleanJs($this->announcement_message);
		$this->announcement_username=C::text($this->announcement_username);
	}

	public function deleteAllEndtime($nTimestamp){
		$oDb=Db::RUN();
		$oDb->query("DELETE FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX']."announcement WHERE announcement_endtime<".$nTimestamp." AND announcement_endtime<>'0'");
	}

}
