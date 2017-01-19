<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除附件专辑($$)*/

!defined('Q_PATH') && exit;

class Deleteattachmentcategory_C_Controller extends InitController{

	public function index($nId=''){
		if(empty($nId)){
			$nAttachmentcategoryid=intval(Q::G('id','G'));
		}else{
			$nAttachmentcategoryid=$nId;
		}

		if(empty($nAttachmentcategoryid)){
			$this->E(Q::L('你没有选择你要删除的专辑','Controller'));
		}

		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		if(empty($oAttachmentcategory['attachmentcategory_id'])){
			$this->E(Q::L('你要删除的专辑不存在','Controller'));
		}

		if($oAttachmentcategory['user_id']!=$GLOBALS['___login___']['user_id']){
			$this->E(Q::L('你不能删除别人的专辑','Controller'));
		}

		$nTotalRecord=Model::F_('attachment','attachmentcategory_id=?',$oAttachmentcategory['attachmentcategory_id'])
			->all()
			->getCounts();
		if($nTotalRecord>0){
			$this->E(Q::L('专辑含有照片，请先删除照片后再删除专辑','Controller'));
		}

		$oAttachmentcategory->destroy();
		if(!$nId){
			$this->S(Q::L('专辑删除成功','Controller'));
		}
	}

}
