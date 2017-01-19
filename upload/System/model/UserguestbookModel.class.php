<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   留言板评论模型($$)*/

!defined('Q_PATH') && exit;

class UserguestbookModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'userguestbook',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('userguestbook_ip','getIp','create','callback'),
			),
			'check'=>array(
				'userguestbook_name'=>array(
					array('require',Q::L('评论名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',25,Q::L('评论名字的最大字符数为25','__COMMON_LANG__@Common'))
				),
				'userguestbook_email'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论Email 最大字符数为300','__COMMON_LANG__@Common')),
					array('email',Q::L('评论的邮件必须为正确的Email 格式','__COMMON_LANG__@Common'))
				),
				'userguestbook_url'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论URL 最大字符数为300','__COMMON_LANG__@Common')),
					array('url',Q::L('评论的邮件必须为正确的URL 格式','__COMMON_LANG__@Common'))
				),
				'userguestbook_content'=>array(
					array('require',Q::L('评论的内容不能为空','__COMMON_LANG__@Common'))
				),
			),
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	protected function beforeSave_(){
		$this->userguestbook_name=C::text($this->userguestbook_name);
		$this->userguestbook_content=C::cleanJs($this->userguestbook_content);
		$this->userguestbook_qq=C::text($this->userguestbook_qq);
		$this->userguestbook_mobile=C::text($this->userguestbook_mobile);
	}

	static public function getParentCommentsPage($nFinecommentid,$nEveryCommentnum=1,$nUserguestbookid=0){
		$arrWhere['userguestbook_status']=1;
		$arrWhere['userguestbook_userid']=$nUserguestbookid;
		
		// 查找当前评论的记录
		$nTheSearchKey='';
		$arrUserguestbookLists=self::F()->where($arrWhere)
			->order('userguestbook_id DESC')
			->setColumns('userguestbook_id')
			->getAll();
		foreach($arrUserguestbookLists as $nKey=>$arrUserguestbookList){
			if($arrUserguestbookList['userguestbook_id']==$nFinecommentid){
				$nTheSearchKey=$nKey+1;
			}
		}

		$nPage=ceil($nTheSearchKey/$nEveryCommentnum);
		if($nPage<1){
			$nPage=1;
		}

		return $nPage;
	}

	static public function getCommenturlByid($nCommentnumId){
		// 判断评论是否存在
		$oTryUserguestbook=self::F('userguestbook_id=? AND userguestbook_status=1',$nCommentnumId)
			->setColumns('userguestbook_id,userguestbook_userid')
			->getOne();
		if(empty($oTryUserguestbook['userguestbook_id'])){
			return false;
		}

		// 分析出评论所在的分页值
		$nPage=self::getParentCommentsPage($nCommentnumId,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],$oTryUserguestbook['userguestbook_id']);

		return Q::U('home://space@?id='.$oTryUserguestbook['userguestbook_userid'].'&type=guestbook'.($nPage>1?'&page='.$nPage:'')).'#comment-'.$nCommentnumId;
	}

}
