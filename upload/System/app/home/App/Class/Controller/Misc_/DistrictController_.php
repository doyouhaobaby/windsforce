<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   地区获取控制器($$)*/

!defined('Q_PATH') && exit;

class District_C_Controller extends InitController{

	public function index(){
		$value=Q::G('value');
		$provinceValue=intval(Q::G('provincevalue'));// 根据省份获取区，省份存在北京这样的直辖市
		$sType=trim(Q::G('type'));
		$nIsname=intval(Q::G('isname'));
		$nElementType=intval(Q::G('ele'));// 默认0=option,1=checkbox,2=radio
		$sElementNameId=trim(Q::G('elenameid'));// 默认elenameid
		$sElementValue=trim(Q::G('elevalue'));
		$arrType=array('city','district','community');
		$sValueField=$nIsname==1?'district_name':'district_id';

		if(!in_array($sType,$arrType)){
			exit('');
		}

		if(!$sElementNameId){
			$sElementNameId='elenameid';
		}

		$oDistrictModel=Q::instance('DistrictModel');var_dump($value);
		if($sType=='district' && $provinceValue){
			$value=$oDistrictModel->getCityid($value,$provinceValue);
		}
		$arrDataList=$oDistrictModel->getDistrict($sType,$value,$nIsname);

		if(empty($arrDataList)){
			exit('');
		}else{
			if($nElementType==1){
				$sElementValue=explode(',',$sElementValue);
			}
			
			foreach($arrDataList as $arrValue){
				if($nElementType==2){
					echo "<input type=\"radio\" name=\"{$sElementNameId}\" value=\"{$arrValue[$sValueField]}\" ".($arrValue[$sValueField]==$sElementValue?'checked="checked"':'')." />{$arrValue['district_name']}";
				}elseif($nElementType==1){
					echo "<input type=\"checkbox\" ".(in_array($arrValue[$sValueField],$sElementValue)?'checked="checked"':'')." name=\"{$sElementNameId}[]\" value=\"{$arrValue[$sValueField]}\">{$arrValue['district_name']}";
				}else{
					echo "<option value=\"{$arrValue[$sValueField]}\" ".($sElementValue==$arrValue[$sValueField]?'selected':'').">{$arrValue['district_name']}</option>";
				}
			}
		}

		exit();
	}

	public function php(){
		if(isset($_GET['app'])){
			unset($_GET['app']);
		}
		if(isset($_GET['a'])){
			unset($_GET['a']);
		}
		if(isset($_GET['c'])){
			unset($_GET['c']);
		}

		exit(Core_Extend::showDistrict($_GET));
	}

}
