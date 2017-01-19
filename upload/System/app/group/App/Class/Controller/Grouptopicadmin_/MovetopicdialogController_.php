<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   移动帖子对话框控制器($$)*/

!defined('Q_PATH') && exit;

class Movetopicdialog_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('groupid','G'));
		$sGrouptopicid=trim(Q::G('grouptopicid','G'));

		$arrGrouptopicid=Q::normalize($sGrouptopicid);
		$sGrouptopics=implode(',',$arrGrouptopicid);
		if(empty($sGrouptopics)){
			$this->E(Q::L('没有待操作的帖子','Controller'));
		}
		
		$this->assign('sGrouptopics',$sGrouptopics);
		$this->assign('nGroupid',$nGroupid);
		$this->assign('sTitle',Q::L('你选择了 %d 篇帖子','Controller',null,count($arrGrouptopicid)));
		$this->display('grouptopicadmin+movetopicdialog');
	}

}
