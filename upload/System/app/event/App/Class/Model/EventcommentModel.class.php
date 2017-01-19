<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动评论模型($$)*/

!defined('Q_PATH') && exit;

class EventcommentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'eventcomment',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('eventcomment_ip','getIp','create','callback'),
			),
			'check'=>array(
				'eventcomment_name'=>array(
					array('require',Q::L('评论名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',25,Q::L('评论名字的最大字符数为25','__COMMON_LANG__@Common'))
				),
				'eventcomment_email'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论Email 最大字符数为300','__COMMON_LANG__@Common')),
					array('email',Q::L('评论的邮件必须为正确的Email 格式','__COMMON_LANG__@Common'))
				),
				'eventcomment_url'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论URL 最大字符数为300','__COMMON_LANG__@Common')),
					array('url',Q::L('评论的邮件必须为正确的URL 格式','__COMMON_LANG__@Common'))
				),
				'eventcomment_content'=>array(
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
		$this->eventcomment_name=C::text($this->eventcomment_name);
		$this->eventcomment_content=C::cleanJs($this->eventcomment_content);
		$this->eventcomment_qq=C::text($this->eventcomment_qq);
		$this->eventcomment_mobile=C::text($this->eventcomment_mobile);
	}

	static public function getParentCommentsPage($nFinecommentid,$nEveryCommentnum=1,$nEventcommentid=0){
		$arrWhere['eventcomment_status']=1;
		$arrWhere['event_id']=$nEventcommentid;
		
		// 查找当前评论的记录
		$nTheSearchKey='';
		$arrEventcommentLists=self::F()->where($arrWhere)
			->order('eventcomment_id ASC')
			->setColumns('eventcomment_id')
			->getAll();
		foreach($arrEventcommentLists as $nKey=>$oEventcommentList){
			if($oEventcommentList['eventcomment_id']==$nFinecommentid){
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
		$oTryEventcomment=self::F('eventcomment_id=? AND eventcomment_status=1',$nCommentnumId)->setColumns('eventcomment_id,event_id')->getOne();
		if(empty($oTryEventcomment['eventcomment_id'])){
			return false;
		}

		// 分析出评论所在的分页值
		$nPage=self::getParentCommentsPage($nCommentnumId,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],$oTryEventcomment['event_id']);

		return Q::U('event://e@?id='.$oTryEventcomment['event_id'].($nPage>1?'&page='.$nPage:'')).'#comment-'.$nCommentnumId;
	}

}
