<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动相关函数($$)*/

!defined('Q_PATH') && exit;

class Event_Extend{

	public static function getEventcover($arrEvent){
		if(empty($arrEvent['event_id'])){
			$arrEvent=Model::F_('event','event_status=1 AND event_id=?',$arrEvent)->setColumns('event_id,event_cover')->getOne();
		}

		if(empty($arrEvent['event_id'])){
			return Appt::path('cover.png');
		}

		if(empty($arrEvent['event_cover'])){
			return Appt::path('cover.png');
		}else{
			return Attachment_Extend::getPrefix().$arrEvent['event_cover'];
		}
	}

}
