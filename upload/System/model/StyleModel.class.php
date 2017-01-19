<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题模型($$)*/

!defined('Q_PATH') && exit;

class StyleModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'style',
			'check'=>array(
				'style_name'=>array(
					array('require',Q::L('主题名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('主题名字最大长度为32个字符','__COMMON_LANG__@Common')),
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
		$this->style_name=C::text($this->style_name);
		$this->style_extend=strip_tags($this->style_extend);
	}

	public function saveStyleData($arrStyleData,$nThemeId,$nStyleId=0){
		$bStyleExists=false;

		if(!empty($nStyleId)){
			$oTryStyle=StyleModel::F('style_id=?',$nStyleId)->getOne();
			if(!empty($oTryStyle['style_id'])){
				$bStyleExists=true;
				$oTryStyle->changeProp($arrStyleData);
				$oTryStyle->save('update');
				if($oTryStyle->isError()){
					$this->_sErrorMessage=$oTryStyle->getErrorMessage();
				}
			}
		}

		if($bStyleExists===false){
			$oStyle=new StyleModel($arrStyleData);
			$oStyle->save();
			if($oStyle->isError()){
				$this->_sErrorMessage=$oStyle->getErrorMessage();
			}
			$nStyleId=$oStyle['style_id'];
		}

		return $nStyleId;
	}

}
