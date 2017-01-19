<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人信息处理相关函数($$)*/

!defined('Q_PATH') && exit;

class Profile_Extend{
	
	static public function getDistrict($arrSpace=array(),$sDistrictName='birth',$bDisplayModify=true,$sDistrictPrefix='userprofile_',$bUserprofile=true){
		$arrValues=array(0,0,0,0);
		$arrElems=array($sDistrictName.'province',$sDistrictName.'city',$sDistrictName.'dist',$sDistrictName.'community');

		if($bDisplayModify===false || !empty($arrSpace[$sDistrictPrefix.$sDistrictName.'province'])){
			if($bUserprofile===true){
				$sHtml=self::profileShow($sDistrictPrefix.$sDistrictName.'city',$arrSpace);
			}else{
				$sHtml=$arrSpace[$sDistrictPrefix.$sDistrictName.'province']
					.(!empty($arrSpace[$sDistrictPrefix.$sDistrictName.'city'])?' '.$arrSpace[$sDistrictPrefix.$sDistrictName.'city']:'')
					.(!empty($arrSpace[$sDistrictPrefix.$sDistrictName.'dist'])?' '.$arrSpace[$sDistrictPrefix.$sDistrictName.'dist']:'')
					.(!empty($arrSpace[$sDistrictPrefix.$sDistrictName.'community'])?' '.$arrSpace[$sDistrictPrefix.$sDistrictName.'community']:'');
			}

			if($bDisplayModify===true){
				$sHtml.='&nbsp;(<a href="javascript:;" onclick=\'showDistrict("'.$sDistrictName.'districtbox",
				{"isfirst":1,"isname":1,"name":"'.$sDistrictPrefix.$arrElems[0].','.$sDistrictPrefix.$arrElems[1].','.$sDistrictPrefix.$arrElems[2].','.$sDistrictPrefix.$arrElems[3].'","id":"'.$sDistrictPrefix.$arrElems[0].','.$sDistrictPrefix.$arrElems[1].','.$sDistrictPrefix.$arrElems[2].','.$sDistrictPrefix.$arrElems[3].'"}); return false;\'>'.Q::L('修改','__COMMON_LANG__@Common').'</a>)';
				$sHtml.= '<p id="'.$sDistrictName.'districtbox"></p>';
			}
		}else{
			$sHtml='<p id="'.$sDistrictName.'districtbox">'.Core_Extend::showDistrict(
				array(
					'isfirst'=>1,
					'isname'=>1,
					'name'=>$sDistrictPrefix.$arrElems[0].','.$sDistrictPrefix.$arrElems[1].','.$sDistrictPrefix.$arrElems[2].','.$sDistrictPrefix.$arrElems[3],
					'id'=>$sDistrictPrefix.$arrElems[0].','.$sDistrictPrefix.$arrElems[1].','.$sDistrictPrefix.$arrElems[2].','.$sDistrictPrefix.$arrElems[3]
				)
			).'</p>';
		}

		return $sHtml;
	}

	static public function profileShow($sFieldid,$arrSpace=array()){
		if(empty($GLOBALS['_cache_']['userprofilesetting'])){
			Core_Extend::loadCache('userprofilesetting');
		}

		if(isset($GLOBALS['_cache_']['userprofilesetting'][$sFieldid])){
			$arrField=$GLOBALS['_cache_']['userprofilesetting'][$sFieldid];
		}else{
			return false;
		}

		if(empty($arrField) || in_array($sFieldid,array('userprofile_birthprovince','userprofile_resideprovince'))){
			return false;
		}

		if($sFieldid=='userprofile_birthcity'){
			return $arrSpace['userprofile_birthprovince']
				.(!empty($arrSpace['userprofile_birthcity'])?' '.$arrSpace['userprofile_birthcity']:'')
				.(!empty($arrSpace['userprofile_birthdist'])?' '.$arrSpace['userprofile_birthdist']:'')
				.(!empty($arrSpace['userprofile_birthcommunity'])?' '.$arrSpace['userprofile_birthcommunity']:'');
		}elseif($sFieldid=='userprofile_residecity'){
			return $arrSpace['userprofile_resideprovince']
				.(!empty($arrSpace['userprofile_residecity'])?' '.$arrSpace['userprofile_residecity']:'')
				.(!empty($arrSpace['userprofile_residedist'])?' '.$arrSpace['userprofile_residedist']:'')
				.(!empty($arrSpace['userprofile_residecommunity'])?' '.$arrSpace['userprofile_residecommunity']:'');
		}
	}

	public static function getGender($nGender){
		switch($nGender){
			case 0:
				return Q::L('保密','__COMMON_LANG__@Common');
				break;
			case 1:
				return Q::L('男','__COMMON_LANG__@Common');
				break;
			case 2:
				return Q::L('女','__COMMON_LANG__@Common');
				break;
		}
	}

	public static function getInfoMenu(){
		$arrInfoMenus=array(
			''=>Q::L('基本资料','__COMMON_LANG__@Common'),
			'contact'=>Q::L('联系方式','__COMMON_LANG__@Common'),
			'edu'=>Q::L('教育情况','__COMMON_LANG__@Common'),
			'work'=>Q::L('工作状况','__COMMON_LANG__@Common'),
			'info'=>Q::L('个人信息','__COMMON_LANG__@Common')
		);

		return $arrInfoMenus;
	}

	public static function getProfileSetting(){
		$arrBases=array('userprofile_realname','userprofile_gender','userprofile_birthday',
			'userprofile_birthcity','userprofile_residecity','userprofile_affectivestatus',
			'userprofile_lookingfor','userprofile_bloodtype');

		$arrContacts=array('userprofile_telephone','userprofile_mobile','userprofile_icq',
			'userprofile_qq','userprofile_yahoo','userprofile_msn','userprofile_taobao',
			'userprofile_google','userprofile_baidu','userprofile_renren','userprofile_douban',
			'userprofile_windsforce','userprofile_weibocom','userprofile_tqqcom',
			'userprofile_diandian','userprofile_facebook','userprofile_twriter','userprofile_skype');

		$arrEdus=array('userprofile_nowschool','userprofile_kindergarten','userprofile_primary',
			'userprofile_juniorhighschool','userprofile_highschool','userprofile_university',
			'userprofile_master','userprofile_dr','userprofile_graduateschool','userprofile_education');

		$arrWorks=array('userprofile_occupation','userprofile_company','userprofile_position','userprofile_revenue');

		$arrInfos=array('userprofile_idcardtype','userprofile_idcard','userprofile_address','userprofile_zipcode',
			'userprofile_site','userprofile_bio','userprofile_interest');

		return array($arrBases,$arrContacts,$arrEdus,$arrWorks,$arrInfos);
	}

	public static function formatUserinfo($sInfo){
		return nl2br(htmlspecialchars($sInfo));
	}

	public static function getUserprofilegender($nUserprofilegender){
		$sUsergender='';
		switch($nUserprofilegender){
			case '0':
				$sUsergender=__PUBLIC__.'/images/common/sex/secrecy.png';
				break;
			case '1':
				$sUsergender=__PUBLIC__.'/images/common/sex/male.png';
				break;
			case '2':
				$sUsergender=__PUBLIC__.'/images/common/sex/female.png';
				break;
		}
		
		return $sUsergender;
	}

	static public function checkPrivacy($nUserid,$nPrivacy=0){
		$nUserid=intval($nUserid);
		$nPrivacy=intval($nPrivacy);

		// 公开权限直接放行
		if($nPrivacy==0){
			return true;
		}
		
		if($GLOBALS['___login___']===false){
			return false;
		}

		// 自己 && 后台管理员
		$nLoginuserid=intval($GLOBALS['___login___']['user_id']);
		if($nUserid===$nLoginuserid || Core_Extend::isAdmin()){
			return true;
		}

		// 好友可见
		if($nPrivacy==1){
			$arrTryfriend=Model::F_('friend','user_id=? AND friend_friendid=? AND friend_status=1',$nUserid,$nLoginuserid)
				->setColumns('user_id')
				->getOne();
			if(!empty($arrTryfriend['user_id'])){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

}
