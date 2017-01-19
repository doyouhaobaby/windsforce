<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   地区控制器($$)*/

!defined('Q_PATH') && exit;

class DistrictController extends AController{
	
	protected $_arrUpids;
	protected $_nTheId;
	
	public function index($sName=NULL,$bDisplay=true){
		$this->init_district_();

		$arrThevalues=array();
		foreach(Core_Extend::getDistrictByUpid($this->_arrUpids) as $arrValue){
			if($arrValue['district_upid']==$this->_nTheId){
				$arrThevalues[]=array($arrValue['district_id'],$arrValue['district_name'],$arrValue['district_sort']);
			}
		}

		$this->assign('arrThevalues',$arrThevalues);
		$this->display();
	}

	protected function init_district_(){
		$arrValues=array(intval(Q::G('pid','G')),intval(Q::G('cid','G')),intval(Q::G('did','G')));
		$arrElems=array(Q::G('province','G'),Q::G('city','G'),Q::G('district','G'));

		$nLevel=1;
		$arrUpids=array(0);
		$nTheid=0;
		for($nI=0;$nI<3;$nI++){
			if(!empty($arrValues[$nI])){
				$nTheid=intval($arrValues[$nI]);
				$arrUpids[]=$nTheid;
				$nLevel++;
			}else{
				for($nJ=$nI;$nJ<3;$nJ++){
					$arrValues[$nJ]='';
				}
				break;
			}
		}

		$this->_arrUpids=$arrUpids;
		$this->_nTheId=$nTheid;

		$arrOptions=array(1=>array(),2=>array(),3=>array());
		foreach(Core_Extend::getDistrictByUpid($arrUpids) as $arrValue){
			$arrOptions[$arrValue['district_level']][]=array($arrValue['district_id'],$arrValue['district_name']);
		}

		$arrNames=array('province','city','district');
		for($nI=0;$nI<3;$nI++){
			$arrElems[$nI]=!empty($arrElems[$nI])?$arrElems[$nI]:$arrNames[$nI];
		}

		$sHtml='';
		for($nI=0;$nI<3;$nI++){
			$nL=$nI+1;
			$sJscall=($nI==0?'this.form.city.value=\'\';this.form.district.value=\'\';':'')."refreshDistrict('{$arrElems[0]}','{$arrElems[1]}','{$arrElems[2]}')";
			$sHtml.='<select name="'.$arrElems[$nI].'" id="'.$arrElems[$nI].'" onchange="'.$sJscall.'">';
			$sHtml.='<option value="">'.Core_Extend::getDistrictType($nL).'</option>';
			foreach($arrOptions[$nL] as $arrOption){
				$sSelected=$arrOption[0]==$arrValues[$nI]?' selected="selected"':'';
				$sHtml.='<option value="'.$arrOption[0].'"'.$sSelected.'>'.$arrOption[1].'</option>';
			}
			$sHtml.='</select>&nbsp;&nbsp;';
		}

		$this->assign('arrValues',$arrValues);
		$this->assign('sHtml',$sHtml);
		$this->assign('nUpid',array_pop($arrUpids));
	}

	public function bAdd_(){
		$this->init_district_();
	}

	public function bEdit_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_district($nId)){
			$this->E(Q::L('系统地理数据无法编辑','Controller'));
		}

		$this->init_district_();
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('id','G');

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_district($nId)){
				$this->E(Q::L('系统地理数据无法删除','Controller'));
			}

			$nDistricts=DistrictModel::F('district_upid=?',$nId)->all()->getCounts();
			$oDistrict=DistrictModel::F('district_id=?',$nId)->query();
			if($nDistricts>0){
				$this->E(Q::L('地区%s存在子地区，你无法删除','Controller',null,$oDistrict->district_name));
			}
		}
	}

	public function is_system_district($nId){
		if($nId<=45051){
			return true;
		}
		return false;
	}

}
