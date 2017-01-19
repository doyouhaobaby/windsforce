<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台个人信息管理($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Index_C_Controller extends InitController{

	public function index(){
		// 资料类型
		$sDo=Q::G('do','G');
		if(!in_array($sDo,array('','base','contact','edu','work','info'))){
			$sDo='';
		}

		Core_Extend::loadCache('userprofilesetting');
		$this->assign('arrUserprofilesettingDatas',$GLOBALS['_cache_']['userprofilesetting']);
		
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
			->joinLeft(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$GLOBALS['___login___']['user_id']))
			->getOne();

		// 生日计算
		$nNowYear=date('Y',CURRENT_TIMESTAMP);
		if(in_array($arrUserInfo['userprofile_birthmonth'],array(1,3,5,7,8,10,12))){
			$nDays=31;
		}elseif(in_array($arrUserInfo['userprofile_birthmonth'],array(4,6,9,11))){
			$nDays=30;
		}elseif($arrUserInfo['userprofile_birthyear'] &&
			(($arrUserInfo['userprofile_birthyear']%400==0) || ($arrUserInfo['userprofile_birthyear']%4==0 && $arrUserInfo['userprofile_birthyear']%400!=0))
		){
			$nDays=29;
		}else{
			$nDays=28;
		}

		$this->assign('nNowYear',$nNowYear);
		$this->assign('nNowDays',$nDays);
		$this->assign('sDirthDistrict',Profile_Extend::getDistrict($arrUserInfo,'birth'));
		$this->assign('sResideDistrict',Profile_Extend::getDistrict($arrUserInfo,'reside'));
		$this->assign('sDo',$sDo);

		// 视图
		$arrProfileSetting=Profile_Extend::getProfileSetting();
		$this->assign('arrBases',$arrProfileSetting[0]);
		$this->assign('arrContacts',$arrProfileSetting[1]);
		$this->assign('arrEdus',$arrProfileSetting[2]);
		$this->assign('arrWorks',$arrProfileSetting[3]);
		$this->assign('arrInfos',$arrProfileSetting[4]);

		$arrInfoMenus=Profile_Extend::getInfoMenu();
		$this->assign('arrInfoMenus',$arrInfoMenus);

		// 用户是否在线
		$arrOnline=Model::F_('online','user_id=?',$GLOBALS['___login___']['user_id'])->setColumns('user_id,online_isstealth,online_ip')->getOne();
		$bOnline=false;
		if($arrOnline && $arrOnline['online_isstealth']==0){
			$bOnline=true;
		}
		
		Core_Extend::getSeo($this,array('title'=>Q::L('修改资料','Controller')));
		
		$this->assign('arrUserInfo',$arrUserInfo);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_changeinformation_status']);
		$this->assign('bOnline',$bOnline);
		$this->display('spaceadmin+index');
	}

}
