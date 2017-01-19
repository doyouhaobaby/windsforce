<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化用户绑定控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入绑定数据模型 */
if(!Q::classExists('SociauserModel')){
	require_once(WINDSFORCE_PATH.'/System/extension/socialization/lib/mvc/SociauserModel.class.php');
}

class SociauserController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.sociauser_name']=array('like',"%".Q::G('sociauser_name')."%");
		$arrMap['A.sociauser_openid']=array('like',"%".Q::G('sociauser_openid')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function add(){
		$this->E(Q::L('后台无法直接添加绑定数据','Controller'));
	}

}
