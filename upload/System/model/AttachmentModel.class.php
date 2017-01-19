<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件上传模型($$)*/

!defined('Q_PATH') && exit;

class AttachmentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'attachment',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('attachment_username','userName','create','callback'),
			),
			'check'=>array(
				'attachment_name'=>array(
					array('require',Q::L('附件名不能为空','__COMMON_LANG__@Common')),
					array('max_length',100,Q::L('附件名最大长度为100个字符','__COMMON_LANG__@Common')),
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
		$this->attachment_name=C::text($this->attachment_name);
		$this->attachment_type=C::text($this->attachment_type);
		$this->attachment_extension=C::text($this->attachment_extension);
		$this->attachment_savepath=C::text($this->attachment_savepath);
		$this->attachment_savename=C::text($this->attachment_savename);
		$this->attachment_thumbprefix=C::text($this->attachment_thumbprefix);
		$this->attachment_thumbpath=C::text($this->attachment_thumbpath);
		$this->attachment_username=C::text($this->attachment_username);
	}

	public function upload($arrUploadinfos){
		if(empty($arrUploadinfos)){
			return FALSE;
		}

		$arrUploadinfoTemps=array();
		$nUploadcategoryId=Q::G('attachmentcategory_id','P');
		if($nUploadcategoryId===null){
			$nUploadcategoryId=0;
		}

		foreach($arrUploadinfos as $nKey=>$arrUploadinfo){
			foreach($arrUploadinfo as $sKey=>$value){
				$arrUploadinfoTemps[$nKey]['attachment_'.$sKey]=$value;
				$arrUploadinfoTemps[$nKey]['attachmentcategory_id']=$nUploadcategoryId;
			}
		}
		unset($arrUploadinfos);

		$arrUploadids=array();
		foreach($arrUploadinfoTemps as $arrUploadinfoTemp){
			if(!in_array($arrUploadinfoTemp['attachment_extension'],array('jpg','jpeg','gif','png','bmp')) || !is_file($arrUploadinfoTemp['attachment_thumbpath'].'/'.$arrUploadinfoTemp['attachment_savename'])){
				$arrUploadinfoTemp['attachment_isthumb']=0;
				$arrUploadinfoTemp['attachment_thumbpath']='';
				$arrUploadinfoTemp['attachment_thumbprefix']='';
			}
			
			if(!in_array($arrUploadinfoTemp['attachment_extension'],array('jpg','jpeg','gif','png','bmp'))){
				$arrUploadinfoTemp['attachment_isimg']=0;
			}
			
			$arrUploadinfoTemp['attachment_savepath']=str_replace(C::tidyPath(WINDSFORCE_PATH.'/user/attachment').'/','',C::tidyPath($arrUploadinfoTemp['attachment_savepath']));
			$arrUploadinfoTemp['attachment_thumbpath']=str_replace(C::tidyPath(WINDSFORCE_PATH.'/user/attachment/').'/','',C::tidyPath($arrUploadinfoTemp['attachment_thumbpath']));
			$arrUploadinfoTemp['attachment_name']=substr($arrUploadinfoTemp['attachment_name'],0,strrpos($arrUploadinfoTemp['attachment_name'],'.'));
		
			$oUpload=new self($arrUploadinfoTemp);
			$oUpload->save('create');
			if($oUpload->isError()){
				$this->_sErrorMessage=$oUpload->getErrorMessage();
				return FALSE;
			}
			$arrUploadids[]=$oUpload['attachment_id'];
		}

		return $arrUploadids;
	}

	public function updateAttachmentcommentnum($nAttachmentid){
		$nAttachmentid=intval($nAttachmentid);
		$oAttachment=AttachmentModel::F('attachment_id=?',$nAttachmentid)->getOne();
		if(!empty($oAttachment['attachment_id'])){
			$nAttachmentcommentnum=Model::F_('attachmentcomment','attachmentcomment_status=1 AND attachment_id=?',$nAttachmentid)
				->all()
				->getCounts();
			$oAttachment->attachment_commentnum=$nAttachmentcommentnum;
			$oAttachment->save('update');
			if($oAttachment->isError()){
				$this->_sErrorMessage=$oAttachment->getErrorMessage();
				return false;
			}
		}

		return true;
	}

}
