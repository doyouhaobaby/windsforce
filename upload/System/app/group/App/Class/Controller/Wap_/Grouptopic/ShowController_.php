<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组Wap帖子阅读控制器($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	public function index(){
		if(!Core_Extend::checkRbac('group@grouptopic@view')){
			$this->_oParent->wap_mes(Q::L('你没有权限查看帖子','Controller'),'',0);
		}
		
		// 获取参数
		$nId=intval(Q::G('id','G'));

		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nId)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->join(Q::C('DB_PREFIX').'user AS E','E.user_nikename,E.user_name','A.user_id=E.user_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->_oParent->wap_mes(Q::L('你访问的主题不存在或已删除','Controller'),'',0);
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGrouptopic);
		}catch(Exception $e){
			$this->_oParent->wap_mes($e->getMessage(),'',0);
		}

		// 更新点击量
		Model::M_('grouptopic')->updateWhere(array('grouptopic_views'=>$arrGrouptopic['grouptopic_views']+1),'grouptopic_id=?',$nId);
		$arrGrouptopic['grouptopic_views']++;

		if($arrGrouptopic['grouptopic_thumb']>0){
			$arrGrouptopic['grouptopic_content']='<div class="grouptopicthumb"><div class="grouptopicthumb_title">'.Q::L('主题缩略图','Controller').'</div><p>[attachment]'.$arrGrouptopic['grouptopic_thumb'].'[/attachment]</p></div>'.$arrGrouptopic['grouptopic_content'];
		}

		Core_Extend::getSeo($this,array(
			'title'=>$arrGrouptopic['grouptopic_title'].' - '.$arrGrouptopic['group_nikename'],
			'keywords'=>$arrGrouptopic['group_nikename'].','.$arrGrouptopic['grouptopic_title']));
		
		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('arrGroup',$arrGrouptopic);
		$this->display('wap+show');
	}

}
