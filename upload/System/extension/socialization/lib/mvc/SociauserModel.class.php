<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化登录模型($$)*/

!defined('Q_PATH') && exit;

class SociauserModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'sociauser',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	public function checkBinded(){
		$arrUser=Socia::getUser();
		if($arrUser===false){
			return false;
		}

		$oSociauser=self::F('sociauser_vendor=? AND sociauser_openid=?',$arrUser['sociauser_vendor'],$arrUser['sociauser_openid'])->getOne();
		if(!empty($oSociauser['sociauser_id'])){
			return $oSociauser['user_id'];
		}else{
			return false;
		}
	}

	public function checkLogin(){
		return $GLOBALS['___login___']===FALSE?FALSE:$GLOBALS['___login___']['user_id'];
	}

	public function processBind($nUserid){
		if(empty($nUserid)){
			return FALSE;
		}

		$arrUser=Socia::getUser();
		if(empty($arrUser)){
			return false;
		}

		if($this->checkBinded()){
			return false;
		}
		
		$arrUser['user_id']=$nUserid;

		$oSociauser=new SociauserModel($arrUser);
		$oSociauser->save('create');
		if($oSociauser->isError()){
			$this->setErrorMessage($oSociauser->getErrorMessage());
			return false;
		}
	}

	protected function beforeSave_(){
		$this->sociauser_openid=C::text($this->sociauser_openid);
		$this->sociauser_vendor=C::text($this->sociauser_vendor);
		$this->sociauser_name=C::text($this->sociauser_name);
		$this->sociauser_nikename=C::text($this->sociauser_nikename);
		$this->sociauser_desc=C::text($this->sociauser_desc);
		$this->sociauser_url=C::strip($this->sociauser_url);
		$this->sociauser_img=C::strip($this->sociauser_img);
		$this->sociauser_img1=C::strip($this->sociauser_img1);
		$this->sociauser_img2=C::strip($this->sociauser_img2);
		$this->sociauser_gender=C::strip($this->sociauser_gender);
		$this->sociauser_email=C::strip($this->sociauser_email);
		$this->sociauser_location=C::strip($this->sociauser_location);
	}

}
