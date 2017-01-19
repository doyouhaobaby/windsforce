<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   创建新的专辑处理($$)*/

!defined('Q_PATH') && exit;

class Newattachmentcategory_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			exit($e->getMessage());
		}
		
		$this->display('attachment+newattachmentcategory');
	}

	public function save(){
		$nAttachmentcategorySort=intval(Q::G('attachmentcategory_sort','G'));
		$sAttachmentcategoryName=trim(Q::G('attachmentcategory_name','G'));
		$sAttachmentcategoryDescription=trim(Q::G('attachmentcategory_description','G'));

		$oAttachmentcategory=new AttachmentcategoryModel();
		$oAttachmentcategory->attachmentcategory_sort=$nAttachmentcategorySort;
		$oAttachmentcategory->attachmentcategory_name=$sAttachmentcategoryName;
		$oAttachmentcategory->attachmentcategory_description=$sAttachmentcategoryDescription;
		$oAttachmentcategory->save();
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		$this->A($oAttachmentcategory->toArray(),Q::L('新增专辑成功','Controller'),1);
	}

}
