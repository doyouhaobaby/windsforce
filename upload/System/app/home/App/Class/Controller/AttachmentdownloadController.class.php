<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件下载数量更新管理($$)*/

!defined('Q_PATH') && exit;

class AttachmentdownloadController extends InitController{
	
	public function index(){
		$nId=intval(Q::G('id','G'));

		if(empty($nId)){
			$this->E(Q::L('没有指定更新的附件ID','Controller'));
		}

		$oAttachment=AttachmentModel::F('attachment_id=?',$nId)->getOne();
		if(empty($oAttachment['attachment_id'])){
			$this->E(Q::L('你请求的附件不存在','Controller'));
		}

		$bHidereallypath=Attachment_Extend::attachmentHidereallypath($oAttachment);
		if(!$bHidereallypath){
			// 记录下载
			$bDownload=false;
			if($sAttachmentCookie=Q::cookie('attachment_read')){
				$arrAttachmentIds=explode(',',$sAttachmentCookie);
				if(in_array($nId,$arrAttachmentIds)){
					$bDownload=true;
				}
			}

			if($bDownload===false){
				$oAttachment->attachment_download=$oAttachment->attachment_download+1;
				$oAttachment->setAutofill(false);
				$oAttachment->save('update');
				if($oAttachment->isError()){
					$this->E($oAttachment->getErrorMessage());
				}

				$sAttachmentCookie.=empty($sAttachmentCookie)?$nId:','.$nId;
				Q::cookie('attachment_read',$sAttachmentCookie,86400);
			}
		}
		
		$this->S(Q::L('下载成功','Controller'),1);
	}

}
