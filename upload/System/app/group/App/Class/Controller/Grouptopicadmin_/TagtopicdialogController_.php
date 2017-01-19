<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子标签对话框控制器($$)*/

!defined('Q_PATH') && exit;

class Tagtopicdialog_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('groupid','G'));
		$nGrouptopicid=intval(Q::G('grouptopicid','G'));
		
		if(empty($nGrouptopicid)){
			$this->E(Q::L('没有待操作的帖子','Controller'));
		}

		// 获取帖子标签
		$sTag='';
		$arrTags=Model::F_('grouptopictagindex','@A','A.grouptopic_id=?',$nGrouptopicid)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'grouptopictag AS B','B.grouptopictag_name,B.grouptopictag_count','A.grouptopictag_id=B.grouptopictag_id')
			->order('B.create_dateline DESC')
			->getAll();
		if(!empty($arrTags)){
			foreach($arrTags as $arrTemp){
				$sTag.=','.$arrTemp['grouptopictag_name'];
			}
			$sTag=trim($sTag,',');
		}

		$this->assign('nGroupid',$nGroupid);
		$this->assign('sGrouptopics',$nGrouptopicid);
		$this->assign('sTag',$sTag);
		$this->assign('sTitle',Q::L('你选择了 %d 篇帖子','Controller',null,1));
		$this->display('grouptopicadmin+tagtopicdialog');
	}

}
