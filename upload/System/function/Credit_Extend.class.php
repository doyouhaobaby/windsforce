<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分相关函数($$)*/

!defined('Q_PATH') && exit;

class Credit_Extend{
	
	public static function getCreditType($nCycleType){
		switch($nCycleType){
			case 0:
				return Q::L('一次','__COMMON_LANG__@Common');
				break;
			case 1:
				return Q::L('每天','__COMMON_LANG__@Common');
				break;
			case 2:
				return Q::L('整点','__COMMON_LANG__@Common');
				break;
			case 3:
				return Q::L('间隔分钟','__COMMON_LANG__@Common');
				break;
			case 4:
				return Q::L('不限','__COMMON_LANG__@Common');
				break;
		}
	}

	public static function getAvailableExtendCredits(){
		$arrAvailableExtendCredits=array();

		$arrExtendCredits=unserialize($GLOBALS['_option_']['extend_credit']);
		foreach($arrExtendCredits as $nKey=>$arrExtendCredit){
			if($arrExtendCredit['available']==1){
				$arrAvailableExtendCredits[$nKey]=$arrExtendCredit;
			}
		}

		return $arrAvailableExtendCredits;
	}

	public static function checkCredit($nCreditNum){
		return ($nCreditNum>=-99 && $nCreditNum<=99);
	}

	public static function updateUsercount($nUserid,$arrData=array(),$sOperation='',$nRelatedid=0,$sRuletxt=''){
		if(empty($nUserid)){
			return;
		}

		if(!is_array($arrData) || empty($arrData)){
			return;
		}

		if($sOperation && $nRelatedid){
			$bWritelog=true;
		} else {
			$bWritelog=false;
		}

		$arrSavedata=$arrCreditLog=array();
		foreach($arrData as $sKey=>$nValue){
			if(empty($nValue)){
				continue;
			}

			$nVal = intval($nValue);
			$nId=intval($sKey);
			$nId=!$nId && substr($sKey,0,-1)=='extcredits'? intval(substr($sKey,-1,1)):$nId;
			if(0<$nId && $nId<9){
				$arrSavedata['usercount_extendcredit'.$nId]=$nValue;
				if($bWritelog){
					$arrCreditLog['creditlog_extcredits'.$nId]=$nValue;
				}
			}else{
				$arrCreditLog[$sKey]=$nValue;
			}
		}

		if($bWritelog){
			self::creditLog($nUserid,$sOperation,$nRelatedid,$arrCreditLog);
		}

		if($arrSavedata){
			if(!Q::classExists('Credit')){
				require_once(Core_Extend::includeFile('class/Credit'));
			}

			$oCredit=Q::instance('Credit');
			$oCredit->updateUsercount($arrSavedata,$nUserid,$sRuletxt);
		}
	}

	public static function creditLog($nUserid,$sOperation,$nRelatedid,$arrCreditLog){
		if(!$sOperation || empty($nRelatedid) || empty($nUserid) || empty($arrCreditLog)){
			return;
		}

		$arrSavedata=array(
			'user_id' =>$nUserid,
			'creditlog_operation'=>$sOperation,
			'creditlog_relatedid'=>$nRelatedid,
		);

		foreach($arrCreditLog as $sKey=>$nValue){
			$arrSavedata[$sKey]=$nValue;
		}

		if(is_array($nUserid)){
			foreach($nUserid as $nKey=>$nOneuserid){
				$arrSavedata['user_id']=$nOneuserid;
				$arrSavedata['creditlog_relatedid']=is_array($nRelatedid)?$nRelatedid[$nKey]:$nRelatedid;

				$oCreditlog=Q::instance('CreditlogModel');
				$oCreditlog->insert($arrSavedata);
				if($oCreditlog->isError()){
					Q::E($oCreditlog->getErrorMessage());
				}
			}
		}else{
				$oCreditlog=Q::instance('CreditlogModel');
				$oCreditlog->insert($arrSavedata);
				if($oCreditlog->isError()){
					Q::E($oCreditlog->getErrorMessage());
				}
		}
	}

}
