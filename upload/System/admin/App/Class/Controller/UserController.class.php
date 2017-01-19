<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入Home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

/** 定义Home的语言包 */
define('__APPHOME_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/home/App/Lang/Admin');

/** 导入个人信息处理函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class UserController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.user_id']=array('gt',1);
		$arrMap['A.user_name']=array('like','%'.Q::G('user_name').'%');
		$arrMap['A.user_nikename']=array('like','%'.Q::G('user_nikename').'%');

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);

		// 性别
		$nUserprofileGender=Q::G('userprofile_gender');
		if($nUserprofileGender!==null && $nUserprofileGender!=''){
			$arrMap['B.userprofile_gender']=$nUserprofileGender;
		}

		// 最后登录时间
		$this->getTime_('A.update_dateline',$arrMap,'start_time_2','end_time_2');

		// 登录次数
		$arrMap['A.user_logincount']=array('egt',intval(Q::G('user_logincount')));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."userprofile AS B','B.userprofile_gender','A.user_id=B.user_id')";
	}

	public function checkusername(){
		$sUserName=Q::G('user_name','R');
		if(!empty($sUserName)){
			$arrUser=UserModel::F()->getByuser_name($sUserName)->toArray();
			if(!empty($arrUser['user_id'])){
				$this->E(Q::L('用户名已经存在','Controller'));
			}else{
				$this->S(Q::L('用户名可以使用','Controller'));
			}
		}else{
			$this->E(Q::L('用户名必须','Controller'));
		}
	}

	public function check_username(){
		$sUserName=trim(Q::G('user_name'));
		if(!$sUserName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['user_name']=$sUserName;
		$oUser=UserModel::F()->where($arrWhere)->setColumns('user_id')->getOne();
		if(empty($oUser['user_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function check_useremail(){
		$sUserEmail=trim(Q::G('user_email'));
		$nId=intval(Q::G('id'));
		if(!$sUserEmail){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['user_email']=$sUserEmail;
		if($nId){
			$arrWhere['user_id']=array('neq',$nId);
		}

		$oUser=UserModel::F()->where($arrWhere)->setColumns('user_id')->getOne();
		if(empty($oUser['user_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function resetpassword(){
		$nId=intval(Q::G('id'));
		$sPassword=Q::G('password');
		if(!empty($sPassword)){
			if(strlen($sPassword)<6){
				$this->E(Q::L('用户密码最小长度为6个字符','__COMMON_LANG__@Common'));
			}
			
			$arrUser=UserModel::F()->getByuser_id($nId)->toArray();
			$oUserModel=Q::instance('UserModel');
			$oUserModel->changePassword($sPassword,$sPassword,'',true,$arrUser,true);
			if($oUserModel->isError()){
				$this->E($oUserModel->getErrorMessage());
			}else{
				$this->S(Q::L('密码修改成功','Controller'));
			}
		}else{
			$this->E(Q::L('密码不能为空','Controller'));
		}
	}

	public function bForbid_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_user($nId)){
			$this->E(Q::L('系统用户无法禁用','Controller'));
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_user($nId)){
				$this->E(Q::L('系统用户无法删除','Controller'));
			}
		}
	}

	protected function aInsert($nId=null){
		// 写入注册用户的关联数据
		$oUserprofile=new UserprofileModel();
		$oUserprofile->user_id=$nId;
		$oUserprofile->save();
		if($oUserprofile->isError()){
			$oUserprofile->getErrorMessage();
		}

		$oUserCount=new UsercountModel();
		$oUserCount->user_id=$nId;
		$oUserCount->save();
		if($oUserCount->isError()){
			$oUserCount->getErrorMessage();
		}

		// 将用户加入注册会员角色
		$oUserrole=new UserroleModel();
		$oUserrole->role_id=1;
		$oUserrole->user_id=$nId;
		$oUserrole->save();
		if($oUserrole->isError()){
			$oUserrole->getErrorMessage();
		}

		// 保存home今日数据
		Core_Extend::updateOption(
			array(
				'todayusernum'=>$GLOBALS['_option_']['todayusernum']+1,
				'todaytotalnum'=>$GLOBALS['_option_']['todaytotalnum']+1
			)
		);
		
		$this->cache_site_();
	}

	protected function aForeverdelete_deep($sId){
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			// 用户资料数据
			$oUserprofileMeta=UserprofileModel::M();
			$oUserprofileMeta->deleteWhere(array('user_id'=>$nId));
			if($oUserprofileMeta->isError()){
				$this->E($oUserprofileMeta->getErrorMessage());
			}

			// 用户统计数据
			$oUsercountMeta=UsercountModel::M();
			$oUsercountMeta->deleteWhere(array('user_id'=>$nId));
			if($oUsercountMeta->isError()){
				$this->E($oUsercountMeta->getErrorMessage());
			}
			
			// 用户角色数据
			$oUserroleMeta=UserroleModel::M();
			$oUserroleMeta->deleteWhere(array('user_id'=>$nId));
			if($oUserroleMeta->isError()){
				$this->E($oUserroleMeta->getErrorMessage());
			}

			// 用户标签数据
			$oHometagindexMeta=HometagindexModel::M();
			$oHometagindexMeta->deleteWhere(array('user_id'=>$nId));
			if($oHometagindexMeta->isError()){
				$this->E($oHometagindexMeta->getErrorMessage());
			}

			// 用户留言数据
			$oUserguestbookMeta=UserguestbookModel::M();
			$oUserguestbookMeta->deleteWhere(array('userguestbook_userid'=>$nId));
			if($oUserguestbookMeta->isError()){
				$this->E($oUserguestbookMeta->getErrorMessage());
			}
		}

		$this->cache_site_();
	}

	public function is_system_user($nId){
		if($nId==1){
			return true;
		}
		return false;
	}

	protected function cache_site_(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("site");
	}

	protected function aUpdate($nId=null){
		$this->cache_site_();
	}

	public function show(){
		$nId=Q::G('id','G');

		if(!empty($nId)){
			$arrUser=Model::F_('user','@A','A.user_id=?',$nId)
				->setColumns('A.*')
				->joinLeft(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
				->joinLeft(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
				->getOne();

			if(!empty($arrUser['user_id'])){
				require_once(Core_Extend::includeFile('function/Credit_Extend'));
				$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();

				$arrData=array();
				foreach($arrAvailableExtendCredits as $nKey=>$arrValue){
					$arrData[$nKey]['title']="creditrule_extendcredit{$nKey} ({$arrValue['title']})";
					$arrData[$nKey]['value']=$arrUser['usercount_extendcredit'.$nKey];
				}

				$sDo=Q::G('do','G');
				if(!in_array($sDo,array('','base','contact','edu','work','info'))){
					$sDo='';
				}

				require_once(Core_Extend::includeFile('function/Profile_Extend'));
				Core_Extend::loadCache('userprofilesetting');
				$this->assign('arrUserprofilesettingDatas',$GLOBALS['_cache_']['userprofilesetting']);

				$this->assign('sDirthDistrict',Profile_Extend::getDistrict($arrUser,'birth',false));
				$this->assign('sResideDistrict',Profile_Extend::getDistrict($arrUser,'reside',false));

				// 视图
				$arrProfileSetting=Profile_Extend::getProfileSetting();
				$arrInfoMenus=Profile_Extend::getInfoMenu();

				$this->assign('arrBases',$arrProfileSetting[0]);
				$this->assign('arrContacts',$arrProfileSetting[1]);
				$this->assign('arrEdus',$arrProfileSetting[2]);
				$this->assign('arrWorks',$arrProfileSetting[3]);
				$this->assign('arrInfos',$arrProfileSetting[4]);
				$this->assign('arrInfoMenus',$arrInfoMenus);
				$this->assign('arrValue',$arrUser);
				$this->assign('nId',$nId);
				$this->assign('sDo',$sDo);
				$this->assign('arrUserCounts',$arrData);
				$this->display('user+show');
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

}
