<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   照片封面操作($$)*/

!defined('Q_PATH') && exit;

class Cover_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('没有待设置的照片ID','Controller'));
		}

		$oAttachment=AttachmentModel::F('attachment_id=?',$nId)->getOne();
		if(!empty($oAttachment['attachment_id'])){
			if($oAttachment['attachmentcategory_id']>0){
				$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$oAttachment['attachmentcategory_id'])->getOne();
				if(empty($oAttachmentcategory['attachmentcategory_id'])){
					$this->E(Q::L('照片的专辑不存在','Controller'));
				}
				$oAttachmentcategory->attachmentcategory_cover=Attachment_Extend::getAttachmenturl_($oAttachment);
				$oAttachmentcategory->save('update');
				if($oAttachmentcategory->isError()){
					$this->E($oAttachmentcategory->getErrorMessage());
				}

				$this->S(Q::L('专辑封面设置成功','Controller'));
			}else{
				$this->E(Q::L('默认专辑不需要设置封面','Controller'));
			}
		}else{
			$this->E(Q::L('待设置的照片不存在','Controller'));
		}
	}

}
