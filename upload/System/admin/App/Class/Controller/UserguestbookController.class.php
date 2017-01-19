<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户留言控制器($$)*/

!defined('Q_PATH') && exit;

class UserguestbookController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.userguestbook_content']=array('like',"%".Q::G('userguestbook_content')."%");
		$arrMap['A.userguestbook_name']=array('like',"%".Q::G('userguestbook_name')."%");
		$arrMap['B.user_name']=array('like',"%".Q::G('user_name')."%");
		$arrMap['A.userguestbook_ip']=array('like',"%".Q::G('userguestbook_ip')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		// 附件检索
		$nTuid=intval(Q::G('tuid','G'));
		if($nTuid){
			$oTouser=UserModel::F('user_id=?',$nTuid)->getOne();
			if(!empty($oTouser['user_id'])){
				$arrMap['A.userguestbook_userid']=$nTuid;
				$this->assign('oTouser',$oTouser);
			}
		}

		// 用户检索
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}
	}
	
	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."user AS B','B.user_name','A.userguestbook_userid=B.user_id')";
	}

	public function add(){
		$this->E(Q::L('后台无法添加用户留言','Controller'));
	}

}
