<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件管理($$)*/

!defined('Q_PATH') && exit;

class AttachmentController extends InitController{
		
	public function init__(){
		if(ACTION_NAME==='flash_upload'){
			Controller::init__();
			
			// 初始化flash上传数据
			Core_Extend::flashuploadInit();

			// 配置&登陆信息
			Core_Extend::loadCache('option');
			Core_Extend::loginInformation();
		}else{
			parent::init__();
		}

		if(!in_array(ACTION_NAME,array('show','mp3list','get_ajaximg','fullplay_frame','playout','flash_upload'))){
			$this->is_login();
		}
	}
	
	public function index(){
		$this->child('Attachment@Index','index');
	}

	public function add(){
		$this->child('Attachment@Add','index');
	}

	public function dialog_add(){
		$this->child('Attachment@Add','dialog');
	}

	public function new_attachmentcategory(){
		$this->child('Attachment@Newattachmentcategory','index');
	}

	public function new_attachmentcategorysave(){
		$this->child('Attachment@Newattachmentcategory','save');
	}

	public function normal_upload(){
		$this->child('Attachment@Normalupload','index');
	}
	
	public function flash_upload(){
		$this->child('Attachment@Flashupload','index');
	}

	public function flashinfo(){
		$this->child('Attachment@Flashinfo','index');
	}

	public function attachmentinfo(){
		$this->child('Attachment@Attachmentinfo','index');
	}

	public function attachmentinfo_save(){
		$this->child('Attachment@Attachmentinfo','save');
	}

	public function dialog_addattachmentcategory(){
		$this->child('Attachment@Dialogaddattachmentcategory','index');
	}

	public function dialog_attachmentcategorysave(){
		$this->child('Attachment@Dialogaddattachmentcategory','save');
	}

	public function edit_attachmentcategory(){
		$this->child('Attachment@Editattachmentcategory','index');
	}

	public function edit_attachmentcategorysave(){
		$this->child('Attachment@Editattachmentcategory','save');
	}

	public function lists(){
		$this->child('Attachment@Lists','index');
	}

	public function dialog_editattachmentcategorysave(){
		$this->child('Attachment@Editattachmentcategory','dialogsave');
	}

	public function delete_attachmentcategory(){
		$this->child('Attachment@Deleteattachmentcategory','index');
	}

	public function edit_attachment(){
		$this->child('Attachment@Editattachment','index');
	}

	public function edit_attachmentsave(){
		$this->child('Attachment@Editattachment','save');
	}

	public function delete_attachment(){
		$this->child('Attachment@Deleteattachment','index');
	}

	public function delete_attachments(){
		$this->child('Attachment@Deleteattachment','all');
	}

	public function show(){
		$this->child('Attachment@Show','index');
	}

	public function mp3list(){
		$this->child('Attachment@Show','mp3list');
	}

	public function get_ajaximg(){
		$this->child('Attachment@Getajaximg','index');
	}
	
	public function fullplay_frame(){
		$this->child('Attachment@Fullplayframe','index');
	}
	
	public function playout(){
		$this->child('Attachment@Playout','index');
	}

	public function cover(){
		$this->child('Attachment@Cover','index');
	}

	public function uncover(){
		$this->child('Attachment@Uncover','index');
	}

	public function add_attachmentcomment(){
		$this->child('Attachment@Addcomment','index');
	}

}
