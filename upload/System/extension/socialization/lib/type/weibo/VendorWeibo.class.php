<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Sina微博处理类($$)*/

!defined('Q_PATH') && exit;

class VendorWeibo extends Vendor{

	const NAME='weibo';
	protected $_oOauth=null;

	public function __construct(){
		parent::__construct(self::NAME);
		
		new OauthWeibo();
		$this->_oOauth=new SaeTOAuthV2($this->_arrConfig['sociatype_appid'],$this->_arrConfig['sociatype_appkey']);
	}

	public function login($sAppid,$sScope,$sCallback){
		$sState=md5(uniqid(rand(),TRUE));
		Q::cookie('_socia_state_',$sState);

		try{
			$sUrl=$this->_oOauth->getAuthorizeURL($this->_arrConfig['sociatype_callback'],'code',$sState);
			C::urlGo($sUrl);
		}catch(OAuthException $e){
			$this->setErrorMessage($e->getMessage());
			return false;
		}
	}

	public function callback($sAppid,$sAppkey,$sCallback,$sCookieState){
		$sState=trim(Q::G('state','G'));
		$sCode=trim(Q::G('code','G'));
		
		if(!empty($sCookieState) && $sState==$sCookieState){
			$arrKeys= array();
			$arrKeys['code']=$sCode;
			$arrKeys['redirect_uri']=$this->_arrConfig['sociatype_callback'];
			
			try{
				$arrToken=$this->_oOauth->getAccessToken('code',$arrKeys);

				if(isset($arrToken['error_code']) && $arrToken['error_code']>0){
					$sErrorMessage="<h5>Error:</h5>".$arrToken['error_code'];
					$sErrorMessage.="<h5>Msg :</h5>".$arrToken['error'];

					$this->setErrorMessage($sErrorMessage);
					return false;
				}
			}catch(OAuthException $e){
				$this->setErrorMessage($e->getMessage());
				return false;
			}
		
			if($arrToken){
				Q::cookie("_socia_access_token_",$arrToken['access_token'],$GLOBALS['socia_login_time']);
			}
		}else{
			$this->setErrorMessage("The state does not match. You may be a victim of CSRF.");
			return false;
		}
	}

	public function getUserInfo($sAppid){
		if(($this->getAccessToken()===false)){
			return false;
		}
		
		try{
			$sAccesstoken=Q::cookie("_socia_access_token_");

			$oClient=new SaeTClientV2($this->_arrConfig['sociatype_appid'],$this->_arrConfig['sociatype_appkey'],$sAccesstoken);
			$ms=$oClient->home_timeline();
			$arrUidget=$oClient->get_uid();

			if(isset($arrUidget['error_code']) && $arrUidget['error_code']>0){
				$sErrorMessage="<h5>Error:</h5>".$arrUidget['error_code'];
				$sErrorMessage.="<h5>Msg :</h5>".$arrUidget['error'];

				$this->setErrorMessage($sErrorMessage);
				return false;
			}
			
			$nUid=$arrUidget['uid'];
			$arrUserMessage=$oClient->show_user_by_id($nUid);

			return $arrUserMessage;
		}catch(OAuthException $e){
			$this->setErrorMessage($e->getMessage());
			return false;
		}
	}

	public function gotoLoginPage(){
		$this->login($this->_sAppid,$this->_arrConfig['sociatype_scope'],$this->_sCallback);
	}

	public function getAccessToken(){
		$sCookieState=Q::cookie('_socia_state_');
		
		return $this->callback($this->_sAppid,$this->_sSecid,$this->_sCallback,$sCookieState);
	}

	public function showUser($keys=array()){
		$arrWeibouser=$this->getUserInfo($this->_sAppid);

		if($arrWeibouser && $arrWeibouser['id']){
			$arrSaveData=array();
			$arrSaveData['sociauser_openid']=$arrWeibouser['id'];
			$arrSaveData['sociauser_vendor']=$this->_sVendor;
			$arrSaveData['sociauser_gender']=$arrWeibouser['gender']=='m'?'男':'女';
			$arrSaveData['sociauser_name']=$arrWeibouser['name'];
			$arrSaveData['sociauser_nikename']=$arrWeibouser['screen_name'];
			$arrSaveData['sociauser_desc']=$arrWeibouser['description'];
			$arrSaveData['sociauser_img']=$arrWeibouser['profile_image_url'];
			$arrSaveData['sociauser_img1']=$arrWeibouser['avatar_large'];
			$arrSaveData['sociauser_img2']=$arrWeibouser['avatar_large'];
			$arrSaveData['sociauser_vip']='0';
			$arrSaveData['sociauser_level']='0';
			
			return $arrSaveData;
		}

		return FALSE;
	}

}
