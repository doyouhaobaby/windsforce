<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   多媒体附件评论控制器($$)*/

!defined('Q_PATH') && exit;

class AttachmentcommentController extends AController{

	protected $_arrAttachmentId=array();
	
	public function filter_(&$arrMap){
		$arrMap['A.attachmentcomment_content']=array('like',"%".Q::G('attachmentcomment_content')."%");
		$arrMap['A.attachmentcomment_name']=array('like',"%".Q::G('attachmentcomment_name')."%");
		$arrMap['A.attachmentcomment_ip']=array('like',"%".Q::G('attachmentcomment_ip')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		// 附件检索
		$nAid=intval(Q::G('aid','G'));
		if($nAid){
			$oAttachment=AttachmentModel::F('attachment_id=?',$nAid)->getOne();
			if(!empty($oAttachment['attachment_id'])){
				$arrMap['A.attachment_id']=$nAid;
				$this->assign('oAttachment',$oAttachment);
			}
		}

		// 用户检索
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}
	}

	public function add(){
		$this->E(Q::L('后台无法添加附件评论','Controller'));
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				$oAttachmentcomment=AttachmentcommentModel::F('attachmentcomment_id=?',$nId)->setColumns('attachment_id')->getOne();
				if(!in_array($oAttachmentcomment['attachment_id'],$this->_arrAttachmentId)){
					$this->_arrAttachmentId[]=$oAttachmentcomment['attachment_id'];
				}
			}
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	protected function aForeverdelete($sId){
		$this->aForeverdelete_deep($sId);
	}

	protected function aForeverdelete_deep($sId){
		// 更新附件评论数量
		$oAttachment=Q::instance('AttachmentModel');
		foreach($this->_arrAttachmentId as $nId){
			$oAttachment->updateAttachmentcommentnum($nId);
			if($oAttachment->isError()){
				$oAttachment->getErrorMessage();
			}
		}
	}

}
