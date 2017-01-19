<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   好友搜索结果($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Searchresult_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_option_']['allow_search_user']==0){
			$this->E(Q::L('系统关闭了好友搜索功能','Controller'));
		}
		
		// 扩展信息字段
		Core_Extend::loadCache('userprofilesetting');

		$arrFields=array();
		foreach($GLOBALS['_cache_']['userprofilesetting'] as $nKey=>$arrValue){
			if($arrValue['userprofilesetting_title'] && $arrValue['userprofilesetting_status']==1 && $arrValue['userprofilesetting_allowsearch']==1){
				$arrFields[$arrValue['userprofilesetting_id']]=$arrValue;
			}
		}

		$nNowyear=date('Y',CURRENT_TIMESTAMP);

		// 初始化数据库查询条件
		$arrWhere=$arrFrom=array();
		$sSql='';

		// 用户表
		$arrFrom['user']=$GLOBALS['_commonConfig_']['DB_PREFIX'].'user as u';

		$arrWhere[]='u.user_status=1';

		// 用户ID,用户名，头像情况
		foreach(array('user_id','user_name','user_avatar') as $sValue){
			if(isset($_GET[$sValue]) && $_GET[$sValue]){
				if($sValue=='user_name' && empty($_GET['username_precision'])){
					$_GET[$sValue]=strip_tags($_GET[$sValue]);
					$arrWhere[]='u.'.$sValue.' LIKE '.'"%'.$_GET[$sValue].'%"';
				}elseif($sValue=='user_id'){
					$_GET[$sValue]=Q::normalize($_GET[$sValue]);
					$arrWhere[]='u.'.$sValue.' in( '.implode(',',$_GET[$sValue]).')';
				}else{
					$arrWhere[]='u.'.$sValue.'="'.$_GET[$sValue].'"';
				}
			}
		}

		// 年龄段
		$nUserstartage=$nUserendage=0;
		if(!empty($_GET['user_endage'])){
			$nUserstartage=$nNowyear-intval($_GET['user_endage']);
		}
		if(!empty($_GET['user_startage'])){
			$nUserendage=$nNowyear-intval($_GET['user_startage']);
		}

		if($nUserstartage && $nUserendage && $nUserendage>$nUserstartage){
			$arrWhere[]='up.userprofile_birthyear>='.$nUserstartage.' AND up.userprofile_birthyear<='.$nUserendage;
		}elseif($nUserstartage && empty($nUserendage)){
			$arrWhere[]='up.userprofile_birthyear>='.$nUserstartage;
		}elseif(empty($nUserstartage) && $nUserendage){
			$arrWhere[]='up.userprofile_birthyear<='.$nUserendage;
		}

		// 扩展字段查询条件
		$bHavefield=FALSE;

		foreach($arrFields as $sKey=>$arrValue){
			$_GET[$sKey]=empty($_GET[$sKey])?'':strip_tags($_GET[$sKey]);

			if($_GET[$sKey]){
				$bHavefield=TRUE;
				$arrWhere[]='up.'.$sKey.'  LIKE '.'"%'.$_GET[$sKey].'%"';
			}
		}

		$arrFrom['userprofile']=$GLOBALS['_commonConfig_']['DB_PREFIX'].'userprofile as up';
		$arrWhere['userprofile']="up.user_id=u.user_id";

		$arrUsers=array();
		if($arrWhere){
			$oDb=Db::RUN();
			$arrCount=$oDb->getRow("SELECT COUNT(*) AS row_count FROM ".implode(',',$arrFrom)." WHERE ".implode(' AND ',$arrWhere));
			$nTotalRecord=$arrCount['row_count'];
			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['searchuser_list_num']);
			$sSql="SELECT u.user_id,u.user_name,u.user_nikename,u.create_dateline,u.user_lastlogintime,u.user_logincount,up.userprofile_gender FROM ".implode(',',$arrFrom)." WHERE ".implode(' AND ',$arrWhere).' ORDER BY user_id DESC LIMIT '.$oPage->S().','.$oPage->N();
			$arrUsers=$oDb->getAllRows($sSql);

			$this->assign('nTotalUser',$nTotalRecord);
			$nOldUrlModel=$GLOBALS['_commonConfig_']['URL_MODEL'];
			$GLOBALS['_commonConfig_']['URL_MODEL']=0;
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$GLOBALS['_commonConfig_']['URL_MODEL']=$nOldUrlModel;
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('好友搜索结果','Controller')));

		$this->assign('arrUsers',$arrUsers);
		$this->assign('sKey',trim(Q::G('user_name')));
		$this->display('friend+searchresult');
	}

}
