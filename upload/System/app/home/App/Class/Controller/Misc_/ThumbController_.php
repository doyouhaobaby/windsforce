<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   缩略图控制器($$)*/

!defined('Q_PATH') && exit;

class Thumb_C_Controller extends InitController{

	public function index(){
		$nId=trim(Q::G('id','G'));
		$nWidth=intval(Q::G('w','G'));
		$nHeight=intval(Q::G('h','G'));
		$nThumb=intval(Q::G('thumb','G'));
		$nEditor=intval(Q::G('editor','G'));

		// 普通图片
		if($nEditor==0){
			$arrAttachment=Model::F_('attachment','attachment_id=?',$nId)
				->setColumns('attachment_id,attachment_isthumb,attachment_thumbpath,attachment_thumbprefix,attachment_savepath,attachment_savename')
				->getOne();

			if(!empty($arrAttachment['attachment_id'])){
				$sAttachmentpath=Attachment_Extend::getPrefix(true).
					($arrAttachment['attachment_isthumb'] && $nThumb==1?
					$arrAttachment['attachment_thumbpath'].'/'.$arrAttachment['attachment_thumbprefix']:
					$arrAttachment['attachment_savepath'].'/').$arrAttachment['attachment_savename'];
			}else{
				$sAttachmentpath='';
			}
		}else{
			if(!preg_match("/[^\d-.,]/",$nId)){
				$sAttachmentpath=Attachment_Extend::getAttachmentPreview($nId,false);
			}elseif(strpos($nId,'http://')===0 || strpos($nId,'https://')===0){
				// JS忽略
				exit();
			}else{
				$sAttachmentpath=Attachment_Extend::getPrefix(true).$nId;
			}
		}

		Core_Extend::thumb($sAttachmentpath,$nWidth,$nHeight);
		exit();
	}

}
