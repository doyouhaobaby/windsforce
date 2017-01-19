<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分等级缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheCreditrule{

	public static function cache(){
		$arrData=array();

		$arrCreditruls=Model::F_('creditrule')->getAll();
		if(is_array($arrCreditruls)){
			foreach($arrCreditruls as $arrRule){
				$arrRule['creditrule_rulenameuni']=urlencode($arrRule['creditrule_name']);
				$arrData[$arrRule['creditrule_action']]=$arrRule;
			}
		}

		Core_Extend::saveSyscache('creditrule',$arrData);
	}

}
