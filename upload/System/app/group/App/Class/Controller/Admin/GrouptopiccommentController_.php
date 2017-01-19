<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组回帖控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopiccommentController extends AController{

	protected $_arrTopics=array();
	
	public function filter_(&$arrMap){
		$arrMap['A.grouptopiccomment_content']=array('like','%'.Q::G('grouptopiccomment_content').'%');
		$arrMap['A.grouptopiccomment_name']=array('like','%'.Q::G('grouptopiccomment_name').'%');
		$arrMap['B.grouptopic_title']=array('like','%'.Q::G('grouptopic_title').'%');
		$arrMap['C.group_nikename']=array('like','%'.Q::G('group_nikename').'%');

		$nGroupId=Q::G('group_id');
		if($nGroupId!==NULL && $nGroupId!=''){
			$arrMap['B.group_id']=$nGroupId;
		}

		// 帖子检索
		$nTid=intval(Q::G('tid','G'));
		if($nTid){
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nTid)->getOne();
			if(!empty($oGrouptopic['grouptopic_id'])){
				$arrMap['A.grouptopic_id']=$nTid;
				$this->assign('oGrouptopic',$oGrouptopic);
			}
		}
		
		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}
	
	public function index($sModel=null,$bDisplay=true){
		parent::index('grouptopiccomment',false);
		$this->display(Admin_Extend::template('group','grouptopiccomment/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."grouptopic AS B','B.grouptopic_title,B.group_id','A.grouptopic_id=B.grouptopic_id')".
			"->joinLeft('".Q::C('DB_PREFIX')."group AS C','C.group_nikename','B.group_id=C.group_id')";
	}
	
	public function add(){
		$this->E(Q::L('后台无法发布回帖','__APPGROUP_COMMON_LANG__@Controller'));
	}
	
	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('grouptopiccomment',$nId,false);
		$this->display(Admin_Extend::template('group','grouptopiccomment/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('grouptopiccomment',$nId);
	}

	public function bForeverdelete_deep_(){
		$arrGroups=array();

		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		// 读取所有待删除的回帖，提取相关主题
		foreach($arrIds as $nId){
			$oGrouptopiccomment=GrouptopiccommentModel::F('grouptopiccomment_id=?',$nId)->getOne();
			if(!empty($oGrouptopiccomment['grouptopiccomment_id'])){
				$arrTopics[]=$oGrouptopiccomment['grouptopic_id'];
			}
		}

		$arrTopics=array_unique($arrTopics);
		$this->_arrTopics=$arrTopics;
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('grouptopiccomment',$sId);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('grouptopiccomment',$sId,true);
	}

	protected function aForeverdelete_deep($sId){
		// 重新统计相关主题的回帖数量
		$arrTopics=$this->_arrTopics;
		foreach($arrTopics as $nTid){
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nTid)->getOne();
			if(!empty($oGrouptopic['grouptopic_id'])){
				// 更新主题回帖数量
				$nCommentnum=GrouptopiccommentModel::F('grouptopic_id=?',$nTid)->all()->getCounts();
				$oGrouptopic->grouptopic_comments=$nCommentnum;
				$oGrouptopic->save('update');
				if($oGrouptopic->isError()){
					$this->E($oGrouptopic->getErrorMessage());
				}
			}
		}
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::forbid('grouptopiccomment',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::resume('grouptopiccomment',$nId,true);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('grouptopiccomment',$sField);
	}

}
