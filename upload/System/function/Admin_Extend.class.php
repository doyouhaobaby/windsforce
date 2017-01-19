<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台相关函数($$)*/

!defined('Q_PATH') && exit;

@set_time_limit(0);

class Admin_Extend{

	static public function sortField($sName){
		Core_Extend::sortField($sName);
	}
	
	static public function base($arrPars=array(),$sExtend=''){
		return Q::U("app/config?id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}

	static public function soryBy($sField,$sSort,$arrPars=array(),$sExtend=''){
		return Q::U("app/config?id=".$_GET['id']."&order_={$sField}&sort_={$sSort}".($sExtend?'&'.$sExtend:''),$arrPars,$sExtend);
	}

	static public function edit($arrPars=array(),$sExtend=''){
		return Q::U("app/config?action=edit&id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}
	
	static public function delete($arrPars=array(),$sExtend=''){
		return Q::U("app/config?action=foreverdelete&id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}
	
	static public function insert($arrPars=array(),$sExtend=''){
		return Q::U("app/config?action=insert&id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}
	
	static public function update($arrPars=array(),$sExtend=''){
		return Q::U("app/config?action=update&id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}
	
	static public function add($arrPars=array(),$sExtend=''){
		return Q::U("app/config?action=add&id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}
	
	static public function index($arrPars=array(),$sExtend=''){
		return Q::U("app/config?id=".$_GET['id'].($sExtend?'&'.$sExtend:''),$arrPars);
	}

	static public function dateColor($date){
		$date=date('Ymd',$date);
		$sNow=date('Ymd',time());
	
		if($date>=$sNow){
			return 'todayDatecolor';
		}elseif(($sNow-$date)==1){
			return 'yesterdayDatecolor';
		}else{
			return 'otherDatecolor';
		}
	}

	static public function getStatus($nStatus,$bReturnText=false){
		$arrTitle=array(
			0=>Q::L('待审','__COMMON_LANG__@Common'),
			1=>Q::L('通过','__COMMON_LANG__@Common'),
			2=>Q::L('拒绝','__COMMON_LANG__@Common'),
			9=>Q::L('回收站','__COMMON_LANG__@Common'),
			3=>Q::L('管理员关闭','__COMMON_LANG__@Common'),
			11=>Q::L('修改后提交','__COMMON_LANG__@Common'),
			12=>Q::L('结束','__COMMON_LANG__@Common'),
			13=>Q::L('用户关闭','__COMMON_LANG__@Common'),
		);

		if(array_key_exists($nStatus,$arrTitle)){
			$sTitle=$arrTitle[$nStatus];
		}else{
			$sTitle=Q::L('未知','__COMMON_LANG__@Common');
		}

		if($bReturnText===true){
			return $sTitle;
		}

		switch($nStatus){
			case 0:
			case 3:
			case 12:
				$sImg='no.gif';
				break;
			case 11:
				$sImg='edit_submit.png';
				break;
			case 2:
				$sImg='icn_trash.png';
				break;
			case 9:
				$sImg='recycle.png';
				break;
			case 13:
				$sImg='close_icon.png';
				break;
			case 1:
				$sImg='yes.gif';
				break;
		}

		return '<span title="'.$sTitle.'"><img src="'.At::path($sImg).'"/></span>';
	}

	static public function runQuery($sSql){
		$sTabelPrefix=$GLOBALS['_commonConfig_']['DB_PREFIX'];
		$sDbCharset=$GLOBALS['_commonConfig_']['DB_CHAR'];

		$sSql=str_replace(array(' windsforce_',' `windsforce_',' prefix_',' `prefix_',' #@__'),array(' {WINDSFORCE}',' `{WINDSFORCE}',' {WINDSFORCE}',' `{WINDSFORCE}',' {WINDSFORCE}'),$sSql);
		$sSql=str_replace("\r","\n",str_replace(array(' {WINDSFORCE}',' `{WINDSFORCE}'),array(' '.$sTabelPrefix,' `'.$sTabelPrefix),$sSql));

		$arrResult=array();
		$nNum=0;
		foreach(explode(";\n",trim($sSql)) as $sQuery){
			$arrQueries=explode("\n",trim($sQuery));
			foreach($arrQueries as $sQuery){
				if(isset($arrResult[$nNum])){
					$arrResult[$nNum].=(isset($sQuery[0]) && $sQuery[0]=='#') || (isset($sQuery[0]) && isset($sQuery[1]) && $sQuery[0].$sQuery[1]=='--')?'':$sQuery;
				}else{
					$arrResult[$nNum]=(isset($sQuery[0]) && $sQuery[0]=='#') || (isset($sQuery[0]) && isset($sQuery[1]) && $sQuery[0].$sQuery[1]=='--')?'':$sQuery;
				}
			}
			$nNum++;
		}
		unset($sSql);

		$oDb=Db::RUN();
		foreach($arrResult as $sQuery){
			$sQuery=trim($sQuery);
			if($sQuery){
				if(substr($sQuery,0,12)=='CREATE TABLE'){
					$sName=preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1",$sQuery);
					$oDb->query(self::createTable($sQuery,$sDbCharset));
				}else{
					$oDb->query($sQuery);
				}
			}
		}
	}

	static public function createTable($sSql,$sDbCharset){
		$sType=strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2",$sSql));
		$sType=in_array($sType,array('MYISAM','HEAP'))?$sType:'MYISAM';

		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU","\\1",$sSql).(mysql_get_server_info()>'4.1'?" ENGINE={$sType} DEFAULT CHARSET={$sDbCharset}":" TYPE={$sType}");
	}

	static public function testWrite($sPath){
		$sFile='WindsForce.txt';

		$sPath=preg_replace("#\/$#",'',$sPath);
		$hFp=@fopen($sPath.'/'.$sFile,'w');
		if(!$hFp){
			return false;
		}else{
			fclose($hFp);
			$bRs=@unlink($sPath.'/'.$sFile);
			if($bRs){
				return true;
			}else{
				return false;
			}
		}
	}

	static public function template($sApp,$sTemplate,$sTheme=null){
		if(empty($sTheme)){
			$sTemplate=TEMPLATE_NAME.'/'.$sTemplate;
		}else{
			$sTemplate=$sTheme.'/'.$sTemplate;
		}

		$sUrl=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Theme/Admin/'.$sTemplate.'.html';
		if(is_file($sUrl)){
			return $sUrl;
		}

		if(defined('QEEPHP_TEMPLATE_BASE') && empty($sTheme) && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖模板 兼容性分析
			$sUrlTry=str_replace('/Theme/Admin/'.TEMPLATE_NAME.'/','/Theme/Admin/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/',$sUrl);
			if(is_file($sUrlTry)){
				return $sUrlTry;
			}
		}

		if(empty($sTheme) && 'Default'!==TEMPLATE_NAME){// Default模板 兼容性分析
			$sUrlTry=str_replace('/Theme/Admin/'.TEMPLATE_NAME.'/','/Theme/Admin/Default/',$sUrl);
			if(is_file($sUrlTry)){
				return $sUrlTry;
			}
		}

		Q::E(sprintf('Template File %s is not exist',$sUrl));
	}

	static public function installApp($sApp){
		$nSqlprotected=intval(Q::G('sqlprotected','G'));
		if($nSqlprotected==1){
			return;
		}
		
		if(empty($sApp)){
			return false;
		}

		// 表结构
		$sInstallapptable=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Static/Sql/Install/windsforce.table.sql';
		if(is_file($sInstallapptable)){
			$sContent=file_get_contents($sInstallapptable);
			self::runQuery($sContent);
		}

		// 数据
		$sLanguage=Q::cookie('language');
		if(empty($sLanguage)){
			$sLanguage='Zh-cn';
		}

		$sInstallappdata=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Static/Sql/Install/'.$sLanguage.'/windsforce.data.sql';
		if(!is_file($sInstallappdata)){
			$sInstallappdata=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Static/Sql/Install/Zh-cn/windsforce.data.sql';
		}

		if(is_file($sInstallappdata)){
			$sContent=file_get_contents($sInstallappdata);
			self::runQuery($sContent);
		}
	}

	static public function uninstallApp($sApp){
		$nSqlprotected=intval(Q::G('sqlprotected','G'));
		if($nSqlprotected==1){
			return;
		}
		
		if(empty($sApp)){
			return;
		}

		$sUninstallapp=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Static/Sql/Install/windsforce.delete.sql';
		if(is_file($sUninstallapp)){
			$sContent=file_get_contents($sUninstallapp);
			self::runQuery($sContent);
		}
	}

	static public function updateApp($sApp){
		$nSqlprotected=intval(Q::G('sqlprotected','G'));
		if($nSqlprotected==1){
			return;
		}

		if(empty($sApp)){
			return;
		}
	}

}
