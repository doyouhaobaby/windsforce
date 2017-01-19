<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除专辑封面操作($$)*/

!defined('Q_PATH') && exit;

class Uncover_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('没有待取消封面的专辑ID','Controller'));
		}

		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nId)->getOne();
		if(empty($oAttachmentcategory['attachmentcategory_id'])){
			$this->E(Q::L('待取消封面的专辑不存在','Controller'));
		}

		$oAttachmentcategory->attachmentcategory_cover='';
		$oAttachmentcategory->save('update');
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		$this->S(Q::L('专辑封面删除成功','Controller'));
	}

}
