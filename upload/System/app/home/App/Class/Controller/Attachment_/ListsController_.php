<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我的附件($$)*/

!defined('Q_PATH') && exit;

class Lists_C_Controller extends InitController{

	public function index(){
		$nAttachmentcategoryid=Q::G('cid','G');
		$nDialog=intval(Q::G('dialog','G'));
		$sFunction=trim(Q::G('function','G'));
		$nFiletype=intval(Q::G('filetype','G'));
		$nFull=intval(Q::G('full','G'));
		$nMulti=intval(Q::G('multi','G'));

		$arrWhere=array();

		if($nFiletype=='1'){
			$arrWhere['attachment_isimg']=1;
		}elseif($nFiletype=='2'){
			$arrWhere['attachment_isimg']=0;
		}

		if($nAttachmentcategoryid!==null){
			$arrWhere['A.attachmentcategory_id']=intval($nAttachmentcategoryid);

			// 取得专辑信息
			$arrAttachmentcategoryinfo=array();
			if($nAttachmentcategoryid==0){
				$nDefaultattachmentnum=Model::F_('attachment','user_id=? AND attachmentcategory_id=0',$GLOBALS['___login___']['user_id'])
					->all()
					->getCounts();
				$arrAttachmentcategoryinfo['totalnum']=$nDefaultattachmentnum;
			}elseif($nAttachmentcategoryid>0){
				$arrAttachmentcategoryinfo=Model::F_('attachmentcategory','attachmentcategory_id=?',$nAttachmentcategoryid)
					->setColumns('attachmentcategory_id,attachmentcategory_name,create_dateline,attachmentcategory_attachmentnum')
					->getOne();
				if(empty($oAttachmentcategoryinfo)){
					$arrAttachmentcategoryinfo=false;
				}
			}

			$this->assign('arrAttachmetncategoryinfo',$arrAttachmentcategoryinfo);
		}

		$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];

		// 取得附件列表
		if($nDialog==1){
			$nEverynum=$GLOBALS['_option_']['attachment_dialogmyattachmentnum'];
		}else{
			$nEverynum=$GLOBALS['_option_']['attachment_myattachmentnum'];
		}

		$nTotalRecord=Model::F_('attachment','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$nEverynum,'*@~');
		$arrAttachments=Model::F_('attachment','@A')->where($arrWhere)
			->setColumns('A.attachment_id,A.create_dateline,A.attachment_name,A.attachment_extension,A.attachment_isthumb,A.attachment_savepath,A.attachment_savename,A.attachment_thumbpath,A.attachmentcategory_id,A.attachment_size,A.attachment_isimg')
			->joinLeft(Q::C('DB_PREFIX').'attachmentcategory AS B','B.attachmentcategory_cover,B.attachmentcategory_name','A.attachmentcategory_id=B.attachmentcategory_id')
			->order('A.attachment_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('我的附件','Controller')));

		$this->assign('arrAttachments',$arrAttachments);
		$this->assign('nAttachmentcategoryid',$nAttachmentcategoryid);
		$this->assign('nDialog',$nDialog);
		$this->assign('sFunction',$sFunction);
		$this->assign('nFiletype',$nFiletype);
		$this->assign('nFull',$nFull);
		$this->assign('nMulti',$nMulti);
		$this->assign('sPageNavbar',$nDialog==1?$oPage->P():$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));

		if($nDialog==1){
			$this->display('attachment+dialoglists');
		}else{
			$this->display('attachment+lists');
		}
	}

}
