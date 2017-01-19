<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加帖子回复控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入home应用配置值 */
if(!Q::classExists('HomeoptionModel')){
	require_once(WINDSFORCE_PATH.'/System/app/home/App/Class/Model/HomeoptionModel.class.php');
}

/** 载入home应用配置信息 */
if(!isset($GLOBALS['_cache_']['home_option'])){
	Core_Extend::loadCache('home_option');
}

class Reply_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
		
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定主题的ID','Controller'));
		}

		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nId)
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->E(Q::L('你访问的主题不存在或已删除','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGrouptopic,true);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGrouptopic['group_id']);

		Core_Extend::getSeo($this,array('title'=>Q::L('帖子回复','Controller').' - '.$arrGrouptopic['group_nikename'].Q::L('小组','Controller')));

		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('arrGroup',$arrGrouptopic);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->display('grouptopic+reply');
	}

}
