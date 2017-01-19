<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   网站公告缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheAnnouncement{

	public static function cache(){
		$arrData=array();

		$arrWhere=array();
		$arrWhere['announcement_status']=1;
		$arrWhere['create_dateline']=array('elt',CURRENT_TIMESTAMP);
		$arrWhere['announcement_endtime']=array('egt',CURRENT_TIMESTAMP);

		$arrAnnouncements=Model::F_('announcement')->where($arrWhere)
			->setColumns('announcement_title,create_dateline,announcement_type,announcement_message,announcement_id')
			->order('announcement_sort ASC,create_dateline DESC')
			->limit(0,10)
			->getAll();
		if(!empty($arrAnnouncements)){
			foreach($arrAnnouncements as $oAnnouncement){
				$arrData[]=array(
					'announcement_title'=>$oAnnouncement['announcement_title'],
					'create_dateline'=>$oAnnouncement['create_dateline'],
					'announcement_url'=>$oAnnouncement['announcement_type']==1?$oAnnouncement['announcement_message']:Q::U('home://msg@?id='.$oAnnouncement['announcement_id']),
				);
			}
		}

		Core_Extend::saveSyscache('announcement',$arrData);
	}

}
