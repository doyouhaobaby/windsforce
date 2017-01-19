<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子搜索缓存模型($$)*/

!defined('Q_PATH') && exit;

class GroupsearchindexModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'groupsearchindex',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('groupsearchindex_ip','getIp','create','callback'),
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
		$this->groupsearchindex_keywords=C::text($this->groupsearchindex_keywords);
		$this->groupsearchindex_searchstring=C::strip($this->groupsearchindex_searchstring);
		$this->groupsearchindex_ids=C::text($this->groupsearchindex_ids);
	}

	public function deleteAll(){
		$oDb=Db::RUN();
		return $oDb->query("DELETE FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].'groupsearchindex');
	}

}
