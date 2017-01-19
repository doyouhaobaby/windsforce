<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户模型($$)*/

!defined('Q_PATH') && exit;

class UserModel extends CommonModel{

	static public function init__(){

		return array(
			'table_name'=>'user',
			'check'=>array(
				'user_email'=>array(
					array('require',Q::L('E-mail不能为空','__COMMON_LANG__@Common')),
					array('email',Q::L('E-mail格式错误','__COMMON_LANG__@Common')),
					array('max_length',150,Q::L('E-mail不能超过150个字符','__COMMON_LANG__@Common'))
				),
				'user_name'=>array(
					array('require',Q::L('用户名不能为空','__COMMON_LANG__@Common')),
				),
				'user_password'=>array(
					array('require',Q::L('用户密码不能为空','__COMMON_LANG__@Common')),
					array('min_length',6,Q::L('用户密码最小长度为6个字符','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('用户密码最大长度为32个字符','__COMMON_LANG__@Common')),
				),
				'user_sign'=>array(
					array('empty'),
					array('max_length',1000,Q::L('用户签名最大长度为1000个字符','__COMMON_LANG__@Common')),
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

	public function isUsernameExists($sUsername,$nUserId=0){
		$oResult=self::F()->getByuser_name($sUsername);
		if(!empty($oResult['user_id'])){
			if($nUserId==0){
				return true;
			}else{
				if($oResult['user_id']==$nUserId){
					return false;
				}else{
					return true;
				}
			}
		}else{
			return false;
		}
	}

	public function isUseremailExists($sUseremail,$nUserId=0){
		$oResult=self::F()->getByuser_email($sUseremail);
		if(!empty($oResult['user_id'])){
			if($nUserId==0){
				return true;
			}else{
				if($oResult['user_id']==$nUserId){
					return false;
				}else{
					return true;
				}
			}
		}else{
			return false;
		}
	}

	public function changePassword($sPassword,$sNewPassword,$sOldPassword,$bIgnoreOldPassword=false,$arrUserData=array(),$bResetPassword=false){
		if(empty($arrUserData) && $bResetPassword===false){
			$arrUserData=$GLOBALS['___login___'];
		}

		if($bIgnoreOldPassword===false && $sOldPassword==''){
			$this->_sErrorMessage=Q::L('旧密码不能为空','__COMMON_LANG__@Common');
		}

		if($sPassword==''){
			$this->_sErrorMessage=Q::L('新密码不能为空','__COMMON_LANG__@Common');
		}

		if($sPassword!=$sNewPassword){
			$this->_sErrorMessage=Q::L('两次输入的密码不一致','__COMMON_LANG__@Common');
		}

		if(!Q::classExists('Auth')){
			require_once(Core_Extend::includeFile('class/Auth'));
		}

		Auth::changePassword($arrUserData['user_name'],$sPassword,$sOldPassword,$bIgnoreOldPassword);
		if(Auth::isError()){
			$this->_sErrorMessage=Auth::getErrorMessage();
		}

		return true;
	}

	public function checkLoginCommon($sUserName,$sPassword,$bEmail,$sApp='admin',$nLoginCookieTime=null){
		// 是否记住登陆状态
		if($nLoginCookieTime===null){
			if(Q::G('remember_me','P')){
				if(Q::G('remember_time','P')==0){
					$nLoginCookieTime=null;
				}else{
					$nLoginCookieTime=intval(Q::G('remember_time','P'));
				}
			}
		}

		if(!Q::classExists('Auth')){
			require_once(Core_Extend::includeFile('class/Auth'));
		}

		$oUser=Auth::checkLogin($sUserName,$sPassword,$bEmail,$nLoginCookieTime);

		if($GLOBALS['_option_']['loginlog_record']==1){
			$oLoginlog=new LoginlogModel();
			$oLoginlog->loginlog_username=$sUserName;
			$oLoginlog->login_application=$sApp;
		}

		if($GLOBALS['_option_']['loginlog_record']==1 && is_object($oUser)){
			$oLoginlog->user_id=$oUser->user_id;
		}

		if(Auth::isError()){
			if($GLOBALS['_option_']['loginlog_record']==1){
				$oLoginlog->loginlog_status=0;
				$oLoginlog->save();
			}
			$this->_sErrorMessage=Auth::getErrorMessage();
			return false;
		}else{
			if($oUser->isError()){
				if($GLOBALS['_option_']['loginlog_record']==1){
					$oLoginlog->loginlog_status=0;
					$oLoginlog->save();
				}
				$this->_sErrorMessage=$oUser->getErrorMessage();
				return false;
			}

			if($GLOBALS['_option_']['loginlog_record']==1){
				$oLoginlog->loginlog_status=1;
				$oLoginlog->save();
				if($oLoginlog->isError()){
					$this->_sErrorMessage=$oLoginlog->getErrorMessage();
					return false;
				}
			}
		}

		return true;
	}

	protected function beforeSave_(){
		$this->user_name=C::text($this->user_name);
		$this->user_nikename=C::text($this->user_nikename);
		$this->user_remark=C::text($this->user_remark);
		$this->user_sign=C::text($this->user_sign);
		$this->user_extendstyle=C::text($this->user_extendstyle);
		$this->user_verifycode=C::text($this->user_verifycode);
	}

	static public function getUsernameById($nUserId,$sField='user_name'){
		static $arrUser=array();

		if(!isset($arrUser[$nUserId])){
			$arrData=Model::F_('user','user_id=?',$nUserId)->setColumns('user_id,user_name,user_nikename,user_email,user_sign,user_extendstyle')->getOne();
			$arrUser[$arrData['user_id']]=$arrData;
		}
		return $arrUser[$nUserId][$sField];
	}

	protected function beforeCreate_(){
		// 验证用户是否唯一
		if(self::$_arrMeta[$this->_sClassName]->find(array('user_name'=>$this->user_name))->getCounts()>0){
			Q::E(Q::L('用户名%s只能够唯一','__QEEPHP__@Q',null,$this->user_name));
		}
		
		$sRandom=C::randString(6);

		// 保存数据
		$this->changeProp('user_password',$this->encodePassword_($this->user_password,$sRandom));
		$this->changeProp('user_random',$sRandom);
		$this->changeProp('user_registerip',C::getIp());
	}

	private function encodePassword_($sCleartext,$sRandom){
		return md5(md5($sCleartext).$sRandom);
	}

}
