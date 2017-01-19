<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除附件操作($$)*/

!defined('Q_PATH') && exit;

class Deleteattachment_C_Controller extends InitController{

	protected $_nAttachmentcategoryid=0;
	
	public function index($nId=''){
		$this->S(Q::L('附件删除成功','Controller'));
		exit();// 禁止删除附件，防止程序中内容附件缺失，仅供用户浏览图片。
		
		if(empty($nId)){
			$nAttachmentid=intval(Q::G('id','G'));
		}else{
			$nAttachmentid=$nId;
		}

		if(empty($nAttachmentid)){
			$this->E(Q::L('你没有选择你要删除的附件','Controller'));
		}

		$oAttachment=AttachmentModel::F('attachment_id=?',$nAttachmentid)->getOne();
		if(empty($oAttachment['attachment_id'])){
			$this->E(Q::L('你要删除的附件不存在','Controller'));
		}

		if($oAttachment['user_id']!=$GLOBALS['___login___']['user_id']){
			$this->E(Q::L('你不能删除别人的附件','Controller'));
		}

		$this->_nAttachmentcategoryid=$oAttachment['attachmentcategory_id'];

		// 删除附件图片
		/*
			$sFilepath=Attachment_Extend::getPrefix(true).$oAttachment['attachment_savepath'].'/'.$oAttachment['attachment_savename'];
			$sThumbfilepath=Attachment_Extend::getPrefix(true).$oAttachment['attachment_thumbpath'].'/'.$oAttachment['attachment_savename'];

			if(is_file($sFilepath)){
				@unlink($sFilepath);
			}

			if(is_file($sThumbfilepath)){
				@unlink($sThumbfilepath);
			}
		*/

		$oAttachment->destroy();
		$this->cache_site_();

		if(!$nId){
			$this->S(Q::L('附件删除成功','Controller'));
		}
	}

	public function all(){
		$arrAttachmentid=Q::G('ids','P');
		$arrAttachmentid=explode(',',$arrAttachmentid);

		if(is_array($arrAttachmentid)){
			foreach($arrAttachmentid as $nAttachmentid){
				$this->delete_attachment($nAttachmentid);
			}
		}
			
		$this->S(Q::L('批量删除附件成功','Controller'));
	}

	protected function cache_site_(){
		// 更新附件专辑附件数量统计
		$nAttachmentcategoryid=intval($this->_nAttachmentcategoryid);
		if($nAttachmentcategoryid>0){
			$oAttachmentcategory=Q::instance('AttachmentcategoryModel');
			$oAttachmentcategory->updateAttachmentnum($nAttachmentcategoryid);
		}
	}

}
