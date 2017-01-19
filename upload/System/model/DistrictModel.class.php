<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   地区模型($$)*/

!defined('Q_PATH') && exit;

class DistrictModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'district',
			'check'=>array(
				'district_name'=>array(
					array('require',Q::L('地区名字不能为空','__COMMON_LANG__@Common')),
				),
				'district_sort'=>array(
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

	public function getDistrictByUpid($Upid,$sSort='DESC',$arrWhere=array()){
		$Upid=is_array($Upid)?array_map('intval',(array)$Upid):intval($Upid);

		if($Upid!==null){
			$arrWhere=array_merge(array('district_upid'=>array('in',$Upid)),$arrWhere);
			return self::F()->where($arrWhere)->order("district_sort {$sSort}")->asArray()->getAll();
		}

		return array();
	}

	public function isSpecial($nId){
		return in_array($nId,array(1,2,9,22,32,33,34,35));
	}

	public function getCityid($nCityId=0,$nProvinceId=0){
		if($nProvinceId && $this->isSpecial($nProvinceId)){
			$nCityId=$nProvinceId;
		}else{
			if(!$nCityId){
				$oTemp=self::F('district_upid=?',$nProvinceId)
					->order('district_sort ASC,district_id ASC')
					->getOne();
				$nCityId=$oTemp['district_id'];
			}
		}
		return $nCityId;
	}

	/**
	 * 获取区域
	 */
	public function getDistrict($sType='province',$nId=null,$nIsName=0){
		$arrLevel=array('province'=>1,'city'=>2,'district'=>3,'community'=>4);
		$arrWhere=array();

		switch($sType){
			case 'province':// 省
				$arrWhere['district_level']=1;
				break;
			case 'city':// 市
			case 'district':// 县
			case 'community':// 乡
				if($nIsName==1){
					$nId=self::F('district_level='.($arrLevel[$sType]-1).' AND district_name=?',$nId)->getColumn('district_id');
				}

				$arrWhere['district_upid']=$nId;
				$arrWhere['district_level']=$arrLevel[$sType];
				break;
		}

		return self::F()->setColumns('district_id,district_name')
			->where($arrWhere)
			->order('district_sort ASC,district_id ASC')
			->asArray()
			->getAll();
	}

	protected function beforeSave_(){
		$this->district_name=C::text($this->district_name);
		
		if($this->district_sort<0){
			$this->district_sort=0;
		}
		if($this->district_sort>999){
			$this->district_sort=999;
		}
	}

}
