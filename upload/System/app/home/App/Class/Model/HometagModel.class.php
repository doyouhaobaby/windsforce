<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户标签模型($$)*/

!defined('Q_PATH') && exit;

class HometagModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'hometag',
			'check'=>array(
				'hometag_name'=>array(
					array('require',Q::L('用户标签不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',32,Q::L('用户标签不能超过32个字符','__APPHOME_COMMON_LANG__@Model')),
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
		$this->hometag_name=C::text($this->hometag_name);
	}

	public function addTag($nUserId,$sTags,$sOldTags=''){
		if($nUserId && $sTags){
			$sTags=str_replace('，',',',$sTags);
			$sTags=str_replace(' ',',',$sTags);
			$sOldTags=str_replace('，',',',$sOldTags);
			$sOldTags=str_replace(' ',',',$sOldTags);

			$arrTags=array_slice(Q::normalize(explode(',',$sTags)),0,5);
			foreach($arrTags as $sTagName){
				$sTagName=C::text($sTagName);

				// 标签不存在，插入标签
				if($sTagName){
					$nTagCount=self::F('hometag_name=?',$sTagName)->all()->getCounts();
					if(!$nTagCount){
						// 插入标签
						$oTag=new self();
						$oTag->hometag_name=$sTagName;
						$oTag->save('create');
						if($oTag->isError()){
							$this->_sErrorMessage=$oTag->getErrorMessage();
							return false;
						}
					}else{
						$oTag=self::F('hometag_name=?',$sTagName)->getOne();
					}

					// 标签索引
					$nTagId=$oTag->hometag_id;
					$nTagIndexCount=HometagindexModel::F('hometag_id=? AND user_id=?',$nTagId,$nUserId)->all()->getCounts();
					if(!$nTagIndexCount){
						$oHometagindex=new HometagindexModel();
						$oHometagindex->user_id=$nUserId;
						$oHometagindex->hometag_id=$nTagId;
						$oHometagindex->save('create');
						if($oHometagindex->isError()){
							$this->_sErrorMessage=$oHometagindex->getErrorMessage();
							return false;
						}
					}

					// 更新标签中用户数量
					$nTagIdCount=HometagindexModel::F('hometag_id=?',$nTagId)->all()->getCounts();
					$oTag->hometag_count=$nTagIdCount;
					if(isset($_POST['hometag_name'])){
						unset($_POST['hometag_name']);// 这里防止自动填充
					}
					$oTag->save('update');
					if($oTag->isError()){
						$this->_sErrorMessage=$oTag->getErrorMessage();
						return false;
					}
				}
				
				if(!empty($arrOldTags)){
					$arrHometags=HometagModel::F()->where(array('hometag_name'=>array('in',$arrOldTags)))->getAll();
					if(is_array($arrHometags)){
						foreach($arrHometags as $oHometag){
							// 标签索引数据
							$oHometagindexMeta=HometagindexModel::M();
							$oHometagindexMeta->deleteWhere(array('hometag_id'=>$oHometag['hometag_id']));
							if($oHometagindexMeta->isError()){
								$this->_sErrorMessage=$oHometagindexMeta->getErrorMessage();
								return false;
							}

							// 更新标签数据
							$nTagIdCount=self::F('hometag_id=?',$oHometag['hometag_id'])->all()->getCounts();
							$oTag->hometag_count=$nTagIdCount;
							if(isset($_POST['hometag_name'])){
								unset($_POST['hometag_name']);// 这里防止自动填充
							}
							$oTag->save('update');
							if($oTag->isError()){
								$this->_sErrorMessage=$oTag->getErrorMessage();
								return false;
							}
						}
					}
				}
			}
		}
	}

	public function getTagsByUserid($nUserid){
		$arrTagids=Model::F_('hometagindex','user_id=?',$nUserid)->getColumn('hometag_id',true);
		return self::F(array('hometag_id'=>array('in',$arrTagids?$arrTagids:array(0))))->getAll();
	}

	public function getOneTag($nTagId){
		return self::F('hometag_id=?',$nTagId)->getOne();
	}

}
