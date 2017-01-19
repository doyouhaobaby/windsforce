<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事评论模型($$)*/

!defined('Q_PATH') && exit;

class HomefreshcommentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homefreshcomment',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('homefreshcomment_ip','getIp','create','callback'),
			),
			'check'=>array(
				'homefreshcomment_name'=>array(
					array('require',Q::L('评论名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',25,Q::L('评论名字的最大字符数为25','__COMMON_LANG__@Common'))
				),
				'homefreshcomment_email'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论Email 最大字符数为300','__COMMON_LANG__@Common')),
					array('email',Q::L('评论的邮件必须为正确的Email 格式','__COMMON_LANG__@Common'))
				),
				'homefreshcomment_url'=>array(
					array('empty'),
					array('max_length',300,Q::L('评论URL 最大字符数为300','__COMMON_LANG__@Common')),
					array('url',Q::L('评论的邮件必须为正确的URL 格式','__COMMON_LANG__@Common'))
				),
				'homefreshcomment_content'=>array(
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
		$this->homefreshcomment_name=C::text($this->homefreshcomment_name);
		$this->homefreshcomment_content=C::cleanJs($this->homefreshcomment_content);
		$this->homefreshcomment_qq=C::text($this->homefreshcomment_qq);
		$this->homefreshcomment_mobile=C::text($this->homefreshcomment_mobile);
	}

	static public function getParentCommentsPage($nFinecommentid,$nHomefreshcommentParentid=0,$nEveryCommentnum=1,$nHomefreshid=0){
		$arrWhere['homefreshcomment_status']=1;
		$arrWhere['homefreshcomment_parentid']=$nHomefreshcommentParentid;
		$arrWhere['homefresh_id']=$nHomefreshid;
		
		// 查找当前评论的记录
		$nTheSearchKey='';
		$arrHomefreshcommentLists=self::F()->where($arrWhere)->setColumns('homefreshcomment_id')->order('create_dateline ASC')->getAll();
		foreach($arrHomefreshcommentLists as $nKey=>$oHomefreshcommentList){
			if($oHomefreshcommentList['homefreshcomment_id']==$nFinecommentid){
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
		$oTryHomefreshcomment=self::F('@A','homefreshcomment_id=? AND homefreshcomment_status=1',$nCommentnumId)
			->setColumns('A.homefreshcomment_id,A.homefreshcomment_parentid,A.homefresh_id')
			->asArray()
			->getOne();
		if(empty($oTryHomefreshcomment['homefreshcomment_id'])){
			return false;
		}

		// 分析出父级评论所在的分页值
		$nPage=self::getParentCommentsPage($oTryHomefreshcomment['homefreshcomment_parentid']==0?$nCommentnumId:$oTryHomefreshcomment['homefreshcomment_parentid'],0,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],$oTryHomefreshcomment['homefresh_id']);

		// 分析出子评论所在分页值
		if($oTryHomefreshcomment['homefreshcomment_parentid']>0){
			$nCommentPage=self::getParentCommentsPage($nCommentnumId,$oTryHomefreshcomment['homefreshcomment_parentid'],$GLOBALS['_cache_']['home_option']['homefreshchildcomment_list_num'],$oTryHomefreshcomment['homefresh_id']);
		}else{
			$nCommentPage=1;
		}

		return Q::U('home://fresh@?id='.$oTryHomefreshcomment['homefresh_id'].($nPage>1?'&page='.$nPage:'').($nCommentPage>1?'&commentpage_'.$oTryHomefreshcomment['homefreshcomment_parentid'].'='.$nCommentPage:'')).'#comment-'.$nCommentnumId;
	}

}
