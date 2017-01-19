<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   提醒列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		$arrWhere['user_id']=$GLOBALS['___login___']['user_id'];

		$sType=trim(Q::G('type','G'));
		if(!$sType){
			$sType='new';
		}
		if($sType=='new'){
			$arrNoticeId=array();
			$arrWhere['notice_isread']=0;
		}else{
			$arrWhere['notice_isread']=1;
		}

		$nTotalRecord=Model::F_('notice')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['notice_list_num']);
		$arrNoticeLists=Model::F_('notice')->where($arrWhere)->all()->order('`notice_id` DESC')->limit($oPage->S(),$oPage->N())->getAll();

		// 最后处理结果
		$arrNoticedatas=array();
		if(is_array($arrNoticeLists)){
			foreach($arrNoticeLists as $nKey=>$oNotice){
				$arrData=@unserialize($oNotice['notice_data']);
				$arrTempdata=array();
				if(is_array($arrData)){
					foreach($arrData as $nK=>$sValueTemp){
						$sTempkey='{'.$nK.'}';
						// @开头表示URL，调用Q::U来生成地址
						if(strpos($nK,'@')===0){
							$sValueTemp=Q::U($sValueTemp);
						}
						$arrTempdata[$sTempkey]=$sValueTemp;
					}
					// 标记已经阅读
					if($sType=='new'){
						$arrNoticeId[]=$oNotice['notice_id'];
					}
				}

				$arrNoticedatas[]=array(
					'user_id'=>$oNotice['notice_authorid'],
					'notice_username'=>$oNotice['notice_authorusername'],
					'notice_content'=>strtr($oNotice['notice_template'],$arrTempdata),
					'create_dateline'=>$oNotice['notice_fromnum']>1?$oNotice['update_dateline']:$oNotice['create_dateline'],
					'notice_fromnum'=>$oNotice['notice_fromnum'],
					'notice_type'=>$oNotice['notice_type'],
				);
			}
		}

		// 标记已经阅读
		if($sType=='new'){
			if($arrNoticeId){
				$arrUpdateId['notice_id']=array('in',$arrNoticeId);
			}else{
				$arrUpdateId['notice_id']='';
			}

			Model::M_('notice')->updateWhere(array('notice_isread'=>1),$arrUpdateId);
		}
		
		Core_Extend::getSeo($this,array('title'=>$this->title_()));

		$this->assign('nTotalNotice',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrNoticedatas',$arrNoticedatas);
		$this->assign('sNoticeType',$sType);
		$this->assign('sType',$sType);
		$this->display('notice+index');
	}

	protected function title_(){
		$sType=trim(Q::G('type','G'));
		switch($sType){
			case 'new':
				return Q::L('未读提醒','Controller');
				break;
			case 'isread':
				return Q::L('已读提醒','Controller');
			default:
				return Q::L('未读提醒','Controller');
				break;
		}
	}

}
