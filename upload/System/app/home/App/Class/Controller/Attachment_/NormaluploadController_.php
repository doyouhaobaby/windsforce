<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   普通上传逻辑处理($$)*/

!defined('Q_PATH') && exit;

/** 导入附件上传函数 */
require(Core_Extend::includeFile('function/Upload_Extend'));

class Normalupload_C_Controller extends InitController{

	public function index(){
		try{
			$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id'));
			$sHashcode=C::randString(32);
			Q::cookie('_upload_hashcode_',$sHashcode,3600);

			$arrUploadids=Upload_Extend::uploadNormal();
			$sUploadids=implode(',',$arrUploadids);

			$this->cache_category_();

			// 更新积分
			Core_Extend::updateCreditByAction('postattachment',$GLOBALS['___login___']['user_id']);
			
			if(Q::G('dialog','P')==1){
				C::urlGo(Q::U('home://attachment/lists?cid='.$nAttachmentcategoryid.'&dialog=1&function='.Q::G('function').(Q::G('filetype')==1?'&filetype=1':'').(Q::G('full')==1?'&full=1':'')));
			}else{
				$this->assign('__JumpUrl__',Q::U('home://attachment/attachmentinfo?id='.$sUploadids.'&hash='.$sHashcode.'&cid='.$nAttachmentcategoryid));
				$this->S(Q::L('附件上传成功','Controller'));
			}
		}catch(Exception $e){
			if(Q::G('dialog','P')==1){
				C::urlGo(Q::U('home://attachment/dialog_add?dialog=1&function='.Q::G('function').(Q::G('filetype')==1?'&filetype=1':'').(Q::G('full')==1?'&full=1':'')),2,$e->getMessage());
				exit();
			}else{
				$this->E($e->getMessage());
			}
		}
	}

	protected function cache_category_(){
		// 更新附件专辑附件数量统计
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id'));
		if($nAttachmentcategoryid>0){
			$oAttachmentcategory=Q::instance('AttachmentcategoryModel');
			$oAttachmentcategory->updateAttachmentnum($nAttachmentcategoryid);
		}
	}

}
