<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		
		$sType=trim(Q::G('type','G'));

		if(in_array($sType,array('system','systemnew'))){
			$sFormAction=Q::U('home://pm/readselect');
		}elseif($sType=='my'){
			$sFormAction=Q::U('home://pm/delmyselect');
		}else{
			$sFormAction=Q::U('home://pm/delselect');
		}

		if($sType=='new'){
			$arrWhere['pm_isread']=0;
			$arrWhere['pm_type']='user';
		}elseif($sType=='system' || $sType=='systemnew'){
			$arrWhere['pm_type']='system';
		}else{
			$arrWhere['pm_type']='user';
		}

		if($sType!='system' && $sType!='systemnew'){
			if($sType=='my'){
				// 我发送的消息如果被对方删除了，这里status=1的话就无法取出来 && 我的发件箱状态为1
				$arrWhere['pm_msgfromid']=$GLOBALS['___login___']['user_id'];
				$arrWhere['pm_mystatus']=1;
			}else{
				$arrWhere['pm_status']=1;
				$arrWhere['pm_msgtoid']=$GLOBALS['___login___']['user_id'];
			}

			$arrReadPms=array();
		}else{
			// 已删短消息
			$arrSystemdeleteMessages=Model::F_('pmsystemdelete','user_id=?',$GLOBALS['___login___']['user_id'])->getAll();
			if(!empty($arrSystemdeleteMessages)){
				foreach($arrSystemdeleteMessages as $oSystemdeleteMessage){
					$arrDeletePms[]=$oSystemdeleteMessage['pm_id'];
				}
			}else{
				$arrDeletePms=array();
			}

			$arrNotinPms=$arrDeletePms;

			// 已读短消息
			$arrSystemreadMessages=Model::F_('pmsystemread','user_id=?',$GLOBALS['___login___']['user_id'])->getAll();
			if(!empty($arrSystemreadMessages)){
				foreach($arrSystemreadMessages as $oSystemreadMessage){
					$arrReadPms[]=$oSystemreadMessage['pm_id'];
					if($sType=='systemnew'){
						$arrNotinPms[]=$oSystemreadMessage['pm_id'];
					}
				}
			}else{
				$arrReadPms=array();
			}
			
			if(!empty($arrNotinPms)){
				$arrWhere['pm_id']=array('NOT IN',$arrNotinPms);
			}
		}

		$nTotalRecord=Model::F_('pm')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['pm_list_num']);
		$arrPmLists=Model::F_('pm')->where($arrWhere)
			->order('pm_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$this->title_()));
		
		$this->assign('nTotalPm',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrPmLists',$arrPmLists);
		$this->assign('sPmType',$sType);
		$this->assign('arrReadPms',$arrReadPms);
		$this->assign('sType',($sType?$sType:'user'));
		$this->assign('sFormAction',$sFormAction);
		$this->display('pm+index');
	}

	protected function title_(){
		$sType=trim(Q::G('type','G'));

		switch($sType){
			case 'new':
				return Q::L('未读短消息','Controller');
				break;
			case 'user':
				return Q::L('私人短消息','Controller');
				break;
			case 'my':
				return Q::L('已发短消息','Controller');
				break;
			case 'systemnew':
				return Q::L('未读公共短消息','Controller');
				break;
			case 'system':
				return Q::L('公共短消息','Controller');
				break;
			default:
				return Q::L('私人短消息','Controller');
				break;
		}
	}

}
