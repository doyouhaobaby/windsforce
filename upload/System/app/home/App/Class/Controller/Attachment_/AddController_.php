<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件上传界面($$)*/

!defined('Q_PATH') && exit;

class Add_C_Controller extends InitController{

	public function index($bDialog=false){
		try{
			$arrData=array();
			$arrLastattachment=Model::F_('attachment','user_id=?',$GLOBALS['___login___']['user_id'])
				->setColumns('attachment_id,create_dateline')
				->order('create_dateline DESC')
				->getOne();
			if(!empty($arrLastattachment['attachment_id'])){
				$arrData['lasttime']=$arrLastattachment['create_dateline'];
			}
			Core_Extend::checkSpam($arrData);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
		
		$nAttachmentcategoryid=intval(Q::G('cid','G'));
		$nUploadfileMaxsize=Core_Extend::getUploadSize($GLOBALS['_option_']['uploadfile_maxsize']);
		$nUploadFileMode=$GLOBALS['_option_']['upload_file_mode'];
		$sAllAllowType=$GLOBALS['_option_']['upload_allowed_type'];

		$arrAllowedTypes=array();
		$nFiletype=intval(Q::G('filetype'));
		$nFull=intval(Q::G('full'));
		$nMulti=intval(Q::G('multi'));
		if(empty($sAllAllowType)){
			$sAllAllowType='*.*';
			if($nFiletype==1){
				$sAllAllowType='*.jpeg;*.jpg;*.gif;*.bmp;*.png;';
				$arrAllowedTypes=array('jpeg','jpg','png','bmp','gif');
			}
		}else{
			$arrTemp=explode('|',$sAllAllowType);
			$arrType=array('jpeg','jpg','png','bmp','gif');
			if($nFiletype==1){
				foreach($arrType as $nK=>$sType){
					if(!in_array($sType,$arrTemp)){
						unset($arrType[$nK]);
					}
				}
				$arrTemp=$arrType;
			}elseif($nFiletype==2){
				foreach($arrTemp as $nK=>$sType){
					if(in_array($sType,$arrType)){
						unset($arrTemp[$nK]);
					}
				}
			}

			$arrAllowedTypes=$arrTemp;
			$arrTempAllAllowTypeResult=array();
			foreach($arrAllowedTypes as $sV){
				$arrTempAllAllowTypeResult[]='*.'.$sV;
			}
			$sAllAllowType=implode(';',$arrTempAllAllowTypeResult);
		}

		$nUploadFlashLimit=intval($GLOBALS['_option_']['upload_flash_limit']);
		if($nUploadFlashLimit<0){
			$nUploadFlashLimit=0;
		}

		$nFileInputNum=$GLOBALS['_option_']['upload_input_num'];

		// 登录使用COOKIE
		$sAuth=Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'auth');
		$nUploadIsauto=$GLOBALS['_option_']['upload_isauto'];

		// 附件分类
		$arrAttachmentcategorys=Attachment_Extend::getAttachmentcategory();

		// 是否有专辑
		if($nAttachmentcategoryid>0){
			$arrTryattachmentcategory=Model::F_('attachmentcategory','attachmentcategory_id=? AND user_id=?',$nAttachmentcategoryid,$GLOBALS['___login___']['user_id'])->getOne();
			if(empty($arrTryattachmentcategory['attachmentcategory_id'])){
				$nAttachmentcategoryid=false;
			}else{
				$bFound=false;
				foreach($arrAttachmentcategorys as $oAttachmentcategory){
					if($oAttachmentcategory['attachmentcategory_id']==$nAttachmentcategoryid){
						$bFound=true;
						break;
					}
				}
				if($bFound===false){
					$nAttachmentcategoryid=false;
				}
			}
		}else{
			$nAttachmentcategoryid=false;
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('上传附件','Controller')));

		$this->assign('nAttachmentcategoryid',$nAttachmentcategoryid);
		$this->assign('nUploadfileMaxsize',$nUploadfileMaxsize);
		$this->assign('nUploadFileMode',$nUploadFileMode);
		$this->assign('sAllAllowType',$sAllAllowType);
		$this->assign('nUploadFlashLimit',$nUploadFlashLimit);
		$this->assign('nFileInputNum',$nFileInputNum);
		$this->assign('arrAllowedTypes',$arrAllowedTypes);
		$this->assign('nFiletype',$nFiletype);
		$this->assign('nFull',$nFull);
		$this->assign('nMulti',$nMulti);
		$this->assign('sAuth',$sAuth);
		$this->assign('nUploadIsauto',$nUploadIsauto);
		$this->assign('arrAttachmentcategorys',$arrAttachmentcategorys);

		if($bDialog===false){
			$this->display('attachment+add');
		}else{
			$this->display('attachment+dialogadd');
		}
	}

	public function dialog(){
		$this->assign('sFunction',trim(Q::G('function','G')));
		$this->assign('nFiletype',intval(Q::G('filetype','G')));
		$this->assign('nFull',intval(Q::G('full','G')));
		$this->assign('nMulti',intval(Q::G('multi','G')));
		$this->assign('bDialog',true);
		$this->index(true);
	}

}
