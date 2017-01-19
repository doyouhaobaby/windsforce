<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Flash上传附件信息处理($$)*/

!defined('Q_PATH') && exit;

class Flashinfo_C_Controller extends InitController{

	public function index(){
		$arrUploadids=Q::G('attachids','P');
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id_flash'));
		$nDialog=intval(Q::G('dialog'));
		$sFunction=trim(Q::G('function'));
		$nFiletype=intval(Q::G('filetype'));
		$nMulti=intval(Q::G('multi','G'));

		$sHashcode=C::randString(32);
		Q::cookie('_upload_hashcode_',$sHashcode,3600);

		$sUploadids=implode(',',$arrUploadids);
		if($nDialog==1){
			$this->U('home://attachment/attachmentinfo?id='.$sUploadids.'&hash='.$sHashcode.'&cid='.$nAttachmentcategoryid.'&dialog=1&function='.$sFunction.($nFiletype==1?'&filetype='.$nFiletype:'').($nFull==1?'&full='.$nFull:'').($nMulti==1?'&multi='.$nMulti:''));
		}else{
			$this->U('home://attachment/attachmentinfo?id='.$sUploadids.'&hash='.$sHashcode.'&cid='.$nAttachmentcategoryid);
		}
	}

}
