<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动管理相关函数($$)*/

!defined('Q_PATH') && exit;

class Eventadmin_Extend{

	static public function checkEvent($arrEvent){
		if(Core_Extend::isAdmin()){
			return true;
		}
		
		if(empty($arrEvent['event_id'])){
			$arrEvent=Model::F_('event','event_id=? AND event_status=1',$arrEvent)->getOne();
		}

		if(empty($arrEvent['event_id'])){
			return false;
		}

		if($arrEvent['user_id'] && $arrEvent['user_id']==$GLOBALS['___login___']['user_id']){
			return true;
		}

		return false;
	}

}
