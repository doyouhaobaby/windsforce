<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户动态模型($$)*/

!defined('Q_PATH') && exit;

class FeedModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'feed',
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
		$this->feed_username=C::text($this->feed_username);
		//$this->feed_template=C::strip($this->feed_template);
		//$this->feed_data=C::strip($this->feed_data);
		$this->feed_application=C::text($this->feed_application);
	}

	public function addFeed($sTemplate,$arrData,$nUserid,$nUsername){
		$nUserid=intval($nUserid);

		if(is_array($arrData)){
			$sData=serialize($arrData);
			$oFeed=new self(
				array(
					'user_id'=>$nUserid,
					'feed_username'=>$nUsername,
					'feed_template'=>$sTemplate,
					'feed_data'=>$sData,
					'feed_application'=>APP_NAME,
				)
			);
			$oFeed->save();
			if($oFeed->isError()){
				$this->_sErrorMessage=$oFeed->getErrorMessage();
				return false;
			}
		}

		return true;
	}

	public function deleteAllCreatedateline($nTime){
		$oDb=Db::RUN();
		$oDb->query("DELETE FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX']."feed WHERE create_dateline<".(CURRENT_TIMESTAMP-$nTime));
	}

}
