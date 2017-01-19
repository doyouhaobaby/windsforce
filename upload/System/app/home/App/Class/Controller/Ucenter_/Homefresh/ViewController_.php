<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   查看新鲜事($$)*/

!defined('Q_PATH') && exit;

class View_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定要阅读的新鲜事','Controller'));
		}

		$arrHomefresh=Model::F_('homefresh','@A','A.homefresh_id=? AND A.homefresh_status=1',$nId)
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_type,A.homefresh_viewnum,A.homefresh_thumb,A.homefreshcategory_id')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name,B.user_sign','A.user_id=B.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.usercount_extendcredit1','A.user_id=C.user_id')
			->joinLeft(Q::C('DB_PREFIX').'homefreshcategory AS D','D.homefreshcategory_name','A.homefreshcategory_id=D.homefreshcategory_id')
			->getOne();

		if(empty($arrHomefresh['homefresh_id'])){
			$this->E(Q::L('新鲜事不存在或者被屏蔽了','Controller'));
		}	

		// 判断邮件等外部地址过来的查找评论地址
		$nIsolationCommentid=intval(Q::G('isolation_commentid','G'));
		if($nIsolationCommentid){
			$result=HomefreshcommentModel::getCommenturlByid($nIsolationCommentid);
			if($result===false){
				$this->E(Q::L('该条评论已被删除、屏蔽或者尚未通过审核','Controller'));
			}

			C::urlGo($result);
			exit();
		}

		// 更新点击量
		Model::M_('homefresh')->updateWhere(array('homefresh_viewnum'=>$arrHomefresh['homefresh_viewnum']+1),'homefresh_id=?',$nId);
		$arrHomefresh['homefresh_viewnum']++;

		$this->_sHomefreshtitle=$arrHomefresh['homefresh_title'];
		$sHomefreshAttribute=$this->parse_attribute_($arrHomefresh['homefresh_attribute'],$arrHomefresh['homefresh_type'],$arrHomefresh['homefresh_id']);

		// 读取评论列表
		$arrWhere=array();
		$arrWhere['A.homefreshcomment_parentid']=0;
		$arrWhere['A.homefreshcomment_status']=1;
		$arrWhere['A.homefresh_id']=$nId;

		// 评论
		$nTotalRecord=Model::F_('homefreshcomment','@A')->where($arrWhere)->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],'@home://fresh@?id='.$nId);
		$arrHomefreshcommentLists=Model::F_('homefreshcomment','@A')->where($arrWhere)
			->setColumns('A.homefreshcomment_id,A.create_dateline,A.user_id,A.homefreshcomment_name,A.homefreshcomment_content,A.homefresh_id')
			->order('A.`homefreshcomment_id` ASC')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->limit($oPage->S(),$oPage->N())
			->asArray()
			->getAll();

		// 用户和积分
		$this->assign('arrRatinginfo',Core_Extend::getUserrating($arrHomefresh['usercount_extendcredit1'],false));

		Core_Extend::getSeo($this,array(
			'title'=>$arrHomefresh['homefresh_title'],
			'keywords'=>$arrHomefresh['homefresh_title']),true,true);
		
		$this->assign('arrHomefresh',$arrHomefresh);
		$this->assign('nTotalHomefreshcomment',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrHomefreshcommentLists',$arrHomefreshcommentLists);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->assign('sHomefreshAttribute',$sHomefreshAttribute);
		$this->display('homefresh+view');
	}

	protected function parse_attribute_($sAttribute,$nType,$nHomefreshid){
		return Home_Extend::parse($sAttribute,$nType,$nHomefreshid);
	}

}
