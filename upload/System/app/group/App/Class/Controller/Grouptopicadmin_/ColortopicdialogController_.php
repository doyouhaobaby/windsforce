<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子高亮设置对话框控制器($$)*/

!defined('Q_PATH') && exit;

class Colortopicdialog_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('groupid','G'));
		$sGrouptopicid=trim(Q::G('grouptopicid','G'));
		$arrGrouptopicid=Q::normalize($sGrouptopicid);
		$sGrouptopics=implode(',',$arrGrouptopicid);
		
		if(empty($sGrouptopics)){
			$this->E(Q::L('没有待操作的帖子','Controller'));
		}
	
		if(count($arrGrouptopicid)==1){
			// 读取帖子颜色
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$arrGrouptopicid[0])->getOne();
			if(empty($oGrouptopic['grouptopic_id'])){
				$this->E(Q::L('帖子不存在','Controller'));
			}

			$arrColor=array('',array(1=>'0',2=>'0',3=>'0'),'',);
			if($oGrouptopic->grouptopic_color){
				$arrColorTemp=@unserialize($oGrouptopic->grouptopic_color);
				if($arrColorTemp){
					$arrColor=$arrColorTemp;
				}
			}
			$this->assign('arrColor',$arrColor);
		}
		
		$this->assign('nGroupid',$nGroupid);
		$this->assign('sGrouptopics',$sGrouptopics);
		$this->assign('sTitle',Q::L('你选择了 %d 篇帖子','Controller',null,count($arrGrouptopicid)));
		$this->display('grouptopicadmin+colortopicdialog');
	}

}
