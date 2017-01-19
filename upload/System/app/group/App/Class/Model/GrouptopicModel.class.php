<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子模型($$)*/

!defined('Q_PATH') && exit;

class GrouptopicModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'grouptopic',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('grouptopic_username','userName','create','callback'),
			),
			'check'=>array(
				'grouptopic_title'=>array(
					array('require',Q::L('帖子标题不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',300,Q::L('帖子标题不能超过300个字符','__APPGROUP_COMMON_LANG__@Model')),
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
		$this->grouptopic_title=C::text($this->grouptopic_title);
		$this->grouptopic_username=C::text($this->grouptopic_username);
		$this->grouptopic_color=C::strip($this->grouptopic_color);
		$this->grouptopic_updateusername=C::text($this->grouptopic_updateusername);
	}

	public function resetCategory($nCategoryid){
		$oDb=Db::RUN();
		return $oDb->query("UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX'].'grouptopic SET grouptopiccategory_id=0 WHERE grouptopiccategory_id='.$nCategoryid);
	}

	public function rebuildGrouptopicloves($nId=null,$nUserId=null){
		$nGrouptopicloves=Model::F_('grouptopiclove','user_id=? AND grouptopic_id=?',$nUserId===null?$GLOBALS['___login___']['user_id']:$nUserId,$nId===null?$this->grouptopic_id:$nId)
			->all()
			->getCounts();

		if($nId===null){
			$this->grouptopic_loves=$nGrouptopicloves;
			$this->setAutofill(false);
			$this->save('update');
		}else{
			$oGrouptopic=self::F('grouptopic_id=?',$nId)->getOne();
			$oGrouptopic->grouptopic_loves=$nGrouptopicloves;
			$oGrouptopic->setAutofill(false);
			$oGrouptopic->save('update');
		}
	}

	public function saveData(){
		// 启动事务
		$bRollback=false;
		$oDb=$this->getDb()->getConnect();
		$oDb->startTransaction();

		$this->save();
		if($this->isError()){
			$bRollback=true;
		}

		// 保存帖子内容
		if($bRollback===false){
			$oGrouptopiccontent=new GrouptopiccontentModel();
			$oGrouptopiccontent->grouptopic_id=$this->grouptopic_id;
			$oGrouptopiccontent->save();
			if($oGrouptopiccontent->isError()){
				$bRollback=true;
				$this->_sErrorMessage=$oGrouptopiccontent->getErrorMessage();
			}
		}

		if($bRollback===false){
			$oDb->commit();
			return true;
		}else{
			$oDb->rollback();
			return false;
		}
	}

	public function updateData(){
		// 启动事务
		$bRollback=false;
		$oDb=$this->getDb()->getConnect();
		$oDb->startTransaction();

		$this->save('update');
		if($this->isError()){
			$bRollback=true;
		}

		// 保存帖子内容
		if($bRollback===false){
			$oGrouptopiccontent=GrouptopiccontentModel::F('grouptopic_id=?',$this->grouptopic_id)->getOne();
			if(empty($oGrouptopiccontent['grouptopic_id'])){
				$bRollback=true;
			}else{
				$oGrouptopiccontent->save('update');
				if($oGrouptopiccontent->isError()){
					$bRollback=true;
					$this->_sErrorMessage=$oGrouptopiccontent->getErrorMessage();
				}
			}
		}

		if($bRollback===false){
			$oDb->commit();
			return true;
		}else{
			$oDb->rollback();
			return false;
		}
	}

}
