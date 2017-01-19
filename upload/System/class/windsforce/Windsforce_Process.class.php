<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   �ƻ��������� && Modify From Discuz!($$)*/

!defined('Q_PATH') && exit;

class Windsforce_Process{

	public static function isLocked($sProcess,$nTtl=0){
		$nTtl=$nTtl<1?600:intval($nTtl);
		return self::status_('get',$sProcess) || self::find_($sProcess,$nTtl);
	}

	public static function unLock($sProcess){
		self::status_('rm',$sProcess);
		self::cmd_('rm',$sProcess);
	}

	private static function status_($sAction,$sProcess){
		static $arrPlist=array();

		switch($sAction){
			case 'set':
				$arrPlist[$sProcess]=true;
				break;
			case 'get':
				return !empty($arrPlist[$sProcess]);
				break;
			case 'rm':
				$arrPlist[$sProcess]=null;
				break;
			case 'clear':
				$arrPlist=array();
				break;
		}

		return true;
	}

	private static function find_($sName,$nTtl){
		if(!self::cmd_('get',$sName)){
			self::cmd_('set',$sName,$nTtl);
			$bRet=false;
		}else{
			$bRet=true;
		}

		self::status_('set',$sName);

		return $bRet;
	}

	private static function cmd_($sCmd,$sName,$nTtl=0){
		static $bAllowmem;

		if($bAllowmem===null){
			$sMc=Core_Extend::memory('check');
			$bAllowmem=$sMc=='MemcacheCache';
		}

		if($bAllowmem){
			return self::processcmdMemory_($sCmd,$sName,$nTtl);
		}else{
			return self::processcmdDb_($sCmd,$sName,$nTtl);
		}
	}

	private static function processcmdMemory_($sCmd,$sName,$nTtl=0){
		$sRet='';

		switch ($sCmd){
			case 'set':
				$sRet=Core_Extend::memory('set','process_lock_'.$sName,time()+$nTtl,1);
				break;
			case 'get' :
				$sRet=Core_Extend::memory('get','process_lock_'.$sName);
				break;
			case 'rm' :
				$sRet=Core_Extend::memory('rm', 'process_lock_'.$sName);
		}

		return $sRet;
	}

	private static function processcmdDb_($sCmd,$sName,$nTtl=0){
		$bRet='';

		switch($sCmd){
			case 'set':
				$oProcess=new ProcessModel();
				$oProcess->process_id=$sName;
				$oProcess->process_expiry=time()+$nTtl;
				$oProcess->save('replace');
			
				$bRet=true;
				break;
			case 'get':
				$oProcess=ProcessModel::F('process_id=?',$sName)->getOne();
				if(empty($oProcess['process_id']) || $oProcess['process_expiry']<time()){
					$bRet=false;
				}else{
					$bRet=true;
				}
				break;
			case 'rm':
				$bRet=Q::instance('ProcessModel')->deleteProcess($sName,time());
				break;
		}

		return $bRet;
	}

}
