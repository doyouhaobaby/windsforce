<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化帐号模型($$)*/

!defined('Q_PATH') && exit;

class SociatypeModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'sociatype',
			'check'=>array(
				'sociatype_title'=>array(
					array('require',Q::L('社会化帐号名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',35,Q::L('社会化帐号名字最大长度为35个字符','__COMMON_LANG__@Common')),
				),
				'sociatype_identifier'=>array(
					array('require',Q::L('社会化帐号唯一识别符不能为空','__COMMON_LANG__@Common')),
					array('english',Q::L('社会化帐号唯一识别符只能为英文字符','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('显社会化帐号唯一识别符为32个字符','__COMMON_LANG__@Common')),
					array('sociatypeIdentifier',Q::L('社会化帐号唯一识别符已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
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
		$this->sociatype_title=C::text($this->sociatype_title);
		$this->sociatype_scope=C::text($this->sociatype_scope);
		$this->sociatype_identifier=C::text($this->sociatype_identifier);
		$this->sociatype_appid=C::text($this->sociatype_appid);
		$this->sociatype_appkey=C::text($this->sociatype_appkey);
	}

	public function sociatypeIdentifier(){
		$nId=Q::G('id','P');

		$sSociatypeIdentifier=Q::G('sociatype_identifier','P');
		$sSociatypeIdentifierInfo='';
		if($nId){
			$arrSociatypeIdentifier=self::F('sociatype_id=?',$nId)->asArray()->getOne();
			$sSociatypeIdentifierInfo=trim($arrSociatypeIdentifier['sociatype_identifier']);
		}

		if($sSociatypeIdentifier!=$sSociatypeIdentifierInfo){
			$arrResult=self::F()->getBysociatype_identifier($sSociatypeIdentifier)->toArray();
			if(!empty($arrResult['sociatype_id'])){
				return false;
			}else{
				return true;
			}
		}

		return true;
	}

}
