<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   计划任务 && Modify From Discuz!($$)*/

!defined('Q_PATH') && exit;

/** 导入进程 */
if(!Q::classExists('Windsforce_Process')){
	require_once(Core_Extend::includeFile('class/windsforce/Windsforce_Process'));
}

class Windsforce_Cron{

	public static function RUN($nCronid=0){
		$oCron=$nCronid?CronModel::F('cron_id=? AND cron_status=1',$nCronid)->getOne():Q::instance('CronModel')->fetchNextrun(CURRENT_TIMESTAMP);

		$sProcessname='WINDSFORCE_CRON_'.(empty($oCron['cron_id'])?'CHECKER':$oCron['cron_id']);

		if($nCronid && !empty($oCron['cron_id'])){
			Windsforce_Process::unLock($sProcessname);
		}

		if(Windsforce_Process::isLocked($sProcessname,600)){
			return false;
		}

		if(!empty($oCron['cron_id'])){
			$oCron['cron_filename']=str_replace(array('..','/','\\'),'',$oCron['cron_filename']);
			$arrFile=explode('@',$oCron['cron_filename']);
			if(count($arrFile)>1){
				if($oCron['cron_type']=='app'){
					$sCronfile=in_array($arrFile[0],$GLOBALS['_cache_']['app'])?WINDSFORCE_PATH.'/System/app/'.$arrFile[0].'/App/Class/Extension/cron/'.$arrFile[1]:'';
				}elseif($oCron['cron_type']=='plugin'){
					$sCronfile=in_array($arrFile[0],array('helloworld'))?WINDSFORCE_PATH.'/System/plugin/'.$arrFile[0].'/cron/'.$arrFile[1]:'';
				}
			}else{
				if($oCron['cron_type']=='user'){
					$sCronfile=WINDSFORCE_PATH.'/user/cron/'.$oCron['cron_filename'];
				}else{
					$sCronfile=WINDSFORCE_PATH.'/System/cron/'.$oCron['cron_filename'];
				}
			}

			if($sCronfile){
				$oCron['cron_minute']=explode("\t",$oCron['cron_minute']);
				self::setNextime($oCron);

				@set_time_limit(1000);
				@ignore_user_abort(TRUE);

				if(!@include $sCronfile){
					return false;
				}
			}
		}

		self::nextcron();
		Windsforce_Process::unLock($sProcessname);
		return true;
	}

	private static function nextcron(){
		$oCron=Q::instance('CronModel')->fetchNextcron();

		if(!empty($oCron['cron_id']) && isset($oCron['cron_nextrun'])){
			Core_Extend::saveSyscache('cronnextrun',$oCron['cron_nextrun']);
		}else{
			Core_Extend::saveSyscache('cronnextrun',CURRENT_TIMESTAMP);
		}

		return true;
	}

	private static function setNextime($oCron){
		if(empty($oCron['cron_id'])){
			return FALSE;
		}

		list($nYearnow,$nMonthnow,$nDaynow,$nWeekdaynow,$nHournow,$nMinutenow)=explode('-',date('Y-m-d-w-H-i',CURRENT_TIMESTAMP));

		if($oCron['cron_weekday']==-1){
			if($oCron['cron_day']==-1){
				$nFirstday=$nDaynow;
				$nSecondday=$nDaynow+1;
			}else{
				$nFirstday=$oCron['cron_day'];
				$nSecondday=$oCron['cron_day']+date('t',CURRENT_TIMESTAMP);
			}
		}else{
			$nFirstday=$nDaynow+($oCron['cron_weekday']-$nWeekdaynow);
			$nSecondday=$nFirstday+7;
		}

		if($nFirstday<$nDaynow){
			$nFirstday=$nSecondday;
		}

		if($nFirstday==$nDaynow){
			$arrTodaytime=self::todayNextrun($oCron);
			if($arrTodaytime['cron_hour']==-1 && $arrTodaytime['cron_minute']==-1){
				$oCron['cron_day']=$nSecondday;
				$arrNexttime=self::todaynextrun($oCron,0,-1);
				$oCron['cron_hour']=$arrNexttime['cron_hour'];
				$oCron['cron_minute']=$arrNexttime['cron_minute'];
			}else{
				$oCron['cron_day']=$nFirstday;
				$oCron['cron_hour']=$arrTodaytime['cron_hour'];
				$oCron['cron_minute']=$arrTodaytime['cron_minute'];
			}
		}else{
			$oCron['cron_day']=$nFirstday;
			$arrNexttime=self::todayNextrun($oCron,0,-1);
			$oCron['cron_hour']=$arrNexttime['cron_hour'];
			$oCron['cron_minute']=$arrNexttime['cron_minute'];
		}

		$nNextrun=@mktime($oCron['cron_hour'],$oCron['cron_minute']>0?$oCron['cron_minute']:0,0,$nMonthnow,$oCron['cron_day'],$nYearnow);

		$arrData=array('cron_lastrun'=>CURRENT_TIMESTAMP,'cron_nextrun'=>$nNextrun);
		if(!($nNextrun>CURRENT_TIMESTAMP)){
			$arrData['cron_status']='0';
		}

		$oUpdateCron=CronModel::F('cron_id=?',$oCron['cron_id'])->getOne();
		$oUpdateCron->changeProp($arrData);
		$oUpdateCron->save('update');
	
		return true;
	}

	private static function todayNextrun($oCron,$nHour=-2,$nMinute=-2){
		$nHour=$nHour==-2?date('H',CURRENT_TIMESTAMP):$nHour;
		$nMinute=$nMinute==-2?date('i',CURRENT_TIMESTAMP):$nMinute;

		$arrNexttime=array();
		if($oCron['cron_hour']==-1 && !$oCron['cron_minute']){
			$arrNexttime['cron_hour']=$nHour;
			$arrNexttime['cron_minute']=$nMinute+1;
		}elseif($oCron['cron_hour']==-1 && $oCron['cron_minute']!=''){
			$arrNexttime['cron_hour']=$nHour;
			if(($nNextminute=self::nextMinute($oCron['cron_minute'],$nMinute))===false){
				++$arrNexttime['cron_hour'];
				$nNextminute=$oCron['cron_minute'][0];
			}
			$arrNexttime['cron_minute']=$nNextminute;
		}elseif($oCron['cron_hour']!=-1 && $oCron['cron_minute']==''){
			if($oCron['cron_hour']<$nHour){
				$arrNexttime['cron_hour']=$arrNexttime['cron_minute']=-1;
			}elseif($oCron['cron_hour']==$nHour){
				$arrNexttime['cron_hour']=$oCron['cron_hour'];
				$arrNexttime['cron_minute']=$nMinute+1;
			}else{
				$arrNexttime['cron_hour']=$oCron['cron_hour'];
				$arrNexttime['cron_minute']=0;
			}
		}elseif($oCron['cron_hour']!=-1 && $oCron['cron_minute']!=''){
			$nNextminute=self::nextMinute($oCron['cron_minute'],$nMinute);
			if($oCron['cron_hour']<$nHour || ($oCron['cron_hour']==$nHour && $nNextminute===false)){
				$arrNexttime['cron_hour']=-1;
				$arrNexttime['cron_minute']=-1;
			} else {
				$arrNexttime['cron_hour']=$oCron['cron_hour'];
				$arrNexttime['cron_minute']=$nNextminute;
			}
		}

		return $arrNexttime;
	}

	private static function nextMinute($arrNextminutes,$nMinutenow){
		foreach($arrNextminutes as $nNextminute){
			if($nNextminute>$nMinutenow){
				return $nNextminute;
			}
		}

		return false;
	}

}
