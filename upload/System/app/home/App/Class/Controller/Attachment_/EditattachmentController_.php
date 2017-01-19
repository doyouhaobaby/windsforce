<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑附件($$)*/

!defined('Q_PATH') && exit;

class Editattachment_C_Controller extends InitController{

	public function index(){
		$nAttachmentid=intval(Q::G('id'));
		if(empty($nAttachmentid)){
			$this->E(Q::L('你没有选择你要编辑的附件','Controller'));
		}

		$oAttachment=Model::F_('attachment','attachment_id=?',$nAttachmentid)->getOne();
		if(empty($oAttachment['attachment_id'])){
			$this->E(Q::L('你要编辑的附件不存在','Controller'));
		}

		if($oAttachment['user_id']!=$GLOBALS['___login___']['user_id']){
			$this->E(Q::L('你不能编辑别人的附件','Controller'));
		}

		// 读取我的专辑
		$arrAttachmentcategorys=Model::F_('attachmentcategory','user_id=?',$GLOBALS['___login___']['user_id'])
			->setColumns('attachmentcategory_id,attachmentcategory_name')
			->order('attachmentcategory_sort DESC,create_dateline DESC')
			->getAll();

		$this->assign('oAttachment',$oAttachment);
		$this->assign('arrAttachmentcategorys',$arrAttachmentcategorys);
		$this->display('attachment+editattachment');
	}

	public function save(){
		$nAttachmentid=intval(Q::G('attachment_id','G'));
		$sAttachmentname=trim(Q::G('attachment_name','G'));
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id','G'));

		$oAttachment=AttachmentModel::F('attachment_id=?',$nAttachmentid)->getOne();
		$oAttachment->attachment_name=$sAttachmentname;
		$oAttachment->attachmentcategory_id=$nAttachmentcategoryid;
		$oAttachment->save('update');
		if($oAttachment->isError()){
			$this->E($oAttachment->getErrorMessage());
		}

		$this->A($oAttachment->toArray(),Q::L('更新附件信息成功','Controller'),1);
	}

}
