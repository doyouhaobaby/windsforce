<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题变量模型($$)*/

!defined('Q_PATH') && exit;

class StylevarModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'stylevar',
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
		$this->stylevar_variable=C::text($this->stylevar_variable);
		$this->stylevar_substitute=C::cleanJs(strip_tags($this->stylevar_substitute));
	}

	public function saveStylevarData($arrStylevariableData,$nStyleId){
		foreach($arrStylevariableData as $sKey=>$sValue){
			$sKey=strtolower($sKey);
			$oTryStylevar=self::F('stylevar_variable=? AND style_id=?',$sKey,$nStyleId)->getOne();
			if(!empty($oTryStylevar['stylevar_id'])){
				$oTryStylevar->stylevar_substitute=$sValue;
				$oTryStylevar->save('update');
				if($oTryStylevar->isError()){
					$this->_sErrorMessage=$oTryStylevar->getErrorMessage();
				}
			}else{
				$oStylevar=new self();
				$oStylevar->style_id=$nStyleId;
				$oStylevar->stylevar_variable=$sKey;
				$oStylevar->stylevar_substitute=$sValue;
				$oStylevar->save('create');
				if($oStylevar->isError()){
					$this->_sErrorMessage=$oStylevar->getErrorMessage();
				}
			}
		}

		return true;
	}

}
