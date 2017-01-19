<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分管理类($$)*/

!defined('Q_PATH') && exit;

class Credit{

	public $_nCoef=1;
	public $_arrExtrasql=array();

	public function execrule($sAction,$nUserId=0,$nCoef=1,$nUpdate=1){
		$this->_nCoef=$nCoef;

		$nUserId=intval($nUserId?$nUserId:$GLOBALS['___login___']['user_id']);
		$arrRule=$this->getRule($sAction);

		$bUpdateCredit=false;
		$bEnabled=false;
		if($arrRule){
			for($nI=1;$nI<=8;$nI++){
				if(!empty($arrRule['creditrule_extendcredit'.$nI])){
					$bEnabled=true;
					break;
				}
			}
		}

		if($bEnabled){
			$arrRulelog=array();
			$arrRulelog=$this->getRulelog($arrRule['creditrule_id'],$nUserId);
			if($arrRule['creditrule_rewardnum'] && $arrRule['creditrule_rewardnum']<$nCoef){
				$nCoef=$arrRule['creditrule_rewardnum'];
			}

			if(empty($arrRulelog['creditrulelog_id'])){
				$arrLog=array(
					'user_id'=>$nUserId,
					'creditrule_id'=>$arrRule['creditrule_id'],
					'creditrulelog_total'=>$nCoef,
					'creditrulelog_cyclenum'=>$nCoef,
					'update_dateline'=>CURRENT_TIMESTAMP
				);

				if(in_array($arrRule['creditrule_cycletype'],array(2,3))){
					$arrLog['creditrulelog_starttime']=CURRENT_TIMESTAMP;
				}

				$arrLog=$this->addArrlog($arrLog,$arrRule,0);

				if($nUpdate){
					$oCreditrulelogModel=new CreditrulelogModel($arrLog);
					$oCreditrulelogModel->save();
					if($oCreditrulelogModel->isError()){
						Q::E($oCreditrulelogModel->getErrorMessage());
					}
				}

				$bUpdateCredit=true;
			}else{
				$bNewcycle=false;
				$arrLog=array();
				switch($arrRule['creditrule_cycletype']){
					case 0:
						break;
					case 1:
					case 4:
						if($arrRule['creditrule_cycletype']==1){
							$nToday=strtotime(date('Y-m-d',CURRENT_TIMESTAMP));
							if($arrRulelog['update_dateline']<$nToday && $arrRule['creditrule_rewardnum']){
								$arrRulelog['creditrulelog_cyclenum']=0;
								$bNewcycle=true;
							}
						}

						if(empty($arrRule['creditrule_rewardnum']) || $arrRulelog['creditrulelog_cyclenum']<$arrRule['creditrule_rewardnum']){
							if($arrRule['creditrule_rewardnum']){
								$nRemain=$arrRule['creditrule_rewardnum']-$arrRulelog['creditrulelog_cyclenum'];
								if($nRemain<$nCoef){
									$nCoef=$nRemain;
								}
							}

							$nCyclenunm=$bNewcycle?$nCoef:"creditrulelog_cyclenum+'{$nCoef}'";
							
							$arrLog=array(
								'creditrulelog_cyclenum'=>"creditrulelog_cyclenum={$nCyclenunm}",
								'creditrulelog_total'=>"creditrulelog_total=creditrulelog_total+'{$nCoef}'",
								'update_dateline'=>"update_dateline='".CURRENT_TIMESTAMP."'"
							);

							$bUpdateCredit=true;
						}
						break;
					case 2:
					case 3:
						$nNextcycle=0;

						if($arrRulelog['creditrulelog_starttime']){
							if($arrRule['creditrule_cycletype']==2){
								$nStart=strtotime(date('Y-m-d H:00:00',$arrRulelog['creditrulelog_starttime']));
								$nNextcycle=$nStart+$arrRule['creditrule_cycletime']*3600;
							}else{
								$nNextcycle=$arrRulelog['creditrulelog_starttime']+$arrRule['creditrule_cycletime']*60;
							}
						}

						if(CURRENT_TIMESTAMP<=$nNextcycle && $arrRulelog['creditrulelog_cyclenum']<$arrRule['creditrule_rewardnum']){
							if($arrRule['creditrule_rewardnum']){
								$nRemain=$arrRule['creditrule_rewardnum']-$arrRulelog['creditrulelog_cyclenum'];
								if($nRemain<$nCoef){
									$nCoef=$nRemain;
								}
							}

							$arrLog=array(
								'creditrulelog_cyclenum'=>"creditrulelog_cyclenum=creditrulelog_cyclenum+'{$nCoef}'",
								'creditrulelog_total'=>"creditrulelog_total=creditrulelog_total+'{$nCoef}'",
								'update_dateline'=>"update_dateline='".CURRENT_TIMESTAMP."'"
							);

							$bUpdateCredit=true;
						}elseif(CURRENT_TIMESTAMP>=$nNextcycle){
							$bNewcycle=true;
							$arrLog=array(
								'creditrulelog_cyclenum'=>"creditrulelog_cyclenum={$nCoef}",
								'creditrulelog_total'=>"creditrulelog_total=creditrulelog_total+'{$nCoef}'",
								'update_dateline'=>"update_dateline='".CURRENT_TIMESTAMP."'",
								'creditrulelog_starttime'=>"creditrulelog_starttime='".CURRENT_TIMESTAMP."'",
							);

							$bUpdateCredit=true;
						}
						break;
				}

				if($nUpdate){
					if($arrLog){
						$arrLog=$this->addArrlog($arrLog,$arrRule,1);

						$oCreditrulelogModel=Q::instance('CreditrulelogModel');
						$oCreditrulelogModel->increase($arrRulelog['creditrulelog_id'],$arrLog);
					}
				}
			}
		}

		if($nUpdate && ($bUpdateCredit || $this->_arrExtrasql)){
			if(!$bUpdateCredit){
				if(!Q::classExists('Credit_Extend')){
					require_once(Core_Extend::includeFile('function/Credit_Extend'));
				}
			
				$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
				for($nI=1;$nI<=8;$nI++){
					if(isset($arrAvailableExtendCredits[$nI])){
						$arrRule['creditrule_extendcredit'.$nI]=0;
					}
				}
			}

			$this->updateCreditByRule($arrRule,$nUserId,$nCoef);
		}

		$arrRule['creditrule_updatecredit']=$bUpdateCredit;
		return $arrRule;
	}

	public function updateCreditByRule($arrRule,$nUserId=0,$nCoef=1){
		$this->_nCoef=intval($nCoef);

		$nUserId=$nUserId?$nUserId:intval($GLOBALS['___login___']['user_id']);
		$arrRule=is_array($arrRule)?$arrRule:$this->getRule($arrRule);

		$arrCredit=array();
		$bUpdateCredit=false;

		if(!Q::classExists('Credit_Extend')){
			require_once(Core_Extend::includeFile('function/Credit_Extend'));
		}

		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();

		for($nI=1;$nI<=8;$nI++){
			if(isset($arrAvailableExtendCredits[$nI])){
				$arrCredit['usercount_extendcredit'.$nI]=intval($arrRule['creditrule_extendcredit'.$nI])*$this->_nCoef;
				$bUpdateCredit=true;
			}
		}

		if($bUpdateCredit || $this->_arrExtrasql){
			$this->updateUsercount($arrCredit,$nUserId,$this->_nCoef>0?urldecode($arrRule['creditrule_rulenameuni']):'');
		}
	}

	public function updateUsercount($arrCredit,$nUserId=0,$sRuletxt=''){
		if(!$nUserId){
			$nUserId=intval($GLOBALS['___login___']['user_id']);
		}

		$nUserId=is_array($nUserId)?$nUserId:array($nUserId);
		if($nUserId && ($arrCredit || $this->_arrExtrasql)){
			if($this->_arrExtrasql){
				$arrCredit=array_merge($arrCredit,$this->_arrExtrasql);
			}

			$arrSql=array();
			$arrAllowkey=array('usercount_extendcredit1','usercount_extendcredit2','usercount_extendcredit3',
				'usercount_extendcredit4','usercount_extendcredit5','usercount_extendcredit6','usercount_extendcredit7',
				'usercount_extendcredit8','usercount_friends','usercount_oltime','usercount_fans');
			
			foreach($arrCredit as $sKey=>$value){
				if(!empty($sKey) && $value && in_array($sKey,$arrAllowkey)){
					$arrSql[$sKey]=$value;
				}
			}

			if($arrSql){
				$oUsercountModel=Q::instance('UsercountModel');
				$oUsercountModel->increase($nUserId,$arrSql);
			}

			$this->_arrExtrasql=array();
		}
	}

	public function addArrlog($arrLog,$arrRule,$nIssql=0){
		for($nI=1;$nI<=8;$nI++){
			if(!Q::classExists('Credit_Extend')){
				require_once(Core_Extend::includeFile('function/Credit_Extend'));
			}
			
			$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
			if(isset($arrAvailableExtendCredits[$nI])){
				$nExtendcredit=intval($arrRule['creditrule_extendcredit'.$nI])*$this->_nCoef;
				
				if($nIssql){
					$arrLog['creditrulelog_extendcredit'.$nI]='creditrulelog_extendcredit'.$nI."='{$nExtendcredit}'";
				}else{
					$arrLog['creditrulelog_extendcredit'.$nI]=$nExtendcredit;
				}
			}
		}

		return $arrLog;
	}

	public function getRule($sAction){
		if(empty($sAction)){
			return false;
		}

		Core_Extend::loadCache('creditrule');

		$arrRule=false;
		if(is_array($GLOBALS['_cache_']['creditrule'][$sAction])){
			if(!Q::classExists('Credit_Extend')){
				require_once(Core_Extend::includeFile('function/Credit_Extend'));
			}
			
			$arrRule=$GLOBALS['_cache_']['creditrule'][$sAction];
			$sRulenameuni=$arrRule['creditrule_rulenameuni'];

			for($nI=1;$nI<=8;$nI++){
				$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();

				if(empty($arrAvailableExtendCredits[$nI])){
					unset($arrRule['creditrule_extendcredit'.$nI]);
					continue;
				}

				$arrRule['creditrule_extendcredit'.$nI]=intval($arrRule['creditrule_extendcredit'.$nI]);
			}
		}

		return $arrRule;
	}

	public function getRulelog($nRulelog,$nUserId=0){
		$arrLog=array();
		$nUserId=$nUserId?$nUserId:$GLOBALS['___login___']['user_id'];

		if($nRulelog && $nUserId){
			$arrLog=CreditrulelogModel::F('creditrule_id=? AND user_id=?',$nRulelog,$nUserId)->getOne();
		}

		return $arrLog;
	}

}
