<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我的权限($$)*/

!defined('Q_PATH') && exit;

class Myrbac_C_Controller extends InitController{

	public function index(){
		$nRId=intval(Q::G('rid','G'));
		if($nRId){
			$arrRole=Model::F_('role','role_id=?',$nRId)->setColumns('role_id,role_name')->getOne();
			if(empty($arrRole['role_id'])){
				$this->U('home://public/myrbac');
			}
			$this->assign('arrRole',$arrRole);

			// 角色权限
			$arrAccesslist=Rbac::getUserRbac(null,$nRId);
		}else{
			if($GLOBALS['___login___']!==false){
				$nAuthid=$GLOBALS['___login___']['user_id'];
			}else{
				$nAuthid=$GLOBALS['_commonConfig_']['GUEST_AUTH_ID'];
			}

			// 读取我的角色
			$arrUserroles=UserroleModel::F('@A','user_id=?',$nAuthid)
				->setColumns('A.*')
				->joinLeft(Q::C('DB_PREFIX').'role AS B','B.role_name','A.role_id=B.role_id')
				->asArray()
				->getAll();
			$this->assign('arrUserroles',$arrUserroles);
			
			// 处理我的权限
			$arrAccesslist=Rbac::getUserRbac($nAuthid);
		}

		// 处理一下权限
		$arrMyaccesslist=array();
		if(is_array($arrAccesslist)){
			foreach($arrAccesslist as $arrTemp){
				if(is_array($arrTemp)){
					foreach($arrTemp as $arrTempTwo){
						if(is_array($arrTempTwo)){
							foreach($arrTempTwo as $sKey=>$nTemp){
								$arrMyaccesslist[]=$sKey;
							}
						}
					}
				}
			}
		}

		// 读取所有节点
		Core_Extend::loadCache('node');

		Core_Extend::getSeo($this,array('title'=>isset($arrRole)?Q::L('角色权限','Controller').' - '.$arrRole['role_name']:Q::L('我的权限','Controller')));

		$this->assign('nRId',$nRId);
		$this->assign('arrAccessListall',$GLOBALS['_cache_']['node']);
		$this->assign('arrMyaccesslist',$arrMyaccesslist);
		$this->display('public+myrbac');
	}

}
