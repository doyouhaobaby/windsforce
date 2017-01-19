<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   数据库备份处理控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入文件相关函数 */
require_once(Core_Extend::includeFile('function/File_Extend'));

class DatabaseController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function index($sModel=null,$bDisplay=true){
		$oDb=Db::RUN();

		$arrTables=$oDb->getConnect()->getCol("SHOW TABLES LIKE '".$GLOBALS['_commonConfig_']['DB_PREFIX']."%'");
		$nAllowMaxSize=C::returnBytes(ini_get('upload_max_filesize'));// 单位为字节
		$nAllowMaxSize=$nAllowMaxSize/1024;// 转换单位为 KB

		$nMask=File_Extend::fileModeInfo(WINDSFORCE_PATH.'/user/database');
		if($nMask===false){
			$this->assign('sWarning',Q::L('备份目录不存在%s','Controller',null,WINDSFORCE_PATH.'/user/database'));
		}elseif($nMask!=15){
			$sWarning=Q::L('文件夹 %s 权限警告：','Controller',null,WINDSFORCE_PATH.'/user/database');
			if(($nMask&1)<1){
				$sWarning.=Q::L('不可读','Controller');
			}

			if(($nMask&2)<1){
				$sWarning.=Q::L('不可写','Controller');
			}

			if(($nMask&4)< 1){
				$sWarning.=Q::L('不可增加','Controller');
			}

			if(($nMask&8)<1){
				$sWarning.=Q::L('不可修改','Controller');
			}
			$this->assign('sWarning',$sWarning);
		}

		$this->assign('arrTables',$arrTables);
		$this->assign('nVolSize',$nAllowMaxSize);
		$this->assign('sSqlName',DbBackup::getRandomName().'.sql');
		$this->display();
	}

	public function dumpsql(){
		$oDb=Db::RUN();

		$nMask=File_Extend::fileModeInfo(WINDSFORCE_PATH.'/user/database');
		if($nMask===false){
			$this->assign('sWarning',Q::L('备份目录不存在%s','Controller',null,WINDSFORCE_PATH.'/user/database'));
		}else if($nMask!=15){
			$sWarning=Q::L('文件夹 %s 权限警告：','Controller',null,WINDSFORCE_PATH.'/user/database');
			if(($nMask&1)<1){
				$sWarning.=Q::L('不可读','Controller');
			}

			if(($nMask&2)<1){
				$sWarning.=Q::L('不可写','Controller');
			}

			if(($nMask&4)<1){
				$sWarning.=Q::L('不可追加','Controller');
			}

			if(($nMask&8)<1){
				$sWarning.=Q::L('不可修改','Controller');
			}
			$this->assign('sWarning',$sWarning);
		}

		@set_time_limit(300);
		$oConnect=$oDb->getConnect();
		$oBackup=new DbBackup($oConnect);
		$sRunLog=WINDSFORCE_PATH.'/user/database/run.log';
		$sSqlFileName=Q::G('sql_file_name');
		if(empty($sSqlFileName)){
			$sSqlFileName=DbBackup::getRandomName();
		}else{
			$sSqlFileName=str_replace("0xa",'',trim($sSqlFileName));// 过滤 0xa 非法字符
			$nPos=strpos($sSqlFileName,'.sql');
			if($nPos!==false){
				$sSqlFileName=substr($sSqlFileName,0,$nPos);
			}
		}

		$nMaxSize=Q::G('vol_size');
		$nMaxSize=empty($nMaxSize)?0:intval($nMaxSize);
		$nVol=Q::G('vol');
		$nVol=empty($nVol)?1:intval($nVol);
		$bIsShort=Q::G('ext_insert');
		$bIsShort=$bIsShort==0?false:true;
		$oBackup->setIsShort($bIsShort);
		
		$nAllowMaxSize=intval(@ini_get('upload_max_filesize'));// 单位M
		if($nAllowMaxSize>0 && $nMaxSize>($nAllowMaxSize*1024)){
			$nMaxSize=$nAllowMaxSize*1024;// 单位K
		}

		if($nMaxSize>0){
			$oBackup->setMaxSize($nMaxSize*1024);
		}

		$sType=Q::G('type');
		$sType=empty($sType)?'':trim($sType);
		$arrTables=array();
		switch($sType){
			case 'full':
				$arrTemp=$oDb->getConnect()->getCol("SHOW TABLES LIKE '".$GLOBALS['_commonConfig_']['DB_PREFIX']."%'");
				foreach($arrTemp as $sTable){
					$arrTables[$sTable]=-1;
				}
				$oBackup->putTablesList($sRunLog,$arrTables);
				break;
			case 'custom':
				foreach(Q::G('customtables') as $sTable){
					$arrTables[$sTable]=-1;
				}
				$oBackup->putTablesList($sRunLog,$arrTables);
				break;
		}

		$arrTables=$oBackup->dumpTable($sRunLog,$nVol);
		if($arrTables===false){
			$this->E($oBackup->getErrorMessage());
		}

		if(empty($arrTables)){
			if($nVol>1){
				if(!@file_put_contents(WINDSFORCE_PATH.'/user/database/'.$sSqlFileName.'_'.$nVol.'.sql',$oBackup->getDumpSql())){
					$this->E(Q::L('备份文件写入失败%s','Controller',null,$sSqlFileName.'_'.$nVol.'.sql'));
				}

				$arrList=array();
				for($nI=1;$nI<=$nVol;$nI++){
					$arrList[]=array(
						'name'=>$sSqlFileName.'_'.$nI.'.sql',
						'href'=>__ROOT__.'/user/database/'.$sSqlFileName.'_'.$nI.'.sql'
					);
				}

				$arrMessage=array(
					'list'=>$arrList
				);

				if(is_file($sRunLog)){
					@unlink($sRunLog);
				}

				$this->sql_dump_message($arrMessage);
			}else{
				if(!@file_put_contents(WINDSFORCE_PATH.'/user/database/'.$sSqlFileName. '.sql',$oBackup->getDumpSql())){
					$this->E(Q::L('备份文件写入失败%s','Controller',null,$sSqlFileName.'_'.$nVol.'.sql'));
				};

				$arrList=array(
					array('name'=>$sSqlFileName.'.sql',
						'href'=>__ROOT__.'/user/database/'. $sSqlFileName.'.sql'
					)
				);
				$arrMessage=array(
					'list'=>$arrList
				);

				if(is_file($sRunLog)){
					@unlink($sRunLog);
				}

				$this->sql_dump_message($arrMessage);
			}
		}else{
			if(!@file_put_contents(WINDSFORCE_PATH.'/user/database/'.$sSqlFileName.'_'.$nVol.'.sql',$oBackup->getDumpSql())){
				$this->E(Q::L('备份文件写入失败%s','Controller',null,$sSqlFileName.'_'.$nVol.'.sql'));
			}

			$arrList=array(
				'sql_file_name'=>$sSqlFileName,
				'vol_size'=>$nMaxSize,
				'vol'=>$nVol+1,
				'ext_insert'=>$bIsShort==false?0:1,
			);

			$sLink=Q::U('database/dumpsql',$arrList);

			$arrMessage=array(
				'auto_link'=>$sLink,
				'auto_redirect'=>1,
				'done_file'=>$sSqlFileName.'_'.$nVol.'.sql',
				'list'=>$arrList
			);

			$this->sql_dump_message($arrMessage);
		}
	}

	private function sql_dump_message($arrMessage){
		$sBackMsg="";

		if(isset($arrMessage['auto_redirect']) && $arrMessage['auto_redirect']){
			$sBackMsg="<a href=\"{$arrMessage['auto_link']}\">{$arrMessage['done_file']}</a>";
			$this->assign('__JumpUrl__',$arrMessage['auto_link']);
			$this->assign('__WaitSecond__',3);
		}else{
			if(is_array($arrMessage['list'])){
				foreach($arrMessage['list'] as $arrFile){
					$sBackMsg.="<a href=\"{$arrFile['href']}\">{$arrFile['name']}</a><br/>";
				}
			}
			$this->assign('__JumpUrl__',Q::U('database/restore'));
			$this->assign('__WaitSecond__',5);
		}

		$this->S($sBackMsg);
	}

	public function runsql(){
		$sSql=Q::G('sql');
		if(!empty($sSql)){
			$this->assign('sSql',$sSql);
			$this->assign_sql($sSql);
		}

		$this->display();
	}

	private function assign_sql($sSql){
		$oDb=Db::RUN();

		$sSql=stripslashes($sSql);
		$sSql=str_replace("\r",'',$sSql);
		$arrQueryItems=explode(";\n",$sSql);
		$arrQueryItems=array_filter($arrQueryItems,'strlen');
		if(count($arrQueryItems)>1){
			foreach($arrQueryItems as $sKey=>$sValue){
				if($oDb->getConnect()->query($sValue)){
					$this->assign('nType',1);
				}else{
					$this->assign('nType',0);
					return;
				}
			}
			return;
		}

		if(preg_match("/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i",$sSql)){// 执行，但不返回结果型
			try{
				$oDb->getConnect()->query($sSql);
				$this->assign('nType',1);
			}catch(Exception $e){
				$this->assign('nType',-1);
				$this->assign('sError',$e->getMessage());
			}
		}else{
			try{
				$arrData=$oDb->getConnect()->getAllRows($sSql);

				$sResult='';
				if(is_array($arrData) && isset($arrData[0])){
					$sResult="<table class=\"tablesorter\" id=\"checkList\"> \n<thead> \n<tr>";
					$arrKeys=array_keys($arrData[0]);
					$nNum=count($arrKeys);
					for($nI=0;$nI < $nNum;$nI++){
						$sResult.="<th>".$arrKeys[$nI]."</th>\n";
					}
					$sResult.="</tr> \n</thead>\n<tbody>\n";
					foreach($arrData as $arrData1){
						$sResult.="<tr>\n";
						foreach($arrData1 as $sValue){
							$sResult.="<td>".$sValue."</td>";
						}
						$sResult.="</tr>\n";
					}
					$sResult.="</tbody></table>\n";
				}else{
					$sResult="<center><h3>".Q::L('没有发现任何记录','Controller')."</h3></center>";
				}
				$this->assign('nType',2);
				$this->assign('sResult',$sResult);
			}catch(Exception $e){
				$this->assign('nType',-1);
				$this->assign('sError',$e->getMessage());
			}
		}
	}

	public function optimize(){
		$oDb=Db::RUN();

		$nDbVer=$oDb->getConnect()->getVersion();
		$sSql="SHOW TABLE STATUS LIKE '" .$GLOBALS['_commonConfig_']['DB_PREFIX']. "%'";
		$nNum=0;
		$arrList=array();
		$arrReuslt=$oDb->getConnect()->getAllRows($sSql);
		foreach($arrReuslt as $arrRow){
			$sType=$nDbVer>='4.1'?$arrRow['Engine']:$arrRow['Type'];

			if(in_array($sType,array('InnoDB','MEMORY'))){
				$arrRes['Msg_text']='Ignore';
				$arrRow['Data_free']='Ignore';
			}else{
				$arrRes=$oDb->getConnect()->getRow('CHECK TABLE '.$arrRow['Name'],null,false);
				$nNum+=$arrRow['Data_free'];
			}
			
			$sCharset=$nDbVer >='4.1'?$arrRow['Collation']:'N/A';
			$arrList[]=array('table'=>$arrRow['Name'],'type'=>$sType,'rec_num'=>$arrRow['Rows'],'rec_size'=>sprintf(" %.2f KB",$arrRow['Data_length']/1024),'rec_index'=>$arrRow['Index_length'], 'rec_chip'=>$arrRow['Data_free'],'status'=>$arrRes['Msg_text']=='OK'?'OK':'<span style="color:red;">'.$arrRes['Msg_text'].'</span>','charset'=>$sCharset);
		}
		unset($arrReuslt,$sCharset,$sType);

		$this->assign('arrList',$arrList);
		$this->assign('nNum',$nNum);
		$this->display();
	}

	public function run_optimize(){
		$oDb=Db::RUN();

		$arrTables=$oDb->getConnect()->getCol("SHOW TABLES LIKE '".$GLOBALS['_commonConfig_']['DB_PREFIX']."%'");
		$sResult='';
		foreach($arrTables as $sTable){
			if(($arrRow=$oDb->getConnect()->getRow('OPTIMIZE TABLE '.$sTable,null,false))!==false){
				if($arrRow['Msg_type']=='error' && strpos($arrRow['Msg_text'],'repair')!==false){
					$sResult.=Q::L('优化数据库表%s失败','Controller',null,$sTable).'<br/>';
					if($oDb->getConnect()->query('REPAIR TABLE '.$sTable)){
						$sResult.=Q::L('优化失败后，尝试修复数据库%s成功','Controller',null,$sTable).'<br/>';
					}else{
						$sResult.=Q::L('优化失败后，尝试修复数据库%s失败','Controller',null,$sTable).'<br/>';
					}
				}else{
					$sResult.=Q::L('优化数据库表%s成功','Controller',null,$sTable).'<br/>';
				}

				foreach(Q::G('do','P') as $sDo){
					if($oDb->query($sDo.' TABLE '.$sTable)){
						$sResult.=Q::L('数据库表%s成功','Controller',null,$sTable).'<br/>';
					}else{
						$sResult.=Q::L('数据库表%s失败','Controller',null,$sTable).'<br/>';
					}
				}
				$sResult.='<br/><br/>';
			}
		}
		$this->assign('__WaitSecond__',10);

		$this->S(Q::L('数据表优化成功，共清理碎片%d','Controller',null,Q::G('num','P'))."<br/><br/>".Q::L('附加信息','Controller').": ".$sResult);
	}

	public function restore(){
		$arrList=array();

		$nMask=File_Extend::fileModeInfo(WINDSFORCE_PATH.'/user/database');
		if($nMask===false){
			$this->assign('sWarning',Q::L('备份目录不存在%s','Controller',null,WINDSFORCE_PATH.'/user/database'));
		}elseif(($nMask&1)<1){
			$this->assign('sWarning',Q::L('不可读','Controller'));
		}else{
			$arrRealList=array();

			$hFolder=opendir(WINDSFORCE_PATH.'/user/database');
			while(($sFile=readdir($hFolder))!==false){
				if(strpos($sFile,'.sql')!==false){
					$arrRealList[]=$sFile;
				}
			}

			natsort($arrRealList);
			$arrMatch=array();
			foreach($arrRealList as $sFile){
				if(preg_match('/_([0-9])+\.sql$/',$sFile,$arrMatch)){
					if($arrMatch[1]==1){
						$nMark=1;
					}else{
						$nMark=2;
					}
				}else{
					$nMark=0;
				}

				$nFileSize=filesize(WINDSFORCE_PATH.'/user/database/'.$sFile);
				$arrInfo=DbBackup::getHead(WINDSFORCE_PATH.'/user/database/'.$sFile);
				
				$arrList[]=array(
					'name'=>$sFile,
					'add_time'=>isset($arrInfo['date'])?$arrInfo['date']:'Nav',
					'vol'=>isset($arrInfo['vol'])?$arrInfo['vol']:1,
					'file_size'=>C::changeFileSize($nFileSize),
					'mark'=>$nMark
				);
			}
		}
		$this->assign('arrList',$arrList);

		$this->display();
	}

	public function remove(){
		$arrFile=Q::G('file');

		if(!empty($arrFile)){
			$arrMFile=array();//多卷文件
			$arrSFile=array();//单卷文件
			foreach($arrFile as $sFile){
				if(preg_match('/_[0-9]+\.sql$/',$sFile)){
					$arrMFile[]=substr($sFile,0,strrpos($sFile,'_'));
				}else{
					$arrSFile[]=$sFile;
				}
			}

			if($arrMFile){
				$arrMFile=array_unique($arrMFile);
				$arrRealFile=array();
				$hFolder=opendir(WINDSFORCE_PATH.'/user/database');
				while(($sFile=readdir($hFolder))!==false){
					if(preg_match('/_[0-9]+\.sql$/',$sFile)&& is_file(WINDSFORCE_PATH.'/user/database/'.$sFile)){
						$arrRealFile[]=$sFile;
					}
				}

				foreach($arrRealFile as $sFile){
					$sShortFile=substr($sFile,0,strrpos($sFile,'_'));
					if(in_array($sShortFile,$arrMFile)){
						if(is_file(WINDSFORCE_PATH.'/user/database/'.$sFile)){
							@unlink(WINDSFORCE_PATH.'/user/database/'.$sFile);
						}
					}
				}
			}

			if($arrSFile){
				foreach($arrSFile as $sFile){
					if(is_file(WINDSFORCE_PATH.'/user/database/'. $sFile)){
						@unlink(WINDSFORCE_PATH.'/user/database/'. $sFile);
					}
				}
			}

			$this->S(Q::L('删除备份文件成功','Controller'));
		}else{
			$this->E(Q::L('你没有选择任何文件','Controller'));
		}
	}

	public function import(){
		$oDb=Db::RUN();

		$bIsContrim=Q::G('confirm');
		$bIsConfirm=empty($bIsContrim)?false:true;
		$sFileName=Q::G('file_name');
		$sFileName=empty($sFileName)?'':trim($sFileName);

		@set_time_limit(300);
		if(preg_match('/_[0-9]+\.sql$/',$sFileName)){
			if($bIsConfirm==false){
				$sUrl=Q::U('database/import?confirm=1&file_name='. $sFileName);
				$this->assign("__JumpUrl__",$sUrl);
				$this->assign('__WaitSecond__',60);
				$this->S(Q::L('你确定要导入?','Controller')."&nbsp;<a href='".$sUrl."'>".Q::L('确定','Controller')."</a>");
			}

			$sShortName=substr($sFileName,0,strrpos($sFileName,'_'));

			$arrRealFile=array();
			$hFolder=opendir(WINDSFORCE_PATH.'/user/database');
			while(($sFile=readdir($hFolder))!==false){
				if(is_file(WINDSFORCE_PATH.'/user/database/'.$sFile) && preg_match('/_[0-9]+\.sql$/',$sFile)){
					$arrRealFile[]=$sFile;
				}
			}

			$arrPostList=array();
			foreach($arrRealFile as $sFile){
				$sTmpName=substr($sFile,0,strrpos($sFile,'_'));
				if($sTmpName==$sShortName){
					$arrPostList[]=$sFile;
				}
			}

			natsort($arrPostList);
			foreach($arrPostList as $sFile){
				$arrInfo=DbBackup::getHead(WINDSFORCE_PATH.'/user/database/'. $sFile);
				if(!$this->sql_import(WINDSFORCE_PATH.'/user/database/'. $sFile)){
					$this->E(Q::L('导入数据库备份文件失败','Controller'));
				}
			}
			$this->assign("__JumpUrl__",Q::U('database/restore'));
			$this->S(Q::L('数据导入成功','Controller'));
		}else{
			$arrInfo=DbBackup::getHead(WINDSFORCE_PATH.'/user/database/'. $sFileName);
			if($this->sql_import(WINDSFORCE_PATH.'/user/database/'. $sFileName)){
				$this->assign("__JumpUrl__",Q::U('database/restore'));
				$this->S(Q::L('数据导入成功','Controller'));
			}else{
				$this->E(Q::L('导入数据库备份文件失败','Controller'));
			}
		}
	}

	public function upload_sql(){
		$oDb=Db::RUN();
		$sSqlFile=WINDSFORCE_PATH.'/user/database/upload_database_bak.sql';
		$sSqlVerConfirm=Q::G('sql_ver_confirm');
		if(empty($sSqlVerConfirm)){
			$arrSqlfile=Q::G('sqlfile','F');
			if(empty($arrSqlfile)){
				$this->E(Q::L('你没有选择任何文件','Controller'));
			}

			if((isset($arrSqlfile['error'])
				&& $arrSqlfile['error'] > 0)
				||(!isset($arrSqlfile['error'])
				&& $arrSqlfile['tmp_name']=='none')){
				$this->E(Q::L('上传文件失败','Controller'));
			}

			if($arrSqlfile['type']=='application/x-zip-compressed'){
				$this->E(Q::L('不能是zip格式','Controller'));
			}

			if(!preg_match("/\.sql$/i",$arrSqlfile['name'])){
				$this->E(Q::L('不是sql格式','Controller'));
			}

			if(is_file($sSqlFile)){
				unlink($sSqlFile);
			}

			if(!move_uploaded_file($arrSqlfile['tmp_name'],$sSqlFile)){
				$this->E(Q::L('文件移动失败','Controller'));
			}
		}

		// 获取sql文件头部信息
		$arrSqlInfo=DbBackup::getHead($sSqlFile);
		if(empty($sSqlVerConfirm)){// 检查数据库版本是否正确
			if(empty($arrSqlInfo['database_ver'])){
				$this->E(Q::L('没有确定数据库版本','Controller'));
			}else{
				$nSqlVer=$oDb->getConnect()->getVersion();
				if($arrSqlInfo['database_ver'] !=$nSqlVer){
					$sMessage="<a href='".Q::U('database/upload_sql?sql_ver_confrim=1')."'>".Q::L('重试','Controller')."</a></br>< href='".Q::U('database/restore')."'>".Q::L('返回','Controller')."</a>";
					$this->E($sMessage);
				}
			}
		}

		@set_time_limit(300);
		if($this->sql_import($sSqlFile)){
			if(is_file($sSqlFile)){
				unlink($sSqlFile);
			}

			$this->S(Q::L('数据库导入成功','Controller'));
		}else{
			if(is_file($sSqlFile)){
				unlink($sSqlFile);
			}

			$this->E(Q::L('数据库导入失败','Controller'));
		}
	}

	private function sql_import($sSqlFile){
		$oDb=Db::RUN();

		$nDbVer=$oDb->getConnect()->getVersion();
		$sSqlStr=array_filter(file($sSqlFile),'removeComment');
		$sSqlStr=str_replace("\r",'',implode('',$sSqlStr));
		$arrRet=explode(";\n",$sSqlStr);
		$nRetCount=count($arrRet);
		if($nDbVer>'4.1'){
			for($nI=0;$nI<$nRetCount;$nI++){
				$arrRet[$nI]=trim($arrRet[$nI]," \r\n;");//剔除多余信息
				if(!empty($arrRet[$nI])){
					if((strpos($arrRet[$nI],'CREATE TABLE')!==false) && (strpos($arrRet[$nI],'DEFAULT CHARSET='. str_replace('-','',$GLOBALS['_commonConfig_']['DB_CHAR']))===false)){
						// 建表时缺 DEFAULT CHARSET=utf8
						$arrRet[$nI]=$arrRet[$nI].'DEFAULT CHARSET='. str_replace('-','',$GLOBALS['_commonConfig_']['DB_CHAR']);
					}

					$oDb->getConnect()->query($arrRet[$nI]);
				}
			}
		}else{
			for($nI=0;$nI<$nRetCount;$nI++){
				$arrRet[$nI]=trim($arrRet[$nI]," \r\n;");//剔除多余信息
				if((strpos($arrRet[$nI],'CREATE TABLE')!==false)&&(strpos($arrRet[$nI],'DEFAULT CHARSET='. str_replace('-','',$GLOBALS['_commonConfig_']['DB_CHAR']))!==false)){
					$arrRet[$nI]=str_replace('DEFAULT CHARSET='. str_replace('-','',$GLOBALS['_commonConfig_']['DB_CHAR']),'',$arrRet[$nI]);
				}

				if(!empty($arrRet[$nI])){
					$oDb->getConnect()->query($arrRet[$nI]);
				}
			}
		}
		return true;
	}

}

function removeComment($sVar){
	return(substr($sVar,0,2)!='--');
}
