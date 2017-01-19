<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   查看私人短消息($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		
		$nPmId=Q::G('id');
		if($nPmId===null || $nPmId=='index'){
			$this->_oParent->index();
			exit();
		}
		
		// 查看短消息是否存在
		$oOnePm=PmModel::F('pm_id=?',$nPmId)->query();
		if(empty($oOnePm['pm_id'])){
			$this->_oParent->page404();
		}

		if(!Q::G('muid','G') && $oOnePm['pm_status']==0){
			$this->_oParent->page404();
		}
		
		// 系统消息
		if($oOnePm['pm_type']=='system'){
			$oPm=Q::instance('PmModel');
			$oPm->readSystemmessage($oOnePm['pm_id']);
			if($oPm->isError()){
				$this->E($oPm->getErrorMessage());
			}

			$this->assign('oPm',$oOnePm);
			$this->assign('sType','system');
			$this->display('pm+singlesystem');
			
			exit();
		}

		$nUserId=intval(Q::G('uid'));

		// 读取用户发送的短消息
		if(empty($nUserId)){
			$nToUserId=intval(Q::G('muid'));
			if($nToUserId!=$oOnePm['pm_msgtoid'] && $GLOBALS['___login___']['user_id']!=$oOnePm['pm_msgfromid']){
				$this->_oParent->page404();
			}
			
			$this->assign('oPm',$oOnePm);
			$this->assign('sType','my');
			$this->display('pm+singlemy');

			exit();
		}
		
		// 最近消息时间
		$sDate=Q::G('date','G');
		if(empty($sDate)){
			$sDate=3;
		}
		if($sDate!='all'){
			$arrWhere['create_dateline']=array('egt',(CURRENT_TIMESTAMP-$sDate*86400));
		}
		
		$arrWhere['pm_type']='user';
		$arrWhere['pm_status']=1;
		$arrWhere['pm_msgfromid']=array('exp',"in(".$oOnePm['pm_msgtoid'].",$nUserId)");
		$arrWhere['pm_id']=array('egt',$nPmId);
		
		$nTotalRecord=Model::F_('pm')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['pm_single_list_num']);
		$arrPms=Model::F_('pm')->where($arrWhere)
			->order('pm_id DESC')
			->limit($oPage->S(),$oPage->N())
			->query();

		$this->assign('arrPms',$arrPms);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nTotalPm',$nTotalRecord);
		
		// 更新短消息状态
		$sReadPmids='';
		
		if(is_array($arrPms)){
			foreach($arrPms as $oPm){
				if($oPm['pm_isread']==0){
					$sReadPmids.=$oPm['pm_id'].',';
				}
			}

			if(!empty($sReadPmids)){
				$sReadPmids=rtrim($sReadPmids,',');
				$sReadPmids="AND `pm_id` IN ({$sReadPmids}) ";
			}
		}

		$oDb=Db::RUN();
		$sSql="UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX']."pm SET pm_isread=1 WHERE `pm_msgfromid`={$nUserId} AND `pm_status`=1 {$sReadPmids}".(!empty($arrWhere['create_dateline'])?" AND `create_dateline`>=".$arrWhere['create_dateline'][1]:'');
		$oDb->query($sSql);
	
		$this->assign('nUserId',$nUserId);
		$this->assign('sDate',$sDate);
		$this->assign('oPm',$oOnePm);
		$this->assign('sType','user');
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['pmsend_seccode']);
	
		// 导出数据
		if(Q::G('export')=='yes'){
			ob_end_clean();
			
			$sName='PM_'.$oOnePm['pm_msgfrom'].'_TO_'.Core_Extend::getUsernameById($oOnePm['pm_msgtoid']).'_'.date('Y_m_d_H_i_s',CURRENT_TIMESTAMP).'.html';
			
			header('Content-Encoding: none');
			header('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')?'application/octetstream':'application/octet-stream'));
			header('Content-Disposition: attachment; filename="'.$sName.'"');
			header('Pragma: no-cache');
			header('Expires: 0');
			
			$this->assign('sCurrentTimestamp',date('Y-m-d H:i',CURRENT_TIMESTAMP));
			$this->assign('sVersion',WINDSFORCE_SERVER_VERSION." Release ".WINDSFORCE_SERVER_RELEASE);
			$this->assign('sBlogName',$GLOBALS['_option_']['site_name']);
			$this->assign('sBlogUrl',Core_Extend::getSiteurl());
			$this->display('pm+archive');
			
			exit;
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('短消息聊天','Controller')));
		
		$this->display('pm+message');
	}

}
