<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件评论模型($$)*/

!defined('Q_PATH') && exit;

class AttachmentcommentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'attachmentcomment',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('attachmentcomment_ip','getIp','create','callback'),
			),
			'check'=>array(
				'attachmentcomment_name'=>array(
					array('require',Q::L('评论名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',25,Q::L('评论名字的最大字符数为25','__COMMON_LANG__@Common'))
				),
				'attachmentcomment_email'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论Email 最大字符数为300','__COMMON_LANG__@Common')),
					array('email',Q::L('评论的邮件必须为正确的Email 格式','__COMMON_LANG__@Common'))
				),
				'attachmentcomment_url'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论URL 最大字符数为300','__COMMON_LANG__@Common')),
					array('url',Q::L('评论的邮件必须为正确的URL 格式','__COMMON_LANG__@Common'))
				),
				'attachmentcomment_content'=>array(
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
		$this->attachmentcomment_name=C::text($this->attachmentcomment_name);
		$this->attachmentcomment_content=C::cleanJs($this->attachmentcomment_content);
		$this->attachmentcomment_qq=C::text($this->attachmentcomment_qq);
		$this->attachmentcomment_mobile=C::text($this->attachmentcomment_mobile);
	}

	static public function getParentCommentsPage($nFinecommentid,$nEveryCommentnum=1,$nAttachmentid=0){
		$arrWhere['attachmentcomment_status']=1;
		$arrWhere['attachment_id']=$nAttachmentid;

		// 查找当前评论的记录
		$nTheSearchKey='';
		$arrAttachmentcommentLists=self::F()->where($arrWhere)
			->setColumns('attachmentcomment_id')
			->order('attachmentcomment_id ASC')
			->getAll();
		foreach($arrAttachmentcommentLists as $nKey=>$oAttachmentcommentList){
			if($oAttachmentcommentList['attachmentcomment_id']==$nFinecommentid){
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
		$oTryAttachmentcomment=self::F('attachmentcomment_id=? AND attachmentcomment_status=1',$nCommentnumId)
			->setColumns('attachmentcomment_id,attachment_id')
			->getOne();
		if(empty($oTryAttachmentcomment['attachmentcomment_id'])){
			return false;
		}

		// 分析出评论所在的分页值
		$nPage=self::getParentCommentsPage($nCommentnumId,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],$oTryAttachmentcomment['attachment_id']);

		return Q::U('home://file@?id='.$oTryAttachmentcomment['attachment_id'].($nPage>1?'&page='.$nPage:'')).'#comment-'.$nCommentnumId;
	}

}
