<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户提醒控制器($$)*/

!defined('Q_PATH') && exit;

class NoticeController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function filter_(&$arrMap){
		$arrMap['B.user_name']=array('like',"%".Q::G('user_name')."%");
		$arrMap['A.notice_authorusername']=array('like',"%".Q::G('notice_authorusername')."%");

		$nNoticeIsread=Q::G('notice_isread');
		if($nNoticeIsread!==null && $nNoticeIsread!=''){
			$arrMap['A.notice_isread']=$nNoticeIsread;
		}

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."user AS B','B.user_name','A.user_id=B.user_id')";
	}

	public function show(){
		$nId=Q::G('id','G');

		if(!empty($nId)){
			$oModel=NoticeModel::F('notice_id=?',$nId)->query();

			if(!empty($oModel->notice_id)){
				$arrData=@unserialize($oModel['notice_data']);
		
				$arrTempdata=array();
				if(is_array($arrData)){
					foreach($arrData as $nK=>$sValueTemp){
						$sTempkey='{'.$nK.'}';

						// @开头表示URL，调用Q::U来生成地址
						if(strpos($nK,'@')===0){
							$sValueTemp='Q::U('.$sValueTemp.')';
							$sValueTemp='javascript:alert(\''.$sValueTemp.'\');';
						}

						$arrTempdata[$sTempkey]=$sValueTemp;
					}
				}

				$arrNoticedata=array(
					'user_id'=>$oModel['notice_authorid'],
					'notice_username'=>$oModel['notice_authorusername'],
					'notice_content'=>strtr($oModel['notice_template'],$arrTempdata),
					'create_dateline'=>$oModel['notice_fromnum']>1?$oModel['update_dateline']:$oModel['create_dateline'],
					'notice_fromnum'=>$oModel['notice_fromnum'],
					'notice_type'=>$oModel['notice_type'],
				);

				$this->assign('oValue',$oModel);
				$this->assign('nId',$nId);
				$this->assign('arrNoticedata',$arrNoticedata);
				
				$this->display('notice+show');
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

}
