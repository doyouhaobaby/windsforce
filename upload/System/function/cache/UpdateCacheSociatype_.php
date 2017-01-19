<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化登陆缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheSociatype{

	public static function cache(){
		$arrData=array();

		$arrSociatypes=Model::F_('sociatype','sociatype_status=?',1)
			->order('create_dateline ASC')
			->getAll();
		if(is_array($arrSociatypes)){
			foreach($arrSociatypes as $oSociatype){
				$arrData[$oSociatype['sociatype_identifier']]=array(
					'sociatype_id'=>$oSociatype['sociatype_id'],
					'sociatype_identifier'=>$oSociatype['sociatype_identifier'],
					'sociatype_title'=>$oSociatype['sociatype_title'],
					'sociatype_appid'=>$oSociatype['sociatype_appid'],
					'sociatype_appkey'=>$oSociatype['sociatype_appkey'],
					'sociatype_callback'=>$oSociatype['sociatype_callback'],
					'sociatype_scope'=>$oSociatype['sociatype_scope'],
					'sociatype_logo'=>__ROOT__.'/System/extension/socialization/static/images/'.$oSociatype['sociatype_identifier'].'/'.$oSociatype['sociatype_identifier'].'_login.gif',
					'sociatype_icon'=>__ROOT__.'/System/extension/socialization/static/images/'.$oSociatype['sociatype_identifier'].'/'.$oSociatype['sociatype_identifier'].'.gif',
					'sociatype_bind'=>__ROOT__.'/System/extension/socialization/static/images/'.$oSociatype['sociatype_identifier'].'/'.$oSociatype['sociatype_identifier'].'_bind.gif',
					'sociatype_bindsmall'=>__ROOT__.'/System/extension/socialization/static/images/'.$oSociatype['sociatype_identifier'].'/'.$oSociatype['sociatype_identifier'].'_bind_small.gif',
				);
			}
		}

		Core_Extend::saveSyscache('sociatype',$arrData);
	}

}
