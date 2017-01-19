<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   对话框增加附件处理($$)*/

!defined('Q_PATH') && exit;

class Dialogaddattachmentcategory_C_Controller extends InitController{

	public function index(){
		$this->assign('nDialog',intval(Q::G('dialog','G')));
		$this->assign('sFunction',trim(Q::G('function','G')));
		$this->assign('nFiletype',intval(Q::G('filetype','G')));
		$this->assign('nFull',intval(Q::G('full','G')));
		$this->assign('nMulti',intval(Q::G('multi','G')));
		$this->display('attachment+dialogaddattachmentcategory');
	}

	public function save(){
		$nDialog=intval(Q::G('dialog','P'));
		$sFunction=trim(Q::G('function','P'));
		$nFiletype=intval(Q::G('filetype','P'));
		$nFull=intval(Q::G('full','P'));
		$nMulti=intval(Q::G('multi','P'));
		$sAttachmentcategoryname=trim(Q::G('attachmentcategory_name'));
		$nAttachementcategorySort=intval(Q::G('attachementcategory_sort'));

		if(!$sAttachmentcategoryname){
			C::urlGo(Q::U('home://attachment/dialog_addattachmentcategory?dialog=1&function='.$sFunction.'&filetype='.$nFiletype.'&full='.$nFull.'&multi='.$nMulti),2,Q::L('专辑名字不能为空','Controller'),400);
			exit();
		}

		$oAttachmentcategory=new AttachmentcategoryModel();
		$oAttachmentcategory->attachmentcategory_sort=$nAttachementcategorySort;
		$oAttachmentcategory->attachmentcategory_name=$sAttachmentcategoryname;
		$oAttachmentcategory->save('create');
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		C::urlGo(Q::U('home://attachment/index?dialog=1&function='.$sFunction.'&filetype='.$nFiletype.'&full='.$nFull.'&multi='.$nMulti),1,Q::L('专辑保存成功','Controller'),400);
	}

}
