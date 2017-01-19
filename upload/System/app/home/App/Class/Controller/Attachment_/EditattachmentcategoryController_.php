<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑专辑($$)*/

!defined('Q_PATH') && exit;

class Editattachmentcategory_C_Controller extends InitController{

	public function index(){
		$nAttachmentcategoryid=intval(Q::G('id'));
		$nDialog=intval(Q::G('dialog','G'));
		$sFunction=trim(Q::G('function','G'));
		$nFiletype=intval(Q::G('filetype','G'));
		$nFull=intval(Q::G('full','G'));
		$nMulti=intval(Q::G('multi','G'));

		if(empty($nAttachmentcategoryid)){
			$this->E(Q::L('你没有选择你要编辑的专辑','Controller'));
		}

		$oAttachmentcategory=Model::F_('attachmentcategory','attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		if(empty($oAttachmentcategory['attachmentcategory_id'])){
			$this->E(Q::L('你要编辑的专辑不存在','Controller'));
		}

		if($oAttachmentcategory['user_id']!=$GLOBALS['___login___']['user_id']){
			$this->E(Q::L('你不能编辑别人的专辑','Controller'));
		}

		$this->assign('oAttachmentcategory',$oAttachmentcategory);
		$this->assign('nDialog',$nDialog);
		$this->assign('sFunction',$sFunction);
		$this->assign('nFiletype',$nFiletype);
		$this->assign('nFull',$nFull);
		$this->assign('nMulti',$nMulti);

		if($nDialog==1){
			$this->display('attachment+dialogeditattachmentcategory');
		}else{
			$this->display('attachment+editattachmentcategory');
		}
	}

	public function save(){
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id','G'));
		$sAttachmentcategoryname=trim(Q::G('attachmentcategory_name','G'));
		$sAttachmentcategorysort=intval(Q::G('attachmentcategory_sort','G'));
		$sAttachmentcategorydescription=trim(Q::G('attachmentcategory_description','G'));
		$sAttachmentcategorycover=trim(Q::G('attachmentcategory_cover','G'));
		
		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		$oAttachmentcategory->attachmentcategory_name=$sAttachmentcategoryname;
		$oAttachmentcategory->attachmentcategory_sort=$sAttachmentcategorysort;
		$oAttachmentcategory->attachmentcategory_description=$sAttachmentcategorydescription;
		$oAttachmentcategory->attachmentcategory_cover=$sAttachmentcategorycover;
		$oAttachmentcategory->save('update');
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		$this->A($oAttachmentcategory->toArray(),Q::L('更新专辑信息成功','Controller'),1);
	}

	public function dialogsave(){
		$nAttachmentcategoryid=intval(Q::G('attachmentcategory_id'));
		$nDialog=intval(Q::G('dialog'));
		$sFunction=trim(Q::G('function'));
		$nFiletype=intval(Q::G('filetype'));
		$nFull=intval(Q::G('full'));
		$nMulti=intval(Q::G('multi'));
		$sAttachmentcategoryname=trim(Q::G('attachmentcategory_name'));
		$nAttachementcategorySort=intval(Q::G('attachementcategory_sort'));

		if(!$sAttachmentcategoryname){
			C::urlGo(Q::U('home://attachment/edit_attachmentcategory?id='.$nAttachmentcategoryid.'&dialog=1&function='.$sFunction.'&filetype='.$nFiletype.'&full='.$nFull),2,Q::L('专辑名字不能为空','Controller'));
			exit();
		}

		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		$oAttachmentcategory->attachmentcategory_sort=$nAttachementcategorySort;
		$oAttachmentcategory->attachmentcategory_name=$sAttachmentcategoryname;
		$oAttachmentcategory->save('update');
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		C::urlGo(Q::U('home://attachment/index?dialog=1&function='.$sFunction.'&filetype='.$nFiletype.'&full='.$nFull.'&multi='.$nMulti),1,Q::L('更新专辑信息成功','Controller'));
	}

}
