<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   日志类($$)*/

!defined('Q_PATH') && exit;

class Log{

	const EMERG='EMERG';// 严重错误: 导致系统崩溃无法使用
	const ALERT='ALERT';// 警戒性错误: 必须被立即修改的错误
	const CRIT='CRIT';// 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
	const ERR='ERR';// 一般错误: 一般性错误
	const WARN='WARN';// 警告性错误: 需要发出警告的错误
	const NOTICE='NOTIC';// 通知: 程序可以运行但是还不够完美的错误
	const INFO='INFO';// 信息: 程序输出信息
	const DEBUG='DEBUG';// 调试: 调试信息
	const EXCEPTION='EXCEPTION';//异常日志记录
	const SQL='SQL';// SQL：SQL语句 注意只在调试模式开启时有效
	const SYSTEM=0;
	const MAIL=1;
	const TCP=2;
	const FILE=3;
	static public $_arrLog=array();
	static public $_sFormat='[ c ]';
	static public $_sFileNameFormat='Y-m-d';
	static public $_sLogFileSize=2097152;
	static public $_nLogCount=0;
	static public $_sFilePath;

	static public function R($sMessage,$sLevel=self::ERR,$bRecord=false){
		// 只记录系统允许的日志级别
		if($bRecord || in_array($sLevel,explode('|',$GLOBALS['_commonConfig_']['LOG_RECORD_LEVEL']))){
			$sNow=self::getFormatTime();// 获取格式化时间
			self::$_arrLog[]="{$sNow} {$sLevel}: {$sMessage}\r\n";
		}
		
		// 记录日志总数
		self::$_nLogCount=count(self::$_arrLog);
	}

	static public function S($sType=self::FILE,$sFilePath='',$sExtra=''){
		// 获取日志路径
		$sFilePath=self::getLogFilePath($sFilePath);
		
		// 文件方式记录日志信息,检测如果超过系统大小，则生成新的日志
		if(self::FILE==$sType){
			self::newLogFile($sFilePath);
		}
		
		// 日志记录保存
		error_log(implode("",self::$_arrLog),$sType,$sFilePath ,$sExtra);
		
		// 保存后清空日志缓存
		self::clearItem();
	}

	static public function W($sMessage,$sLevel=self::ERR,$sType=self::FILE,$sFilePath='',$sExtra=''){
		// 获取格式化时间
		$sNow=self::getFormatTime();
		
		// 获取日志路径
		$sFilePath=self::getLogFilePath($sFilePath);
		
		// 文件方式记录日志信息,检测如果超过系统大小，则生成新的日志
		if(self::FILE == $sType){
			self::newLogFile($sFilePath);
		}
		
		// 保存日志数据
		error_log("{$sNow} {$sLevel}: {$sMessage}\r\n",$sType,$sFilePath,$sExtra);
	}

	static public function clearItem(){
		self::$_nLogCount=count(self::$_arrLog);
		self::$_arrLog=array();
		return self::$_nLogCount;
	}

	static public function deleteLogFile(){
		// 获取日志目录
		$sDir=dirname(self::getLogFilePath());
		
		// 检查目录是否有效
		if(!is_dir($sDir)){
			Q::E('$sDir is not a dir');
		}
		@rmdir($sDir);
	}

	static public function newLogFile($sFilePath){
		// 如果不是文件，则创建
		if(!is_file($sFilePath) && !is_dir(dirname($sFilePath)) && !C::makeDir(dirname($sFilePath))){
			Q::E(Q::L('无法创建日志文件：“%s”','__QEEPHP__@Q',null,$sFilePath));
		}

		// 日志大小限制
		$nLogFileSize=$GLOBALS['_commonConfig_']['LOG_FILE_SIZE']?$GLOBALS['_commonConfig_']['LOG_FILE_SIZE']:self::$_sLogFileSize;
		
		// 检测日志文件大小，超过配置大小则备份日志文件重新生成
		if(is_file($sFilePath) && floor($nLogFileSize)<=filesize($sFilePath)){
			rename($sFilePath,dirname($sFilePath).'/'.CURRENT_TIMESTAMP.'-'.basename($sFilePath));
		}
	}

	static public function getLogFilePath($sFilePath=''){
		// 不存在路径，则直接使用项目路径
		if(empty($sFilePath)){
			self::$_sFilePath=APP_RUNTIME_PATH.'/Log/'.date(self::$_sFileNameFormat).".log";
		}

		return self::$_sFilePath;
	}

	static public function getFormatTime(){
		return $sNow=date(self::$_sFormat);
	}

	static public function getLogCount(){
		return count(self::$_nLogCount);
	}

}
