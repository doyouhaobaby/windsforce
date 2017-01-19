<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除我喜欢的话题($$)*/

!defined('Q_PATH') && exit;

class Lovetopicdelete_C_Controller extends InitController{

	public function index(){
		$arrTopicid=Q::G('key');

		if($arrTopicid){
			foreach($arrTopicid as $nTopicid){
				Model::M_('grouptopiclove')->deleteWhere(array('grouptopic_id'=>$nTopicid,'user_id'=>$GLOBALS['___login___']['user_id']));

				// 整理帖子喜欢数
				$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nTopicid)->getOne();
				if(!empty($oGrouptopic['grouptopic_id'])){
					$oGrouptopic->rebuildGrouptopicloves();
				}
			}
		}else{
			$this->E(Q::L('你没有选择待删除的帖子','Controller'));
		}

		$this->S(Q::L('删除喜欢帖子成功','Controller'));
	}

}
