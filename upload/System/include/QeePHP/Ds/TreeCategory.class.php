<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   无限分类树($$)*/

!defined('Q_PATH') && exit;

class TreeCategory{

	public $_arrData=array();
	public $_arrCate=array();
	protected $_treeLiCallback=null;

	public function __construct($arrNodes=array()){
		if(!empty($arrNodes)){
			if(C::oneImensionArray($arrNodes)){
				Q::E('Value can not be one-dimensional array');
			}
			
			foreach($arrNodes as $arrNode){
				$this->setNode($arrNode[0],$arrNode[1],$arrNode[2]);
			}
		}
	}

	public function setNode ($nId,$nParent,$sValue){
		$nParent=$nParent?$nParent:0;
		$this->_arrData[$nId]=$sValue;
		$this->_arrCate[$nId]=$nParent;
	}

	public function getChildsTree($nId=0){
		$arrChilds=array();
		foreach($this->_arrCate as $nChild=>$nParent){
			if($nParent==$nId){
				$arrChilds[$nChild]=$this->getChildsTree($nChild);
			}
		}
		return $arrChilds;
	}

	public function getChilds($nId=0){
		$arrChild=array();
		$arrChilds=$this->getChild($nId);
		foreach ($arrChilds as $nChild){
			$arrChild[]=$nChild;
			$arrChild=array_merge($arrChild,$this->getChilds($nChild));
		}
		return $arrChild;
	}

	public function getParentidValues($nId=0){
		$arrIds=array();
		foreach($this->_arrCate as $nChild=>$nParent){
			if($nParent==$nId){
				$arrIds[]=$nParent;
			}

		}
		return $arrIds;
	}

	public function getChild($nId){
		$arrChilds=array();
		foreach($this->_arrCate as $nChild=>$nParent){
			if($nParent==$nId){
				$arrChilds[$nChild]=$nChild;
			}
		}
		return $arrChilds;
	}

	public function hasChild($nId){
		$arrChilds=array();
		foreach($this->_arrCate as $nChild=>$nParent){
			if($nParent==$nId){
				$arrChilds[$nChild]=$nChild;
			}
		}
		return !empty($arrChilds);
	}

	public function getNodeLever($nId){
		$arrParents=array();
		if(key_exists($this->_arrCate[$nId ],$this->_arrCate)){
			$arrParents[]=$this->_arrCate[$nId];
			$arrParents=array_merge($arrParents,$this->getNodeLever($this->_arrCate[$nId ]));
		}
		return $arrParents;
	}

	public function getLayer($nId,$sPreStr='|-'){
		return str_repeat($sPreStr,count($this->getNodeLever($nId)));
	}

	public function getValue ($nId){
		return $this->_arrData[$nId ];
	}

	public function setTreeLiCallback($Callback){
		if(is_callable($Callback)){
			$this->_treeLiCallback=$Callback;
		}
	}

	public function displayTree($nId=0,$Callback=null,$arrUlClass=array('tree-parent','tree-child'),$arrLiClass=array('tree-parent-item','tree-child-item')){
		$arrCategory=$this->getChild($nId);

		$sUlClass=$nId==0?$arrUlClass[0]:$arrUlClass[1];// css 样式
		$sLiClass=$nId==0?$arrLiClass[0]:$arrLiClass[1];

		$sBack="<ul class=\"{$sUlClass}\" id=\"{$sUlClass}-{$nId}\" >";
		foreach($arrCategory as $nCategory){
			$sBack.="<li class=\"{$sLiClass}\" id=\"{$sLiClass}-{$nCategory}\">";
			$sBack.="<a href=\"".$this->getDisplayTreeItem($nCategory,$Callback)."\">".$this->_arrData[$nCategory ]."</a>".$this->getDisplayTreeItemLiMore($nCategory);
			$sBack.=$this->displaytree($nCategory,$Callback,$arrUlClass,$arrLiClass);
			$sBack.="</li>\n\t";
		}
		$sBack.="</ul>\n\t";

		return $sBack;
	}

	public function getDisplayTreeItem($nCategory,$Callback=null){
		if(is_callable($Callback)){
			return call_user_func($Callback,$nCategory);
		}else{
			return $nCategory;
		}
	}

	public function getDisplayTreeItemLiMore($nCategory){
		if(is_callable($this->_treeLiCallback)){
			return call_user_func($this->_treeLiCallback,$nCategory);
		}else{
			return '';
		}
	}

	public function setValue($nId,$sValue){
		$sOldValue=$this->_arrData[$nId];
		$this->_arrData[$nId]=$sOldValue;
		return $sOldValue;
	}

}
