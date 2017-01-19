<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件校验控制器($$)*/

!defined('Q_PATH') && exit;

class FilecheckController extends AController{

	protected $_arrMd5data=array();
	
	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function index($sName=null,$bDisplay=true){
		// 读取文件校验相关信息
		$oCache=CacheModel::F('cache_key=?','filecheck')->getOne();
		$this->assign('oCache',$oCache);
		$this->display();
	}

	public function step2(){
		$this->assign('__WaitSecond__',2);
		$this->assign('__JumpUrl__',Q::U('filecheck/step3'));
		$this->S(Q::L('正在进行文件检验，请稍候','Controller').'...');
	}

	public function step3(){
		if(!$arrWindsforceFiles=@file(WINDSFORCE_PATH.'/System/admin/WindsforceFiles.md5')){
			$this->E(Q::L('系统用于检验文件的md5file字典不存在','Controller'));
		}
		
		$this->checkFiles('./','',0);
		$this->checkFiles('~@~/','',0);
		$this->checkFiles('~@~/install/','',1);
		$this->checkFiles('Public/','');
		$this->checkFiles('System/','',1,'WindsforceFiles.md5,FilecheckController.class.php');
		$this->checkFiles('user/attachment/','\.html',0);
		$this->checkFiles('user/avatar/','\.html',0);
		$this->checkFiles('user/database/','\.html',0);
		$this->checkFiles('user/language/','',1);
		$this->checkFiles('user/theme/','',1);
		$this->checkFiles('user/url/','',1);

		/** WindsForce 官方写入数据 && 用户请不要删除注释
		$sWindsforcefilesdata='';
		foreach($this->_arrMd5data as $sTempkey=>$sTempvalue){
			$sWindsforcefilesdata.=$sTempvalue.' *'.$sTempkey."\r\n";
		}

		if(!file_put_contents(WINDSFORCE_PATH.'/System/admin/WindsforceFiles.md5',$sWindsforcefilesdata)){
			$this->E('Can not write WindsforceFiles.md5!');
		} */

		// 记录验证时间
		$oCache=CacheModel::F('cache_key=?','filecheck')->getOne();
		if(empty($oCache['cache_key'])){
			Q::instance('CacheModel')->insertCache('filecheck',serialize(array('dateline'=>CURRENT_TIMESTAMP)));
		}else{
			$oCache->cache_value=serialize(array('dateline'=>CURRENT_TIMESTAMP));
			$oCache->save('update');
			if($oCache->isError()){
				$this->E($oCache->getErrorMessage());
			}
		}

		// md5file文件记录处理
		$arrMd5data=$this->_arrMd5data;
		$arrMd5datanew=$arrModifylist=array();

		foreach($arrWindsforceFiles as $sLine){
			$sFile=trim(substr($sLine,34));
			$arrMd5datanew[$sFile]=substr($sLine,0,32);
			if(isset($arrMd5data[$sFile]) && $arrMd5datanew[$sFile]!=$arrMd5data[$sFile]){
				$arrModifylist[$sFile]=$arrMd5data[$sFile];
			}

			if(isset($arrMd5data[$sFile])){
				$arrMd5datanew[$sFile]=$arrMd5data[$sFile];
			}
		}

		$nWeekbefore=CURRENT_TIMESTAMP-604800;

		$arrAddlist=@array_merge(@array_diff_assoc($arrMd5data,$arrMd5datanew));
		$arrDellist=@array_diff_assoc($arrMd5datanew,$arrMd5data);
		$arrModifylist=@array_merge(@array_diff_assoc($arrModifylist,$arrDellist));
		$arrShowlist=@array_merge($arrMd5data,$arrMd5datanew);
		
		$nDoubt=0;
		$arrDirlist=$arrDirlog=array();
		foreach($arrShowlist as $sFile=>$sMd5){
			$sDir=dirname($sFile);
			if(@array_key_exists($sFile,$arrModifylist)){
				$sFileststus='modify';
			} elseif(@array_key_exists($sFile,$arrDellist)){
				$sFileststus='del';
			} elseif(@array_key_exists($sFile,$arrAddlist)){
				$sFileststus='add';
			}else{
				$sFilemtime=@filemtime(WINDSFORCE_PATH.'/'.$sFile);
				if($sFilemtime>$nWeekbefore){
					$sFileststus='doubt';
					$nDoubt++;
				}else{
					$sFileststus='';
				}
			}

			if(file_exists(WINDSFORCE_PATH.'/'.$sFile)){
				$nFilemtime=@filemtime(WINDSFORCE_PATH.'/'.$sFile);
				$sFileststus && $arrDirlist[$sFileststus][$sDir][basename($sFile)]=array(number_format(filesize(WINDSFORCE_PATH.'/'.$sFile)).' Bytes',Core_Extend::timeFormat($nFilemtime));
			}else{
				$sFileststus && $arrDirlist[$sFileststus][$sDir][basename(WINDSFORCE_PATH.'/'.$sFile)]=array('','');
			}
		}

		$sResult=$sResultjs='';
		$nDirnum=0;
		foreach($arrDirlist as $sStatus=>$arrFilelist){
			$nDirnum++;
			$sClass=$sStatus=='modify'?'edited':($sStatus=='del'?'del':'unknown');
			
			$sResult.= '<tbody id="status_'.$sStatus.'" style="display:'.($sStatus!='modify'?'none':'').'">';
			foreach($arrFilelist as $sDir=>$arrFiles){
				$sResult.= '<tr><td colspan="4"><div class="ofolder">'.$sDir.'</div>';
				foreach($arrFiles as $sFilename=>$arrFile){
					$sResult.='<tr><td><span class="files">'.$sFilename.'</span></td><td>'.$arrFile[0].'&nbsp;&nbsp;</td><td>'.$arrFile[1].'</td><td><span class="'.$sClass.'">&nbsp;</span></td></tr>';
				}
			}

			$sResult.='</tbody>';
			$sResultjs.='$WF(\'status_'.$sStatus.'\').style.display=\'none\';';
		}

		$nModifiedfiles=count($arrModifylist);
		$nDeletedfiles=count($arrDellist);
		$nUnknownfiles=count($arrAddlist);
		$nDoubt=intval($nDoubt);

		$sResult.='<script>function showResult(o0) {'.$sResultjs.'$WF(\'status_\'+o0).style.display=\'\';}</script>';

		$this->assign('sResult',$sResult);
		$this->assign('nModifiedfiles',$nModifiedfiles);
		$this->assign('nDeletedfiles',$nDeletedfiles);
		$this->assign('nUnknownfiles',$nUnknownfiles);
		$this->assign('nDoubt',$nDoubt);
		$this->display();
	}
	
	protected function checkFiles($sCurrentdir,$sExt='',$nSub=1,$sSkip=''){
		$sDir=@opendir(WINDSFORCE_PATH.'/'.$sCurrentdir);
		$sExts='/('.$sExt.')$/i';
		$arrSkips=explode(',',$sSkip);

		while($sEntry=@readdir($sDir)){
			$sFile=$sCurrentdir.$sEntry;

			if($sEntry!='.' && $sEntry!= '..' && (($sExt && preg_match($sExts,$sEntry) || !$sExt) || $nSub && is_dir($sFile)) && !in_array($sEntry,$arrSkips)){
				if($nSub && is_dir(WINDSFORCE_PATH.'/'.$sFile)){
					self::checkFiles($sFile.'/',$sExt,$nSub,$sSkip);
				}else{ 
					if(is_dir(WINDSFORCE_PATH.'/'.$sFile)){
						$this->_arrMd5data[$sFile]=md5(WINDSFORCE_PATH.'/'.$sFile);
					}else{
						$this->_arrMd5data[$sFile]=md5_file(WINDSFORCE_PATH.'/'.$sFile);
					}
				}
			}
		}
	}

}
