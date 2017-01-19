<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑回复对话框控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入home应用配置值 */
if(!Q::classExists('HomeoptionModel')){
	require_once(WINDSFORCE_PATH.'/System/app/home/App/Class/Model/HomeoptionModel.class.php');
}

/** 载入home应用配置信息 */
if(!isset($GLOBALS['_cache_']['home_option'])){
	Core_Extend::loadCache('home_option');
}

class Editcommenttopicdialog_C_Controller extends InitController{

	public function index(){
		$nGrouptopiccommentid=intval(Q::G('cid','G'));
		if(!$nGrouptopiccommentid){
			$this->E(Q::L('你没有指定编辑的回帖的ID','Controller'));
		}

		$arrGrouptopiccomment=Model::F_('grouptopiccomment','grouptopiccomment_id=?',$nGrouptopiccommentid)->getOne();
		if(empty($arrGrouptopiccomment['grouptopiccomment_id'])){
			$this->E(Q::L('你要编辑的回帖不存在','Controller'));
		}

		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$arrGrouptopiccomment['grouptopic_id'])
			->joinLeft(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->E(Q::L('你要编辑的回帖的主题不存在','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGrouptopic,true);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$this->assign('arrEditGrouptopiccomment',$arrGrouptopiccomment);
		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->display('grouptopic+editcommenttopicdialog');
	}

}
