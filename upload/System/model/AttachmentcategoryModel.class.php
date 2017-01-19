<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件归类(专辑)模型($$)*/

!defined('Q_PATH') && exit;

class AttachmentcategoryModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'attachmentcategory',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('attachmentcategory_username','userName','create','callback'),
			),
			'check'=>array(
				'attachmentcategory_name'=>array(
					array('require',Q::L('附件专辑名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('附件专辑名最大长度为50个字符','__COMMON_LANG__@Common')),
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
		$this->attachmentcategory_name=C::text($this->attachmentcategory_name);
		$this->attachmentcategory_description=C::text($this->attachmentcategory_description);
		$this->attachmentcategory_username=C::text($this->attachmentcategory_username);

		if($this->attachmentcategory_sort<0){
			$this->attachmentcategory_sort=0;
		}
		if($this->attachmentcategory_sort>999){
			$this->attachmentcategory_sort=999;
		}
	}

	public function getAttachmentcategoryByUserid($nUserid){
		return self::F('user_id=?',$nUserid)->getAll();
	}

	public function updateAttachmentnum($nAttachmentcategoryid){
		$nAttachmentcategoryid=intval($nAttachmentcategoryid);
		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		if(!empty($oAttachmentcategory['attachmentcategory_id'])){
			$nAttachmentnum=AttachmentModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->all()->getCounts();
			$oAttachmentcategory->attachmentcategory_attachmentnum=$nAttachmentnum;
			$oAttachmentcategory->save('update');
			if($oAttachmentcategory->isError()){
				$this->_sErrorMessage=$oAttachmentcategory->getErrorMessage();
				return false;
			}
		}

		return true;
	}

}
