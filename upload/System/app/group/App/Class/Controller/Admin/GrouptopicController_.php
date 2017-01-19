<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopicController extends AController{

	protected $_arrGroups=array();
	
	public function filter_(&$arrMap){
		$arrMap['A.grouptopic_title']=array('like','%'.Q::G('grouptopic_title').'%');
		$arrMap['A.grouptopic_comments']=array('egt',intval(Q::G('grouptopic_comments')));
		$arrMap['A.grouptopic_views']=array('egt',intval(Q::G('grouptopic_views')));
		$arrMap['C.group_nikename']=array('like','%'.Q::G('group_nikename').'%');

		// 小组检索
		$nGid=intval(Q::G('gid','G'));
		if($nGid){
			$oGroup=GroupModel::F('group_id=?',$nGid)->getOne();
			if(!empty($oGroup['group_id'])){
				$arrMap['A.group_id']=$nGid;
				$this->assign('oGroup',$oGroup);
			}
		}

		// 分类检索
		if(isset($_GET['cid']) && $_GET['cid']>0){
			$oGrouptopiccategory=GrouptopiccategoryModel::F('grouptopiccategory_id=?',$_GET['cid'])->getOne();
			if(!empty($oGrouptopiccategory['grouptopiccategory_id'])){
				$arrMap['A.grouptopiccategory_id']=$_GET['cid'];
				$this->assign('oGrouptopiccategory',$oGrouptopiccategory);
			}
		}

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}
	
	public function index($sModel=null,$bDisplay=true){
		parent::index('grouptopic',false);
		$this->display(Admin_Extend::template('group','grouptopic/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')".
			"->joinLeft('".Q::C('DB_PREFIX')."group AS C','C.group_nikename','A.group_id=C.group_id')";
	}
	
	public function add(){
		$this->E(Q::L('后台无法发布帖子','__APPGROUP_COMMON_LANG__@Controller').'<br/><a href="'.Core_Extend::windsforceOuter('app=group&c=grouptopic&a=add').'" target="_blank">'.Q::L('前往发布','__APPGROUP_COMMON_LANG__@Controller').'</a>');
	}
	
	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('grouptopic',$nId,false);
		
		// 帖子内容
		$sContent=GrouptopiccontentModel::F('grouptopic_id=?',$nId)->getColumn('grouptopic_content');
		$this->assign('sContent',$sContent);
		$this->display(Admin_Extend::template('group','grouptopic/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		
		$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nId)->getOne();
		$oGrouptopic->updateData();
		if($oGrouptopic->isError()){
			$this->E($oGrouptopic->getErrorMessage());
		}

		$this->S(Q::L('数据更新成功','Controller'));
	}

	public function bForeverdelete_deep_(){
		$arrGroups=array();
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		// 读取所有待删除的帖子，提取相关小组
		foreach($arrIds as $nId){
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nId)->getOne();
			if(!empty($oGrouptopic['grouptopic_id'])){
				$arrGroups[]=$oGrouptopic['group_id'];
			}
		}

		$arrGroups=array_unique($arrGroups);
		$this->_arrGroups=$arrGroups;
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('group',$sId);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('grouptopic',$sId,true);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 整理帖子相关信息
		foreach($arrIds as $nId){
			$oGrouptopiccommentMeta=GrouptopiccommentModel::M();
			$oGrouptopiccommentMeta->deleteWhere(array('grouptopic_id'=>$nId));
			if($oGrouptopiccommentMeta->isError()){
				$this->E($oGrouptopiccommentMeta->getErrorMessage());
			}
		}
		
		// 重新统计相关小组的帖子数量
		$arrGroups=$this->_arrGroups;
		foreach($arrGroups as $nGid){
			$oGroup=GroupModel::F('group_id=?',$nGid)->getOne();
			if(!empty($oGroup['group_id'])){
				// 更新小组帖子数量
				$nTopicnum=GrouptopicModel::F('group_id=?',$nGid)->getCounts();
				$oGroup->group_topicnum=$nTopicnum;
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
			}
		}
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::forbid('grouptopic',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::resume('grouptopic',$nId,true);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('grouptopic',$sField);
	}

}
