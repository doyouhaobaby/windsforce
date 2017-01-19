<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   提醒模型($$)*/

!defined('Q_PATH') && exit;

class NoticeModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'notice',
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
		$this->notice_type=C::text($this->notice_type);
		$this->notice_authorusername=C::text($this->notice_authorusername);
		//$this->notice_template=C::strip($this->notice_template);
		//$this->notice_data=C::strip($this->notice_data);
		$this->notice_application=C::text($this->notice_application);
	}

	public function addNotice($sTemplate,$arrData,$nTouserid,$sType,$nFromid,$nUserid,$sUsername){
		$nUserid=intval($nUserid);

		if(is_array($arrData)){
			$sData=serialize($arrData);
			$bNewnotice=true;
			if($nFromid){
				$oNotice=self::F()->where(array('user_id'=>$nTouserid,'notice_type'=>$sType,'notice_authorid'=>$nUserid,'notice_fromid'=>$nFromid))->getOne();
				if(!empty($oNotice['notice_id'])){
					$bNewnotice=false;
					$oNotice->notice_isread=0;
					$oNotice->notice_fromnum=$oNotice->notice_fromnum+1;
					$oNotice->save('update');
					if($oNotice->isError()){
						$this->_sErrorMessage=$oNotice->getErrorMessage();
						return false;
					}

					return true;
				}
			}
			
			if($bNewnotice===true){
				$oNotice=new self(
					array(
						'user_id'=>$nTouserid,
						'notice_type'=>$sType,
						'notice_authorid'=>$nUserid,
						'notice_authorusername'=>$sUsername,
						'notice_template'=>$sTemplate,
						'notice_data'=>$sData,
						'notice_fromnum'=>1,
						'notice_fromid'=>$nFromid,
						'notice_application'=>APP_NAME,
					)
				);
				$oNotice->save();
				if($oNotice->isError()){
					$this->_sErrorMessage=$oNotice->getErrorMessage();
					return false;
				}
			}
		}

		return true;
	}

	public function deleteAllCreatedateline($nTime){
		$oDb=Db::RUN();
		$oDb->query("DELETE FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX']."notice WHERE create_dateline<".(CURRENT_TIMESTAMP-$nTime));
	}

}
