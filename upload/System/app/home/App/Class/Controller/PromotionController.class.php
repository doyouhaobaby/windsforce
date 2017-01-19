<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   访问推广控制器($$)*/

!defined('Q_PATH') && exit;

class PromotionController extends Controller{

	public function index(){
		// URL地址解密
		if(empty($_GET['k'])){
			$bEncodeMethod=TRUE;
			@list($_GET['fromuid'],$_GET['k'],$_GET['t'],$_GET['sid'])=explode('|',base64_decode(Q::G('fromuid','G')));
		}else{
			$bEncodeMethod=FALSE;
		}

		$nUserid=intval(Q::G('fromuid','G'));
		if(empty($nUserid)){
			return;
		}

		$oUser=UserModel::F('user_id=? AND user_status=1',$nUserid)->getOne();
		if(empty($oUser['user_id'])){
			return;
		}

		if($GLOBALS['___login___']===false || ($nUserid!=$GLOBALS['___login___']['user_id'])){
			$sIp=C::getIp();
			$oTrypromotion=PromotionModel::F('promotion_ip=?',$sIp)->getOne();

			// 仅访问
			if(empty($oTrypromotion['promotion_ip'])){
				$oPromotion=new PromotionModel();
				$oPromotion->user_id=$nUserid;
				$oPromotion->promotion_username=$oUser['user_name'];
				$oPromotion->save();
				if($oPromotion->isError()){
					$this->E($oPromotion->getErrorMessage());
				}

				Core_Extend::updateCreditByAction('promotion_visit',$nUserid);
			}
			
			// 访问和注册
			$nCookiepromotion=Q::cookie('_promotion_');
			if(!empty($nCookiepromotion)){
				$nUserid=intval($nCookiepromotion);
			}

			if($nUserid){
				Q::cookie('_promotion_',$nUserid,1800);
			}
		}
	}

}
