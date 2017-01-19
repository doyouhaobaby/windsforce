<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我的专辑($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$nDialog=intval(Q::G('dialog','G'));
		$sFunction=trim(Q::G('function','G'));
		$nFiletype=intval(Q::G('filetype','G'));
		$nFull=intval(Q::G('full','G'));
		$nMulti=intval(Q::G('multi','G'));
		
		$arrWhere=array();
		$arrWhere['user_id']=$GLOBALS['___login___']['user_id'];

		// 取得专辑列表
		if($nDialog==1){
			$nEverynum=$GLOBALS['_option_']['attachment_dialogmycategorynum'];
		}else{
			$nEverynum=$GLOBALS['_option_']['attachment_mycategorynum'];
		}

		$nTotalRecord=Model::F_('attachmentcategory')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$nEverynum,'*@~');
		$arrAttachmentcategorys=Model::F_('attachmentcategory')->where($arrWhere)
			->setColumns('attachmentcategory_id,attachmentcategory_name,create_dateline,attachmentcategory_attachmentnum,attachmentcategory_cover')
			->order('attachmentcategory_sort DESC,attachmentcategory_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('我的专辑','Controller')));

		$this->assign('arrAttachmentcategorys',$arrAttachmentcategorys);
		$this->assign('sPageNavbar',$nDialog==1?$oPage->P():$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nDialog',$nDialog);
		$this->assign('sFunction',$sFunction);
		$this->assign('nFiletype',$nFiletype);
		$this->assign('nFull',$nFull);
		$this->assign('nMulti',$nMulti);

		if($nDialog==1){
			$this->display('attachment+dialogindex');
		}else{
			$this->display('attachment+index');
		}
	}

}
