<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件信息更新处理($$)*/

!defined('Q_PATH') && exit;

class Attachmentinfo_C_Controller extends InitController{

	public function index(){
		$sUploadids=trim(Q::G('id','G'));
		$sHashcode=trim(Q::G('hash','G'));
		$sCookieHashcode=Q::cookie('_upload_hashcode_');
		$nAttachmentcategoryid=intval(Q::G('cid'));
		$nDialog=intval(Q::G('dialog'));
		$sFunction=trim(Q::G('function'));
		$nFiletype=intval(Q::G('filetype'));
		$nFull=intval(Q::G('full'));
		$nMulti=intval(Q::G('multi'));

		if(empty($sCookieHashcode)){
			$this->assign('__JumpUrl__',Q::U('home://attachment/add'));
			$this->E(Q::L('附件信息编辑页面已过期','Controller'));
		}

		if($sCookieHashcode!=$sHashcode){
			$this->assign('__JumpUrl__',Q::U('home://attachment/add'));
			$this->E(Q::L('附件信息编辑页面Hash验证失败','Controller'));
		}

		if(empty($sUploadids)){
			$this->assign('__JumpUrl__',Q::U('home://attachment/add'));
			$this->E(Q::L('你没有选择需要编辑的附件','Controller'));
		}

		$arrAttachments=Model::F_('attachment','user_id=? AND attachment_id in('.$sUploadids.')',$GLOBALS['___login___']['user_id'])->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('保存附件信息','Controller')));

		$this->assign('arrAttachments',$arrAttachments);
		$this->assign('nAttachmentcategoryid',$nAttachmentcategoryid);
		$this->assign('nDialog',$nDialog);
		$this->assign('sFunction',$sFunction);
		$this->assign('nFiletype',$nFiletype);
		$this->assign('nFull',$nFull);
		$this->assign('nMulti',$nMulti);

		if($nDialog==1){
			$this->display('attachment+dialogattachmentinfo');
		}else{
			$this->display('attachment+attachmentinfo');
		}
	}

	public function save(){
		$arrAttachments=Q::G('attachments','P');
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id'));

		if(is_array($arrAttachments)){
			foreach($arrAttachments as $nKey=>$arrAttachment){
				$oAttachment=AttachmentModel::F('attachment_id=?',$nKey)->getOne();
				if(!empty($oAttachment['attachment_id'])){
					$oAttachment->changeProp($arrAttachment);
					$oAttachment->save('update');
					if($oAttachment->isError()){
						$this->E($oAttachment->getErrorMessage());
					}
				}
			}
		}

		Q::cookie('_upload_hashcode_',null,-1);

		$this->S(Q::L('附件信息保存成功','Controller'));
	}

}
