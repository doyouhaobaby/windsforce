<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   节点模型($$)*/

!defined('Q_PATH') && exit;

class NodeModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'node',
			'check'=>array(
				'node_name'=>array(
					array('require',Q::L('节点名不能为空','__COMMON_LANG__@Common')),
					array('max_length',150,Q::L('节点名最大长度为150个字符','__COMMON_LANG__@Common')),
					array('nodeName',Q::L('节点名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
				),
				'node_title'=>array(
					array('require',Q::L('显示名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('显示名最大长度为50个字符','__COMMON_LANG__@Common')),
				),
				'node_parentid'=>array(
					array('nodeParentId',Q::L('节点不能为自己','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
				),
				'node_sort'=>array(
					array('number',Q::L('序号只能是数字','__COMMON_LANG__@Common'),'condition'=>'notempty','extend'=>'regex'),
				)
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

	public function nodeParentId(){
		$nNodeId=Q::G('id');
		$nNodeParentid=Q::G('node_parentid');
		if(($nNodeId==$nNodeParentid)
				and !empty($nNodeId)
				and !empty($nNodeParentid)){
			return false;
		}

		return true;
	}

	public function nodeName(){
		return self::uniqueField_('node_name','node_id','id');
	}

	public function getLevel(){
		$sNodeaccess=trim(Q::G('node_access','P'));

		if($sNodeaccess=='module'){
			return 3;
		}elseif($sNodeaccess=='app'){
			return 2;
		}else{
			return 1;
		}
	}

	protected function beforeSave_(){
		$this->node_name=strip_tags($this->node_name);
		$this->node_title=C::text($this->node_title);
		$this->node_remark=C::text($this->node_remark);

		if($this->node_sort<0){
			$this->node_sort=0;
		}
		if($this->node_sort>999){
			$this->node_sort=999;
		}
	}

}
