<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组个人空间回帖($$)*/

!defined('Q_PATH') && exit;

class Comment_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		
		$arrUserInfo=Model::F_('user','user_status=1 AND user_id=?',$nId)->setColumns('user_id,user_name')->getOne();
		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->assign('nId',$nId);

		// 读取回帖列表
		$arrWhere=array();
		$arrWhere['A.user_id']=$nId;

		if($GLOBALS['___login___']!==false && $GLOBALS['___login___']['user_id']==$nId){
			$sOrderextends='A.grouptopiccomment_status ASC,A.grouptopiccomment_ishide DESC,';
			$nYouself=1;
			$arrWhere['A.grouptopiccomment_status']=array('neq',CommonModel::STATUS_RECYLE);
		}else{
			$sOrderextends='';
			$nYouself=0;
			$arrWhere['A.grouptopiccomment_status']=1;
			$arrWhere['A.grouptopiccomment_ishide']='0';
		}

		$nTotalGrouptopiccommentnum=Model::F_('grouptopiccomment','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopiccommentnum,$GLOBALS['_cache_']['group_option']['group_space_listcommentnum'],'@group://space@?id='.$nId.'&type=comment&page={page}');
		$arrGrouptopiccomments=Model::F_('grouptopiccomment','@A')->where($arrWhere)
			->setColumns('A.grouptopiccomment_content,A.grouptopiccomment_id,A.grouptopiccomment_title,A.create_dateline,A.grouptopiccomment_status,A.grouptopiccomment_ishide,A.user_id,A.grouptopic_id')
			->join(Q::C('DB_PREFIX').'grouptopic AS B','B.grouptopic_title,B.grouptopic_color,B.grouptopic_username,B.user_id AS topic_user_id,B.create_dateline AS topic_create_dateline,B.grouptopic_isanonymous,B.group_id','A.grouptopic_id=B.grouptopic_id')
			->join(Q::C('DB_PREFIX').'group AS C','C.group_name,C.group_nikename','B.group_id=C.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS D','D.grouptopiccategory_name,D.grouptopiccategory_id','B.grouptopiccategory_id=D.grouptopiccategory_id')
			->order($sOrderextends."A.create_dateline DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('我的回帖','Controller')));
		
		$this->assign('arrGrouptopiccomments',$arrGrouptopiccomments);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nYouself',$nYouself);
		$this->display('space+comment');
	}

}
