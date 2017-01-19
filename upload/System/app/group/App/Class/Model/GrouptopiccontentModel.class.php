<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子内容模型($$)*/

!defined('Q_PATH') && exit;

class GrouptopiccontentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'grouptopiccontent',
			'check'=>array(
				'grouptopic_content'=>array(
					array('require',Q::L('帖子内容不能为空','__APPGROUP_COMMON_LANG__@Model')),
				),
			),
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	protected function beforeSave_(){
		$this->grouptopic_content=Core_Extend::replaceAttachment(C::cleanJs($this->grouptopic_content));
	}

}
