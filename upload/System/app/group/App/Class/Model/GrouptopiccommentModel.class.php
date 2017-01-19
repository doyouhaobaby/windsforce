<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子评论模型($$)*/

!defined('Q_PATH') && exit;

class GrouptopiccommentModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'grouptopiccomment',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('grouptopiccomment_ip','getIp','create','callback'),
			),
			'check'=>array(
				'grouptopiccomment_title'=>array(
					array('max_length',300,Q::L('回帖标题不能超过300个字符','__APPGROUP_COMMON_LANG__@Model')),
				),
				'grouptopiccomment_content'=>array(
					array('require',Q::L('帖子评论内容不能为空','__APPGROUP_COMMON_LANG__@Model')),
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

	public function beforeSave_(){
		$this->grouptopiccomment_title=C::text($this->grouptopiccomment_title);
		$this->grouptopiccomment_content=Core_Extend::replaceAttachment(C::cleanJs($this->grouptopiccomment_content));
		$this->grouptopiccomment_name=C::text($this->grouptopiccomment_name);
	}

	static public function getGrouptopiccommentById($nCommentId,$sField='grouptopiccomment_name',$bAll=false){
		$oGrouptopiccomment=GrouptopiccommentModel::F('grouptopiccomment_id=?',$nCommentId)->query();
		if(empty($oGrouptopiccomment['grouptopiccomment_id'])){
			return '';
		}

		if($bAll===true){
			return $oGrouptopiccomment;
		}
		
		return $oGrouptopiccomment[$sField];
	}

	static public function getParentCommentsPage($nFinecommentid,$nGrouptopiccommentParentid=0,$nEveryCommentnum=1,$nGrouptopicid=0,$sOrdertype='ASC',$nAutopass=0){
		$arrWhere['grouptopiccomment_status']=1;
		$arrWhere['grouptopiccomment_parentid']=$nGrouptopiccommentParentid;
		$arrWhere['grouptopic_id']=$nGrouptopicid;

		if($nAutopass==0){
			$arrWhere['grouptopiccomment_status']=array('neq',CommonModel::STATUS_RECYLE);
		}
		
		// 查找当前评论的记录
		$nTheSearchKey='';
		$arrGrouptopiccommentLists=self::F()->where($arrWhere)
			->order('grouptopiccomment_status ASC,grouptopiccomment_stickreply DESC,grouptopiccomment_id '.$sOrdertype)
			->setColumns('grouptopiccomment_id')
			->getAll();
		foreach($arrGrouptopiccommentLists as $nKey=>$oGrouptopiccommentList){
			if($oGrouptopiccommentList['grouptopiccomment_id']==$nFinecommentid){
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
		$arrTryGrouptopiccomment=Model::F_('grouptopiccomment','@A','A.grouptopiccomment_id=?',$nCommentnumId)
			->setColumns('A.grouptopiccomment_id,A.grouptopic_id')
			->join(Q::C('DB_PREFIX').'grouptopic AS B','B.grouptopic_ordertype','A.grouptopic_id=B.grouptopic_id')
			->join(Q::C('DB_PREFIX').'group AS C','C.*','B.group_id=C.group_id')
			->getOne();
		if(empty($arrTryGrouptopiccomment['grouptopiccomment_id'])){
			return false;
		}

		$sOrdertype=$arrTryGrouptopiccomment['grouptopic_ordertype']?'DESC':'ASC';
		if(!Groupadmin_Extend::checkCommentadminRbac($arrTryGrouptopiccomment,array('group@grouptopicadmin@auditcomment'))){
			$nAutopass=1;
		}else{
			$nAutopass=0;
		}

		// 分析出父级评论所在的分页值
		$nPage=self::getParentCommentsPage($nCommentnumId,0,$GLOBALS['_cache_']['group_option']['grouptopic_listcommentnum'],$arrTryGrouptopiccomment['grouptopic_id'],$sOrdertype,$nAutopass);

		return Q::U('group://topic@?id='.$arrTryGrouptopiccomment['grouptopic_id'].($nPage>1?'&page='.$nPage:'')).'#grouptopiccomment-'.$nCommentnumId;
	}

}
