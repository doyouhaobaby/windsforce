<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   计划任务控制器($$)*/

!defined('Q_PATH') && exit;

class CronController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function filter_(&$arrMap){
		$arrMap['A.cron_filename']=array('like',"%".Q::G('cron_filename')."%");
		$arrMap['A.cron_name']=array('like',"%".Q::G('cron_name')."%");

		$sCronType=Q::G('cron_type');
		if($sCronType!==null && $sCronType!=''){
			$arrMap['A.cron_type']=$sCronType;
		}

		// 执行时间
		$this->getTime_('A.cron_lastrun',$arrMap);
		$this->getTime_('A.cron_nextrun',$arrMap,'start_time_2','end_time_2');
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_cron($nId)){
				$this->E(Q::L('系统计划任务无法删除','Controller'));
			}
		}
	}

	public function run(){
		$nId=intval(Q::G('id','G'));

		if(!empty($nId)){
			$oCron=CronModel::F('cron_id=?',$nId)->getOne();

			if(!empty($oCron['cron_id'])){
				$arrFile=explode('@',trim($oCron['cron_filename']));

				$sAppid=$sPluginid='';
				if(count($arrFile)>1){
					if(!isset($GLOBALS['_cache_']['app'])){
						Core_Extend::loadCache('app');
					}
					
					if($oCron['cron_type']=='app' && in_array($arrFile[0],$GLOBALS['_cache_']['app'])){
						$sAppid=$arrFile[0];
						$sCronfilename=$arrFile[1];
					}elseif($oCron['cron_type']=='plugin' && in_array($arrFile[0],array('helloworld'))){
						$sPluginid=$arrFile[0];
						$sCronfilename=$arrFile[1];
					}
				}else{
					$sCronfilename=trim($oCron['cron_filename']);
				}

				if(empty($sCronfilename)){
					$this->E(Q::L('计划任务脚本不能为空','Controller'));
				}

				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/",$sCronfilename)){
					$this->E(Q::L('计划任务脚本含有非法字符','Controller'));
				}

				if($sAppid){
					$sCronfile="/System/app/{$sAppid}/App/Class/Extension/cron/{$sCronfilename}";
				}elseif($sPluginid){
					$sCronfile="/user/plugin/{$sPluginid}/cron/{$sCronfilename}";
				}else{
					if($oCron['cron_type']=='user'){
						$sCronfile='/user/cron/'.$sCronfilename;
					}else{
						$sCronfile='/System/cron/'.$sCronfilename;
					}
				}

				if(!is_file(WINDSFORCE_PATH.$sCronfile)){
					$this->E(Q::L('您指定的任务脚本文件(%s)不存在或包含语法错误','Controller',null,$sCronfile));
				}

				if($oCron['cron_weekday']==-1 && $oCron['cron_day']==-1 && $oCron['cron_hour']==-1 && $oCron['cron_minute']===''){
					$this->E(Q::L('计划任务时间设置不正确','Controller'));
				}

				if(!Q::classExists('Windsforce_Cron')){
					require_once(Core_Extend::includeFile('class/windsforce/Windsforce_Cron'));
				}

				Windsforce_Cron::RUN($nId);

				$this->S(Q::L('定时任务执行成功','Controller'));
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	protected function AUpdateObject_($oModel){
		$nDaynew=$_POST['cron_weekday']!=-1?-1:$_POST['cron_day'];

		if(strpos($_POST['cron_minute'],',')!==FALSE){
			$arrMinutenew=explode(',',$_POST['cron_minute']);
			foreach($arrMinutenew as $nKey=>$nVal){
				$arrMinutenew[$nKey]=$nVal=intval($nVal);
				if($nVal<0 || $nVal>59){
					unset($arrMinutenew[$nKey]);
				}
			}

			$arrMinutenew=array_slice(array_unique($arrMinutenew),0,12);
			$sMinutenew=implode("\t",$arrMinutenew);
		}else{
			$sMinutenew=intval($_POST['cron_minute']);
			$sMinutenew=$sMinutenew>=0 && $sMinutenew<60?$sMinutenew:'';
		}

		$arrFile=explode('@',trim($_POST['cron_filename']));

		$sAppid=$sPluginid='';
		if(count($arrFile)>1){
			if(!isset($GLOBALS['_cache_']['app'])){
				Core_Extend::loadCache('app');
			}
			
			if($oModel['cron_type']=='app' && in_array($arrFile[0],$GLOBALS['_cache_']['app'])){
				$sAppid=$arrFile[0];
				$_POST['cron_filename']=$arrFile[1];
			}elseif($oModel['cron_type']=='plugin' && in_array($arrFile[0],array('helloworld'))){
				$sPluginid=$arrFile[0];
				$_POST['cron_filename']=$arrFile[1];
			}
		}

		if(empty($_POST['cron_filename'])){
			$this->E(Q::L('计划任务脚本不能为空','Controller'));
		}

		if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/",$_POST['cron_filename'])){
			$this->E(Q::L('计划任务脚本含有非法字符','Controller'));
		}

		if($sAppid){
			$sCronfile="/System/app/{$sAppid}/App/Class/Extension/cron/{$_POST['cron_filename']}";
			$_POST['cron_filename']=$sAppid.'@'.$_POST['cron_filename'];
		}elseif($sPluginid){
			$sCronfile="/user/plugin/{$sPluginid}/cron/{$_POST['cron_filename']}";
			$_POST['cron_filename']=$sPluginid.'@'.$_POST['cron_filename'];
		}else{
			if($oModel['cron_type']=='user'){
				$sCronfile='/user/cron/'.$_POST['cron_filename'];
			}else{
				$sCronfile='/System/cron/'.$_POST['cron_filename'];
			}
		}

		if(!is_readable(WINDSFORCE_PATH.$sCronfile)){
			$this->E(Q::L('您指定的任务脚本文件(%s)不存在或包含语法错误','Controller',null,$sCronfile));
		}

		if($_POST['cron_weekday']==-1 && $nDaynew==-1 && $_POST['cron_hour']==-1 && $sMinutenew===''){
			$this->E(Q::L('计划任务时间设置不正确','Controller'));
		}
		
		$oModel->cron_weekday=$_POST['cron_weekday'];
		$oModel->cron_day=$nDaynew;
		$oModel->cron_hour=$_POST['cron_hour'];
		$oModel->cron_minute=$sMinutenew;
		$oModel->cron_filename=trim($_POST['cron_filename']);

		unset($_POST['cron_weekday'],$_POST['cron_hour'],$_POST['cron_filename'],$_POST['cron_minute']);
	}
	
	protected function aUpdate($nId=null){
		if(!Q::classExists('Windsforce_Cron')){
			require_once(Core_Extend::includeFile('class/windsforce/Windsforce_Cron'));
		}

		Windsforce_Cron::RUN($nId);
	}

	public function get_type($sType){
		switch($sType){
			case 'system':
				return Q::L('系统内置','Controller');
				break;
			case 'user':
				return Q::L('用户自定义','Controller');
				break;
			case 'app':
				return Q::L('应用','Controller');
				break;
			case 'plugin':
				return Q::L('插件','Controller');
				break;
			default:
				return 'N/A';
				break;
		}
	}

	public function get_week($nWeek){
		switch($nWeek){
			case '0':
				return Q::L('日','Controller');
				break;
			case '1':
				return Q::L('一','Controller');
				break;
			case '2':
				return Q::L('二','Controller');
				break;
			case '3':
				return Q::L('三','Controller');
				break;
			case '4':
				return Q::L('四','Controller');
				break;
			case '5':
				return Q::L('五','Controller');
				break;
			case '6':
				return Q::L('六','Controller');
				break;
			default:
				return 'N/A';
				break;
		}
	}

	public function is_system_cron($nId){
		$nId=intval($nId);

		$oCron=CronModel::F('cron_id=?',$nId)->setColumns('cron_id,cron_type')->getOne();
		if(empty($oCron['cron_id'])){
			return false;
		}

		if($oCron['cron_type']!='user'){
			return true;
		}

		return false;
	}

}
