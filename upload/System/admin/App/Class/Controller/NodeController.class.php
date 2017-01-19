<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   节点控制器($$)*/

!defined('Q_PATH') && exit;

class NodeController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.node_name']=array('like',"%".Q::G('node_name')."%");
		$arrMap['A.node_title']=array('like',"%".Q::G('node_title')."%");

		$nNodegroupId=Q::G('nodegroup_id');
		$sSearch=Q::G('search');
		$nNodeParentid=Q::G('node_parentid');

		if($nNodegroupId!==null && $nNodegroupId!=''){
			$arrMap['A.nodegroup_id']=$nNodegroupId;
			$this->assign('sNodeName',Q::L('分组','Controller'));
		}elseif(empty($sSearch) && !isset($arrMap['node_parentid'])){
			$arrMap['A.node_parentid']=0;
		}

		if(!empty($nNodeParentid)){
			$arrMap['A.node_parentid']=$nNodeParentid;
		}
		$this->assign('nNodegroupId',$nNodegroupId);

		Q::cookie('current_node_id',$nNodeParentid);

		if(isset($arrMap['node_parentid'])){
			if(($oNode=NodeModel::F()->getBynode_id($arrMap['node_parentid'])) instanceof Model){
				$this->assign('nNodeLevel',$oNode->node_level+1);
				$this->assign('sNodeName',$oNode->node_name);
			}else{
				$this->assign('nNodeLevel',1);
			}
		}else{
			$this->assign('nNodeLevel',1);
		}
	}
	
	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."nodegroup AS B','B.*','A.nodegroup_id=B.nodegroup_id')";
	}

	public function AInsertObject_($oModel){
		$oModel->node_level=$oModel->getLevel();
	}

	public function AUpdateObject_($oModel){
		$oModel->node_level=$oModel->getLevel();
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();
		
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_node($nId)){
				$this->E(Q::L('系统节点无法删除','Controller'));
			}

			$nNodes=NodeModel::F('node_parentid=?',$nId)->all()->getCounts();
			$oNode=NodeModel::F('node_id=?',$nId)->query();
			if($nNodes>0){
				$this->E(Q::L('节点%s存在子分类，你无法删除','Controller',null,$oNode->node_name));
			}
		}
	}

	public function get_access(){
		$sAccess=Q::G('access','P');

		$nNodeLevel=1;
		if($sAccess=='app'){
			$arrAccessList=NodeModel::F()->where(array('node_level'=>1,'node_parentid'=>0))->asArray()->all()->query();
			$nNodeLevel=2;
		}elseif($sAccess=='module'){
			$arrAccessList=NodeModel::F()->where(array('node_level'=>2))->asArray()->all()->query();
			$nNodeLevel=3;
		}

		$this->assign('arrAccessList',$arrAccessList);
		$this->assign('nNodeLevel',$nNodeLevel);

		$this->display();
	}

	public function getNodegroup(){
		$arrNodegroup=array_merge(array(array('nodegroup_id'=>0,'nodegroup_title'=>Q::L('未分组','Controller'))),
			NodegroupModel::F()->setColumns('nodegroup_id,nodegroup_title')->asArray()->all()->query()
		);
		$this->assign('arrNodegroup',$arrNodegroup);
	}

	public function bIndex_(){
		$this->getNodegroup();
	}

	public function bAdd_(){
		$this->check_appdevelop();
		$this->getNodegroup();
	}

	public function bEdit_(){
		$this->check_appdevelop();
		
		$nId=intval(Q::G('id','G'));
		if($this->is_system_node($nId)){
			$this->E(Q::L('系统节点无法编辑','Controller'));
		}

		$this->getNodegroup();
	}

	public function bForbid_(){
		$this->check_appdevelop();
		
		$nId=intval(Q::G('id','G'));
		if($this->is_system_node($nId)){
			$this->E(Q::L('系统节点无法禁用','Controller'));
		}
	}

	public function clear_menu_cache(){}

	public function aInsert($nId=null){
		$this->clear_menu_cache();
	}

	public function aUpdate($nId=null){
		$this->clear_menu_cache();
	}

	public function aForeverdelete($sId){
		$this->clear_menu_cache();
	}

	public function afterInputChangeAjax($sName=null){
		$this->clear_menu_cache();
	}

	public function sort(){
		$this->check_appdevelop();

		$nSortId=Q::G('sort_id');
		if(!empty($nSortId)){
			$arrMap['node_status']=1;
			$arrMap['node_id']=array('in',$nSortId);
			$arrSortList=NodeModel::F()->order('node_sort ASC')->all()->where($arrMap)->query();
		}else{
			$nNodeParentid=Q::G('node_parentid');
			if(!empty($nNodeParentid)){
				$nPid=&$nNodeParentid;
			}else{
				$nPid=Q::cookie('current_node_id',$nNodeParentid);;
			}

			if($nPid===null){
				$nPid=0;
			}

			$arrNode=NodeModel::F()->getBynode_id($nPid)->toArray();
			if(isset($arrNode['node_id'])){
				$nLevel=$arrNode['node_level']+1;
			}else{
				$nLevel=1;
			}
			$this->assign('nLevel',$nLevel);

			$arrSortList=NodeModel::F()->where('node_status=1 and node_parentid=? and node_level=?',$nPid,$nLevel)->order('node_sort ASC')->all()->query();
		}

		$this->assign("arrSortList",$arrSortList);
		$this->display();
	}

	public function get_nodegroup($nGroupId){
		return NodegroupModel::F('nodegroup_id=?',$nGroupId)->getOne();
	}

	public function change_nodegroup(){
		$this->check_appdevelop();
		
		$sId=trim(Q::G('id','G'));
		$nNodegroupId=intval(Q::G('nodegroup_id','G'));
		if(!empty($sId)){
			if($nNodegroupId){
				// 判断节点分组是否存在
				$oNodegroup=NodegroupModel::F('nodegroup_id=?',$nNodegroupId)->getOne();
				if(empty($oNodegroup['nodegroup_id'])){
					$this->E(Q::L('你要移动的节点分组不存在','Controller'));
				}
			}
			
			$arrIds=explode(',', $sId);
			foreach($arrIds as $nId){
				if($this->is_system_node($nId)){
					$this->E(Q::L('系统节点无法移动','Controller'));
				}
				
				$oNode=NodeModel::F('node_id=?',$nId)->getOne();
				$oNode->nodegroup_id=$nNodegroupId;
				$oNode->save('update');
				if($oNode->isError()){
					$this->E($oNode->getErrorMessage());
				}
			}

			$this->S(Q::L('移动节点分组成功','Controller'));
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function check_nodename(){
		$sNodeName=trim(Q::G('node_name'));
		$nId=intval(Q::G('id'));

		if(!$sNodeName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['node_name']=$sNodeName;
		if($nId){
			$arrWhere['node_id']=array('neq',$nId);
		}

		$oNode=NodeModel::F()->where($arrWhere)->setColumns('node_id')->getOne();
		if(empty($oNode['node_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function is_system_node($nId){
		$nId=intval($nId);

		$oNode=NodeModel::F('node_id=?',$nId)->setColumns('node_id,node_issystem')->getOne();
		if(empty($oNode['node_id'])){
			return false;
		}

		if($oNode['node_issystem']==1){
			return true;
		}

		return false;
	}

}
