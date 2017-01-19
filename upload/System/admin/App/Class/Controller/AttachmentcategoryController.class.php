<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   多媒体专辑管理控制器($$)*/

!defined('Q_PATH') && exit;

class AttachmentcategoryController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.attachmentcategory_name']=array('like',"%".Q::G('attachmentcategory_name')."%");
		$arrMap['A.attachmentcategory_username']=array('like',"%".Q::G('attachmentcategory_username')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		// 是否启用状态
		$arrCondition=array();
		if(isset($_REQUEST['attachment_username']) && $_REQUEST['attachmentcategory_recommend']!=''){
			$arrCondition['attachmentcategory_recommend']=intval($_REQUEST['attachmentcategory_recommend']);
		}

		// 用户
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}

		$this->assign('arrCondition',$arrCondition);
	}

	public function uncover(){
		$nId=intval(Q::G('id','G'));

		if(empty($nId)){
			$this->E(Q::L('没有待取消封面的专辑ID','Controller'));
		}

		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nId)->getOne();
		if(empty($oAttachmentcategory['attachmentcategory_id'])){
			$this->E(Q::L('待取消封面的专辑不存在','Controller'));
		}

		$oAttachmentcategory->attachmentcategory_cover='';
		$oAttachmentcategory->save('update');
		if($oAttachmentcategory->isError()){
			$this->E($oAttachmentcategory->getErrorMessage());
		}

		$this->S(Q::L('专辑封面删除成功','Controller'));
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('recommend',0);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('recommend',1);
	}

	public function add(){
		$this->E(Q::L('后台无法创建专辑','Controller').'<br/><a href="'.Core_Extend::windsforceOuter('app=home&c=attachment&a=my_attachmentcategory').'" target="_blank">'.Q::L('前往创建','Controller').'</a>');
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				$this->delete_attachmentcategory_($nId);
			}
		}
	}

	protected function delete_attachmentcategory_($nAttachmentcategoryid){
		if(empty($nAttachmentcategoryid)){
			$this->E(Q::L('你没有选择你要删除的专辑','Controller'));
		}

		$oAttachmentcategory=AttachmentcategoryModel::F('attachmentcategory_id=?',$nAttachmentcategoryid)->getOne();
		if(empty($oAttachmentcategory['attachmentcategory_id'])){
			$this->E(Q::L('你要删除的专辑不存在','Controller'));
		}

		$nTotalRecord=AttachmentModel::F('attachmentcategory_id=?',$oAttachmentcategory['attachmentcategory_id'])->all()->getCounts();
		if($nTotalRecord>0){
			$this->E(Q::L('专辑含有照片，请先删除照片后再删除专辑','Controller'));
		}
	}

}
