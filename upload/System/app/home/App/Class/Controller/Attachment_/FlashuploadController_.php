<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Flash上传逻辑处理($$)*/

!defined('Q_PATH') && exit;

/** 导入附件上传函数 */
require(Core_Extend::includeFile('function/Upload_Extend'));

class Flashupload_C_Controller extends InitController{

	public function index(){
		// flash上传验证
		$bSessionExists=Core_Extend::flashuploadAuth();
		if($bSessionExists===false){
			echo '<div class="upload-error">'.
					'<strong style="color:red;">You do not have access to upload</strong>'.
				'</div>';
			exit;
		}

		try{
			$_POST['attachmentcategory_id']=intval(Q::G('attachmentcategory_id'));
			$arrUploadids=Upload_Extend::uploadFlash();
			echo ($arrUploadids[0]);

			$this->cache_category_();

			// 更新积分
			Core_Extend::updateCreditByAction('postattachment',$GLOBALS['___login___']['user_id']);

			exit();
		}catch(Exception $e){
			echo '<div class="upload-error">'.
						sprintf('&#8220;%s&#8221; has failed to upload due to an error',htmlspecialchars($_FILES['Filedata']['name'])).'</strong><br />'.
						htmlspecialchars($e->getMessage()).
				'</div>';
			exit;
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
