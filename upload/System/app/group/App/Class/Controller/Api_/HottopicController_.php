<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   热门帖子Api控制器($$)*/

!defined('Q_PATH') && exit;

class Hottopic_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nNum=intval(Q::G('num','G'));
		$nDate=intval(Q::G('date','G'));
		$sType=strtolower(trim(Q::G('type','G')));

		// 基本处理
		if($nNum<2){
			$nNum=2;
		}

		if($nNum>100){
			$nNum=100;
		}

		if($nDate<3600){
			$nData=3600;
		}

		// 获取帖子
		$arrGrouptopics=Model::F_('grouptopic','create_dateline>? AND grouptopic_status=1',CURRENT_TIMESTAMP-$nDate)
			->setColumns('grouptopic_id,grouptopic_title')
			->order('grouptopic_comments DESC')
			->limit(0,$nNum)
			->getAll();

		$arrData=array();
		if(!empty($arrGrouptopics)){
			foreach($arrGrouptopics as $arrGrouptopic){
				$arrGrouptopic['url']=Core_Extend::getSiteurl().Q::U('group://topic@?id='.$arrGrouptopic['grouptopic_id']);
				$arrData['k_'.$arrGrouptopic['grouptopic_id']]=$arrGrouptopic;
			}
		}
		
		Core_Extend::api($arrData,$sType);
		exit();
	}

}
