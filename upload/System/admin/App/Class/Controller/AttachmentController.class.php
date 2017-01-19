<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   多媒体管理控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入Home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

/** 定义Home的语言包 */
define('__APPHOME_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/home/App/Lang/Admin');

class AttachmentController extends AController{

	protected $_arrAttachmentcategory=array();

	public function filter_(&$arrMap){
		$arrMap['A.attachment_name']=array('like',"%".Q::G('attachment_name')."%");
		$arrMap['A.attachment_username']=array('like',"%".Q::G('attachment_username')."%");
		$arrMap['A.attachment_extension']=array('like',"%".Q::G('attachment_extension')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		// 用户
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}

		// 类型
		$sType=trim(Q::G('type','G'));
		if($sType){
			$arrMap['A.attachment_extension']=$sType;
			$this->assign('sType',$sType);
		}

		// 专辑
		$nCid=Q::G('cid','G');
		if($nCid!==null){
			$arrAttachmentcategory=array(Q::L('默认专辑','Controller'),0);
			if($nCid>0){
				$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nCid)->getOne();
				if(!empty($oAttachmentcategory['attachmentcategory_id'])){
					$arrAttachmentcategory=array($oAttachmentcategory['attachmentcategory_name'],$nCid);
				}
			}
			$arrMap['A.attachmentcategory_id']=$nCid;
			$this->assign('arrAttachmentcategory',$arrAttachmentcategory);
		}
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."attachmentcategory AS B','B.attachmentcategory_id,B.attachmentcategory_name,B.attachmentcategory_cover,B.attachmentcategory_attachmentnum','A.attachmentcategory_id=B.attachmentcategory_id')".
			"->joinLeft('".Q::C('DB_PREFIX')."user AS C','C.user_name','A.user_id=C.user_id')";
	}

	public function add(){
		$this->E(Q::L('后台无法上传附件','Controller').'<br/><a href="'.Core_Extend::windsforceOuter('app=home&c=attachment&a=add').'" target="_blank">'.Q::L('前往上传','Controller').'</a>');
	}

	public function cover(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('没有待设置的照片ID','Controller'));
		}

		$oAttachment=AttachmentModel::F('attachment_id=?',$nId)->getOne();
		if(!empty($oAttachment['attachment_id'])){
			if($oAttachment['attachmentcategory_id']>0){
				$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$oAttachment['attachmentcategory_id'])->getOne();
				if(empty($oAttachmentcategory['attachmentcategory_id'])){
					$this->E(Q::L('照片的专辑不存在','Controller'));
				}
				$oAttachmentcategory->attachmentcategory_cover=Attachment_Extend::getAttachmenturl_($oAttachment);
				$oAttachmentcategory->save('update');
				if($oAttachmentcategory->isError()){
					$this->E($oAttachmentcategory->getErrorMessage());
				}

				$this->S(Q::L('专辑封面设置成功','Controller'));
			}else{
				$this->E(Q::L('默认专辑不需要设置封面','Controller'));
			}
		}else{
			$this->E(Q::L('待设置的照片不存在','Controller'));
		}
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('recommend',0);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('recommend',1);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				$this->delete_attachment_($nId);
			}
		}
	}

	public function aForeverdelete_deep($sId){
		$arrAttachmentcategory=$this->_arrAttachmentcategory;
		if(is_array($arrAttachmentcategory)){
			foreach($arrAttachmentcategory as $nAttachmentcategory){
				$this->update_attachmentnum_($nAttachmentcategory);
			}
		}

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			// 附件评论数据
			$oAttachmentcommentMeta=AttachmentcommentModel::M();
			$oAttachmentcommentMeta->deleteWhere(array('attachment_id'=>$nId));
			if($oAttachmentcommentMeta->isError()){
				$this->E($oAttachmentcommentMeta->getErrorMessage());
			}
		}
	}

	protected function delete_attachment_($nId){
		if(empty($nId)){
			$this->E(Q::L('你没有选择你要删除的附件','Controller'));
		}

		$oAttachment=AttachmentModel::F('attachment_id=?',$nId)->getOne();
		if(empty($oAttachment['attachment_id'])){
			$this->E(Q::L('你要删除的附件不存在','Controller'));
		}

		// 删除附件图片
		$sFilepath=WINDSFORCE_PATH.'/user/attachment/'.$oAttachment['attachment_savepath'].'/'.$oAttachment['attachment_savename'];
		$sThumbfilepath=WINDSFORCE_PATH.'/user/attachment/'.$oAttachment['attachment_thumbpath'].'/'.$oAttachment['attachment_savename'];

		if(is_file($sFilepath)){
			@unlink($sFilepath);
		}

		if(is_file($sThumbfilepath)){
			@unlink($sThumbfilepath);
		}

		// 记录附件专辑ID
		if($oAttachment['attachmentcategory_id']>0 && !in_array($oAttachment['attachmentcategory_id'],$this->_arrAttachmentcategory)){
			$this->_arrAttachmentcategory[]=$oAttachment['attachmentcategory_id'];
		}
	}

	protected function update_attachmentnum_($nAttachmentcategoryid){
		// 更新附件专辑附件数量统计
		$nAttachmentcategoryid=intval($nAttachmentcategoryid);
		if($nAttachmentcategoryid>0){
			$oAttachmentcategory=Q::instance('AttachmentcategoryModel');
			$oAttachmentcategory->updateAttachmentnum($nAttachmentcategoryid);
		}
	}

}
