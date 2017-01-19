<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   RBAC 权限验证（Modify from TP!）($$)*/

!defined('Q_PATH') && exit;

class Rbac{

	protected static $_sErrorMessage='';
	
	public static function checkRbac($arrUser=array()){
		if(!empty($arrUser['is_admin'])){
			return true;
		}

		if($GLOBALS['_commonConfig_']['USER_AUTH_ON'] && !in_array(MODULE_NAME,Q::normalize($GLOBALS['_commonConfig_']['NOT_AUTH_MODULE']))){// 用户权限检查
			if(!self::access($arrUser)){
				Q::cookie('_rbacerror_referer_',__SELF__);
				if(empty($arrUser['user_id']) && !C::isAjax()){// 检查认证识别号
					C::urlGo(Q::U($GLOBALS['_commonConfig_']['USER_AUTH_GATEWAY'],array('referer'=>__SELF__,'rbac'=>1),true));// 跳转到认证网关
				}

				if($GLOBALS['_commonConfig_']['RBAC_ERROR_PAGE'] && !C::isAjax()){// 没有权限 抛出错误
					C::urlGo(Q::U($GLOBALS['_commonConfig_']['RBAC_ERROR_PAGE'],array('referer'=>__SELF__,'rbac'=>1),true));
				}else{
					self::$_sErrorMessage=Q::L('你没有访问权限','__QEEPHP__@Q');
					return false;
				}
			}
		}

		return true;
	}

	public static function access($arrUser=array(),$sAppName=APP_NAME){
		if(self::check()){
			if(empty($arrUser['is_admin'])){
				// 登录用户访问黑白名单
				$bTrueRbacUserAccess=$bFalseRbacUserAccess='';
				$bTrueRbacUserAccessLevel=$bFalseRbacUserAccessLevel=0;
				
				if($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'] && is_array($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'])){
					if(array_key_exists(APP_NAME.'@*@*',$GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'])){
						if($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@*@*']===true){
							$bTrueRbacUserAccess=true;
							$bTrueRbacUserAccessLevel=1;
						}elseif($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@*@*']===false){
							$bFalseRbacUserAccess=false;
							$bFalseRbacUserAccessLevel=1;
						}
					}
					
					if(array_key_exists(APP_NAME.'@'.MODULE_NAME.'@*',$GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'])){
						if($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@*']===true){
							$bTrueRbacUserAccess=true;
							$bTrueRbacUserAccessLevel=2;
						}elseif($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@*']===false){
							$bFalseRbacUserAccess=false;
							$bFalseRbacUserAccessLevel=2;
						}
					}
					
					if(array_key_exists(APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME,$GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'])){
						if($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME]===true){
							$bTrueRbacUserAccess=true;
							$bTrueRbacUserAccessLevel=3;
						}elseif($GLOBALS['_commonConfig_']['RBAC_USER_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME]===false){
							$bFalseRbacUserAccess=false;
							$bFalseRbacUserAccessLevel=3;
						}
					}
				}

				if($bTrueRbacUserAccessLevel>0 && $bTrueRbacUserAccess===true && $bTrueRbacUserAccessLevel>$bFalseRbacUserAccessLevel){
					return true;
				}
				
				// 游客访问权限判断
				$nAuthid=isset($arrUser['user_id'])?$arrUser['user_id']:-1;
				if(!$nAuthid && $GLOBALS['_commonConfig_']['GUEST_AUTH_ON']){
					$nAuthid=$GLOBALS['_commonConfig_']['GUEST_AUTH_ID'];
				}

				$arrAccessList=self::getUserRbac($nAuthid);

				// 权限认证
				if(empty($arrAccessList) || !isset($arrAccessList[$sAppName][$sAppName.'@'.MODULE_NAME][$sAppName.'@'.MODULE_NAME.'@'.ACTION_NAME])){
					return false;
				}else{
					return true;
				}
			}else{
				return true;
			}
		}

		return true;
	}

	public static function check(){
		if($GLOBALS['_commonConfig_']['USER_AUTH_ON']){// 如果项目要求认证，并且当前模块需要认证，则进行权限认证
			$arrModule=array();
			$arrAction=array();

			if(''!=$GLOBALS['_commonConfig_']['REQUIRE_AUTH_MODULE']){
				$arrModule['yes']=Q::normalize(strtoupper($GLOBALS['_commonConfig_']['REQUIRE_AUTH_MODULE']));
			}else{
				$arrModule['no']=Q::normalize(strtoupper($GLOBALS['_commonConfig_']['NOT_AUTH_MODULE']));
			}

			// 检查当前模块是否需要认证
			if((!empty($arrModule['no']) and !in_array(strtoupper(MODULE_NAME),$arrModule['no'])) ||
				(!empty($arrModule['yes']) and in_array(strtoupper(MODULE_NAME),$arrModule['yes'])) || empty($arrModule['yes'])
			){
				if(''!=$GLOBALS['_commonConfig_']['REQUIRE_AUTH_ACTION']){
					$arrAction['yes']=Q::normalize(strtoupper($GLOBALS['_commonConfig_']['REQUIRE_AUTH_ACTION']));// 需要认证的操作
				}else{
					$arrAction['no']=Q::normalize(strtoupper($GLOBALS['_commonConfig_']['NOT_AUTH_ACTION']));// 无需认证的操作
				}

				// 检查当前操作是否需要认证
				if((!empty($arrAction['no']) and !in_array(strtoupper(ACTION_NAME),$arrAction['no'])) ||
					(!empty($arrAction['yes']) and in_array(strtoupper(ACTION_NAME),$arrAction['yes'])) || empty($arrAction['yes'])
				){
					// 游客访问权限检查
					$bTrueRbacGuestAccess=$bFalseRbacGuestAccess='';
					$bTrueRbacGuestAccessLevel=$bFalseRbacGuestAccessLevel=0;

					if($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'] && is_array($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'])){
						if(array_key_exists(APP_NAME.'@*@*',$GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'])){
							if($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@*@*']===true){
								$bTrueRbacGuestAccess=true;
								$bTrueRbacGuestAccessLevel=1;
							}elseif($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@*@*']===false){
								$bFalseRbacGuestAccess=false;
								$bFalseRbacGuestAccessLevel=1;
							}
						}
						
						if(array_key_exists(APP_NAME.'@'.MODULE_NAME.'@*',$GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'])){
							if($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@*']===true){
								$bTrueRbacGuestAccess=true;
								$bTrueRbacGuestAccessLevel=2;
							}elseif($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@*']===false){
								$bFalseRbacGuestAccess=false;
								$bFalseRbacGuestAccessLevel=2;
							}
						}
						
						if(array_key_exists(APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME,$GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'])){
							if($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME]===true){
								$bTrueRbacGuestAccess=true;
								$bTrueRbacGuestAccessLevel=3;
							}elseif($GLOBALS['_commonConfig_']['RBAC_GUEST_ACCESS'][APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME]===false){
								$bFalseRbacGuestAccess=false;
								$bFalseRbacGuestAccessLevel=3;
							}
						}
					}

					if($bTrueRbacGuestAccessLevel>0 && $bTrueRbacGuestAccess===true && $bTrueRbacGuestAccessLevel>$bFalseRbacGuestAccessLevel){
						return false;
					}

					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		return false;
	}

	public static function getUserRbac($nUserId=null,$nRoleid=null){
		if($nUserId===null){
			$arrRoleIds[]=array('role_id'=>$nRoleid);
		}else{
			$oDb=Db::RUN();

			$sSql="SELECT role_id FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_USERROLE_TABLE']." WHERE user_id={$nUserId}";

			$arrRoleIds=$oDb->getAllRows($sSql);
		}

		$arrRbac=array();
		$arrRbacCache=self::cacheRbac();

		foreach($arrRoleIds as $arrRole){
			if(isset($arrRbacCache[$arrRole['role_id']])){
				foreach($arrRbacCache[$arrRole['role_id']] as $sK=>$arrV){
					foreach($arrV as $sKTwo=>$nVTwo){
						$arrRbac[$sK][$sKTwo]=$nVTwo;
					}
				}
			}
		}

		return $arrRbac;
	}

	public static function cacheRbac(){
		$arrRoles=Q::cache('rbac','',
			array('encoding_filename'=>false,
				'cache_path'=>(defined('DIS_RUNTIME_PATH')?DIS_RUNTIME_PATH:APP_RUNTIME_PATH.'/Data/Dis')
			)
		);

		// 缓存权限
		if($arrRoles===false){
			$oDb=Db::RUN();
			
			$sSql="SELECT role_id FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_ROLE_TABLE']." WHERE role_status=1";
			
			$arrRoleIds=$oDb->getAllRows($sSql);

			$arrRoles=array();
			if($arrRoleIds){
				foreach($arrRoleIds as $arrVal){
					$arrRoles[$arrVal['role_id']]=self::getAccessList(null,$arrVal['role_id']);
				}
			}

			Q::cache('rbac',$arrRoles,
				array('encoding_filename'=>false,
					'cache_path'=>(defined('DIS_RUNTIME_PATH')?DIS_RUNTIME_PATH:APP_RUNTIME_PATH.'/Data/Dis')
				)
			);
		}

		return $arrRoles;
	}

	public static function getAccessList($nAuthId=null,$nRoleid=null){
		$oDb=Db::RUN();

		$arrTable=array(
			'role'=>$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_ROLE_TABLE'],
			'userrole'=>$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_USERROLE_TABLE'],
			'access'=>$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_ACCESS_TABLE'],
			'node'=>$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_NODE_TABLE']
		);

		$sUserroleTable=$sUserroleWhere=$sRoleidWhere='';
		if($nAuthId!==null){
			$sUserroleTable=$arrTable['userrole']." AS userrole,";
			$sUserroleWhere=" AND userrole.user_id='{$nAuthId}' AND userrole.role_id=role.role_id";
		}

		if($nRoleid!==null){
			$sRoleidWhere=" AND role.role_id='{$nRoleid}'";
		}

		$sSql="SELECT DISTINCT node.node_id,node.node_name FROM ".
			$arrTable['role']." AS role,".
			$sUserroleTable.
			$arrTable['access']." AS access ,".
			$arrTable['node']." AS node ".
			"WHERE (access.role_id=role.role_id ".
			"OR (access.role_id=role.role_parentid AND role.role_parentid!=0)) AND role.role_status=1 AND ".
			"access.node_id=node.node_id AND node.node_level=1 AND node.node_status=1".$sUserroleWhere.$sRoleidWhere;
		
		$arrAccess=array();// 项目权限列表
		$arrApps=$oDb->getAllRows($sSql);
		foreach($arrApps as $sKey=>$arrApp){
			$nAppId=$arrApp['node_id'];
			$sAppName=$arrApp['node_name'];
			$arrAccess[ strtolower($sAppName)]=array();// 读取项目的模块权限
			$sSql="SELECT DISTINCT node.node_id,node.node_name FROM ".
				$arrTable['role']." AS role,".
				$arrTable['userrole']." AS userrole,".
				$arrTable['access']." AS access ,".
				$arrTable['node']." AS node ".
				"WHERE (access.role_id=role.role_id ".
				"OR (access.role_id=role.role_parentid AND role.role_parentid!=0)) AND role.role_status=1 ".
				"AND access.node_id=node.node_id AND node.node_level=2 AND node.node_parentid={$nAppId} AND node.node_status=1".$sUserroleWhere.$sRoleidWhere;

			$arrModules=$oDb->getAllRows($sSql);
			$arrPublicAction=array();// 判断是否存在公共模块的权限
			foreach($arrModules as $sKey=>$arrModule){
				$nModuleId=$arrModule['node_id'];
				$sModuleName=$arrModule['node_name'];
				if('PUBLIC'==strtoupper($sModuleName)){
					$sSql="SELECT DISTINCT node.node_id,node.node_name FROM ".
					$arrTable['role']." AS role,".
					$arrTable['userrole']." AS userrole,".
					$arrTable['access']." AS access ,".
					$arrTable['node']." AS node ".
					"WHERE (access.role_id=role.role_id ".
					"OR (access.role_id=role.role_parentid AND role.role_parentid!=0)) AND role.role_status=1 ".
					"AND access.node_id=node.node_id AND node.node_level=3 AND node.node_parentid={$nModuleId} AND node.node_status=1".$sUserroleWhere.$sRoleidWhere;

					$arrRs=$oDb->getAllRows($sSql);
					foreach($arrRs as $arrA){
						$arrPublicAction[$arrA['node_name']]=$arrA['node_id'];
					}
					unset($arrModules[$sKey]);
					break;
				}
			}

			foreach($arrModules as $sKey=>$arrModule){// 依次读取模块的操作权限
				$nModuleId=$arrModule['node_id'];
				$sModuleName=$arrModule['node_name'];
				$sSql="SELECT DISTINCT node.node_id,node.node_name FROM ".
					$arrTable['role']." AS role,".
					$arrTable['userrole']." AS userrole,".
					$arrTable['access']." AS access ,".
					$arrTable['node']." AS node ".
					"WHERE (access.role_id=role.role_id ".
					"OR (access.role_id=role.role_parentid AND role.role_parentid!=0))AND role.role_status=1 ".
					" AND access.node_id=node.node_id AND node.node_level=3 and node.node_parentid={$nModuleId} AND node.node_status=1".$sUserroleWhere.$sRoleidWhere;

				$arrRs=$oDb->getAllRows($sSql);
				$arrAction=array();
				foreach($arrRs as $arrA){
					if(strpos($arrA['node_name'],'|')!==false){
						$arrNodename=Q::normalize($arrA['node_name'],'|');
						foreach($arrNodename as $nKey=>&$sNodename){
							if($nKey>0){
								if(strrpos($arrA['node_name'],'@')!==false){
									$sNodename=C::subString($arrA['node_name'],0,strrpos($arrA['node_name'],'@')).'@'.$sNodename;
								}
							}
						}
					}else{
						$arrNodename=array($arrA['node_name']);
					}

					foreach($arrNodename as $sValue){
						$arrAction[$sValue]=$arrA['node_id'];
					}
				}
				$arrAction+=$arrPublicAction;// 和公共模块的操作权限合并
				$arrAccess[strtolower($sAppName)][strtolower($sModuleName)]=array_change_key_case($arrAction,CASE_LOWER);
			}
		}

		return $arrAccess;
	}

	public static function getNodeList(){
		$oDb=Db::RUN();

		$arrAccessListall=array();

		$sSql="SELECT * FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_NODE_TABLE']." WHERE node_status=1 AND node_id!=1 AND node_parentid=0";
		$arrApps=$oDb->getAllRows($sSql);

		foreach($arrApps as $arrApp){
			$arrAccessListall[$arrApp['node_name']]['title']=$arrApp['node_title'];

			$sSql="SELECT * FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_NODE_TABLE']." WHERE node_status=1 AND node_parentid=".$arrApp['node_id'];
			$arrModules=$oDb->getAllRows($sSql);

			foreach($arrModules as $arrModule){
				$sSql="SELECT * FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX'].$GLOBALS['_commonConfig_']['RBAC_NODE_TABLE']." WHERE node_status=1 AND node_parentid=".$arrModule['node_id'];
				$arrActions=$oDb->getAllRows($sSql);

				foreach($arrActions as $arrAction){
					$sNodename=$arrAction['node_name'];
					if(strpos($sNodename,'|')){
						$sNodename=C::subString($sNodename,0,strpos($sNodename,'|'));
					}
					
					$arrAccessListall[$arrApp['node_name']]['data'][]=array('name'=>$sNodename,'title'=>$arrAction['node_title']);
				}
			}
		}

		return $arrAccessListall;
	}

	public static function isError(){
		return !empty(self::$_sErrorMessage);
	}

	public static function getErrorMessage(){
		return self::$_sErrorMessage;
	}

}
