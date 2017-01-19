<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   屏蔽或者显示多个回帖对话框控制器($$)*/

!defined('Q_PATH') && exit;

class Hidecommentdialog_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('groupid','G'));
		$nGrouptopicid=intval(Q::G('grouptopicid','G'));
		$sGrouptopiccomments=Q::G('commentids','G');
		
		if(empty($nGrouptopicid)){
			$this->E(Q::L('没有待操作的帖子','Controller'));
		}

		$arrGrouptopiccomments=Q::normalize($sGrouptopiccomments);
		if(empty($arrGrouptopiccomments)){
			$this->E(Q::L('没有待操作的回帖','Controller'));
		}

		$this->assign('sGrouptopics',$nGrouptopicid);
		$this->assign('nGroupid',$nGroupid);
		$this->assign('sGrouptopiccomments',implode(',',$arrGrouptopiccomments));
		$this->assign('sTitle',Q::L('你选择了 %d 篇帖子','Controller',null,count($arrGrouptopiccomments)));
		$this->display('grouptopicadmin+hidecommentdialog');
	}

}
