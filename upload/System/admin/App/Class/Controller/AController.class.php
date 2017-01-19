<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   公用控制器($$)*/

!defined('Q_PATH') && exit;

class AController extends Controller{

	public function init__(){
		parent::init__();

		Core_Extend::loadCache('option');
		Core_Extend::loginInformation();

		// RBAC
		if(!Rbac::checkRbac($GLOBALS['___login___'])){
			$this->E(Rbac::getErrorMessage());
		}
		
		Core_Extend::page404($this);
		
		// 记录后台操作记录
		if($GLOBALS['_option_']['adminlog_record']==1 && Q::G('page')<2 && 
			!in_array(MODULE_NAME,array('public','index','adminctrlmenu','adminlog','misc'))
		)
		{
			$sUrl=parse_url(__SELF__,PHP_URL_QUERY);

			// 重复记录判断
			$bRecord=true;

			$nAdminlogrecordtime=intval($GLOBALS['_option_']['adminlog_record_time']);
			if($nAdminlogrecordtime>0){
				$oTryadminlogModel=AdminlogModel::F('adminlog_info=?',$sUrl)->order('adminlog_id DESC')->getOne();
				if(!empty($oTryadminlogModel['adminlog_id']) && CURRENT_TIMESTAMP-$oTryadminlogModel['create_dateline']<=$nAdminlogrecordtime){
					$bRecord=false;
				}
			}

			if($bRecord===true){
				$oAdminlog=new AdminlogModel();
				$oAdminlog->adminlog_info=$sUrl;
				$oAdminlog->save();
				if($oAdminlog->isError()){
					$this->E($oAdminlog->getErrorMessage());
				}
			}
		}
	}
	
	public function page404(){
		$this->display('Public+404');
		exit();
	}
	
	public function index($sName=null,$bDisplay=true){
		if(empty($sName)){
			$sName=MODULE_NAME;
		}
		
		$arrMap=$this->map($sName);
		if(method_exists($this,'filter_')){
			$this->filter_($arrMap);
		}
		$this->get_list($arrMap,$sName);
		
		if($bDisplay===true){
			$this->display();
		}
	}
	
	protected function map($sName=null){
		if(empty($sName)){
			$sName=MODULE_NAME;
		}
		
		$sName=ucfirst($sName).'Model';
		$arrField=array();
		eval('$arrField='.$sName.'::M()->_arrTableMeta;');
	
		$arrMap=array();
		foreach($arrField['field'] as $sField){
			if(isset($_REQUEST[$sField]) && !empty($_REQUEST[$sField])){
				$arrMap['A.'.$sField]=$_REQUEST[$sField];
			}
		}

		$sStatusField=substr(strtolower($sName),0,-5).'_status';

		if(in_array($sStatusField,$arrField['field'])){
			// 回收站
			if(!empty($_REQUEST['recycle_']) || (!empty($_REQUEST[$sStatusField]) && $_REQUEST[$sStatusField]==CommonModel::STATUS_RECYLE)){
				$arrMap['A.'.$sStatusField]=CommonModel::STATUS_RECYLE;
				$this->assign('bIsRecycleList',true);
			}elseif(isset($_REQUEST[$sStatusField]) && $_REQUEST[$sStatusField]!='' && $_REQUEST[$sStatusField]!=CommonModel::STATUS_RECYLE){// 是否启用状态
				$arrMap['A.'.$sStatusField]=$_REQUEST[$sStatusField];
				$this->assign('nIsStatus',intval($_REQUEST[$sStatusField]));
			}else{// 排除回收站
				$arrMap['A.'.$sStatusField]=array('NEQ',CommonModel::STATUS_RECYLE);
			}
		}

		return $arrMap;
	}
	
	protected function get_list($arrMap,$sName=null){
		if(empty($sName)){
			$sName=MODULE_NAME;
		}
		
		$sSqlJoin='';
		if(method_exists($this,'sqljoin_')){
			$sSqlJoin=$this->sqljoin_();
		}
		
		$arrSortUrl=array();
		$nTotalRecord=0;
		eval('$nTotalRecord='.ucfirst($sName).'Model::F(\'@A\')->where($arrMap)'.$sSqlJoin.'->all()'.($sSqlJoin?'->asArray()':'').'->getCounts();');
		foreach($arrMap as $sKey=>$sVal){
			if(!is_array($sVal)){
				$arrSortUrl[]='"'.$sKey.'='.urlencode($sVal).'"';
			}
		}
		
		$sSortBy=strtoupper(Q::G('sort_'))=='ASC'?'ASC':'DESC' ;
		$sOrder=Q::G('order_')?Q::G('order_'):$sName.'_id';
		
		$this->assign('sSortByUrl',strtolower($sSortBy)=='desc'? 'asc':'desc');
		$this->assign('sSortByDescription',strtolower($sSortBy)=='desc'?Q::L('倒序','Controller'):Q::L('升序','Controller'));
		$this->assign('sOrder',$sOrder);
		$this->assign('sSortUrl','new Array('.implode(',',$arrSortUrl).')');

		$nPerpage=intval(Q::G('perpage'));

		$oPage=Page::RUN($nTotalRecord,$nPerpage?$nPerpage:$GLOBALS['_option_']['admin_list_num']);
		$arrLists=array();
		eval('$arrLists='.ucfirst($sName).'Model::F(\'@A\')->setColumns(\'A.*\')->where($arrMap)'.$sSqlJoin.'->order(\'A.\'.$sOrder.\' \'.$sSortBy)->limit($oPage->S(),$oPage->N())'.($sSqlJoin?'->asArray()':'').'->getAll();');

		$this->assign('sPageNavbar',$oPage->P());
		$this->assign('arrLists',$arrLists);
	}
	
	public function input_change_ajax($sName=null){
		if(empty($sName)){
			$sName=MODULE_NAME;
		}
		
		$nInputAjaxId=intval(Q::G('input_ajax_id'));
		$sInputAjaxField=trim(Q::G('input_ajax_field'));
		$sInputAjaxVal=trim(Q::G('input_ajax_val'));
		$nUnique=intval(Q::G('unique'));

		$oModel=null;
		eval('$oModel='.ucfirst($sName).'Model::F(\''.$sName.'_id=?\','.$nInputAjaxId.')->query();');
		$oModel->{$sInputAjaxField}=$sInputAjaxVal;
		
		if($nUnique==1){
			$this->input_change_unique($sName);
		}
		
		if(method_exists($this,'BInput_change_ajax_data_')){
			$arrData=call_user_func(array($this,'BInput_change_ajax_data_'),$arrData);
		}
		
		$oModel->save('update');
		if($oModel->isError()){
			$this->E($oModel->getErrorMessage());
		}else{
			$this->afterInputChangeAjax($sName);
			$arrVo=array(
				'id'=>$sInputAjaxField.'_'.$nInputAjaxId,
				'value'=>$oModel->{$sInputAjaxField},
			);
			
			$this->A($arrVo,Q::L('数据更新成功','Controller'));
		}
	}
	
	protected function afterInputChangeAjax($sName=null){}
	
	public function input_change_unique($sModel=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		$oModelMeta=null;
		eval('$oModelMeta='.ucfirst($sModel).'Model::M();');
		$nId=intval(Q::G('input_ajax_id'));
		$sField=trim(Q::G('input_ajax_field'));
		$sName=trim(Q::G('input_ajax_val'));
		$sInfo='';
		if($nId){
			$oModel=null;
			eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
			$arrInfo=$oModel->toArray();
			$sInfo=$arrInfo[ $sField ];
		}
		
		if($sName!=$sInfo){
			$oSelect=null;
			eval('$oSelect='.ucfirst($sModel).'Model::F();');
			$sFunc='getBy'.$sField;
			$arrResult=$oSelect->{$sFunc}($sName)->toArray();
			if(!empty($arrResult[$sField])){
				$this->E(Q::L('该项数据已经存在','Controller'));
			}
		}
	}
	
	public function insert($sModel=null,$nId=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id','G');
		}
		
		$nId=$this->getPostInt($nId);
		$oModel=null;
		eval('$oModel=new '.ucfirst($sModel).'Model();');
		
		if(method_exists($this,'AInsertObject_')){
			call_user_func(array($this,'AInsertObject_'),$oModel);
		}
		$oModel->save();
		
		$sPrimaryKey=$sModel.'_id';
		if(!$oModel->isError()){
			$this->aInsert($oModel->{$sPrimaryKey});
			
			if(!isset($_POST['no_ajax'])){
				$this->A($oModel->toArray(),Q::L('数据保存成功','Controller'),1);
			}else{
				$nId=$oModel->{$sPrimaryKey};
				if(Q::G('is_app','P')){
					$sUrl=Q::U('app/config?controller='.trim(Q::G('controller','G')).'&action=edit&id='.intval(Q::G('id','G')).'&value='.$nId);
				}else{
					$sUrl=Q::U($sModel.'/edit?id='.$nId);
				}
				
				$this->assign('__JumpUrl__',$sUrl);
				$this->S(Q::L('数据保存成功','Controller'));
			}
		}else{
			$this->E($oModel->getErrorMessage());
		}
	}
	
	protected function aInsert($nId=null){}
	
	public function add(){
		$this->display();
	}
	
	public function edit($sModel=null,$nId=null,$bDidplay=true){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id','G');
		}
		
		$nId=$this->getPostInt($nId);
		if(!empty($nId)){
			$oModel=null;
			eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
			if(method_exists($this,'AEditObject_')){
				call_user_func(array($this,'AEditObject_'),$oModel);
			}
			
			if(!empty($oModel->{$sModel.'_id'})){
				$this->assign('oValue',$oModel);
				$this->assign('nId',$nId);
				if($bDidplay===true){
					$this->display($sModel.'+add');
				}
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}
	
	public function update($sModel=null,$nId=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id');
		}
		
		$nId=$this->getPostInt($nId);
		$oModel=null;
		eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
		
		if(method_exists($this,'AUpdateObject_')){
			call_user_func(array($this,'AUpdateObject_'),$oModel);
		}
		
		$oModel->save('update');
		$sPrimaryKey=$sModel.'_id';
		if(!$oModel->isError()){
			$this->aUpdate($oModel->{$sPrimaryKey});
			
			if(!isset($_POST['no_ajax'])){
				$this->A($oModel->toArray(),Q::L('数据更新成功','Controller'),1);
			}else{
				$this->S(Q::L('数据更新成功','Controller'));
			}
		}else{
			$this->E($oModel->getErrorMessage());
		}
	}
	
	protected function aUpdate($nId=null){}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($sId)){
			$sId=Q::G('id');
		}

		if(!empty($sId)){
			$arrId=explode(',',$sId);
			foreach($arrId as $nV){
				$this->change_status_('status',CommonModel::STATUS_RECYLE,$sModel,$nV,$bApp);
			}

			$this->aForeverdelete($sId);

			if(C::isAjax()){
				$this->A('',Q::L('记录放置到回收站成功','Controller'),1);
			}else{
				$this->S(Q::L('记录放置到回收站成功','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}
	
	protected function aForeverdelete($sId){}
	
	public function foreverdelete_deep($sModel=null,$sId=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($sId)){
			$sId=Q::G('id');
		}
		
		if(!empty($sId)){
			$oModelMeta=null;
			eval('$oModelMeta='.ucfirst($sModel).'Model::M();');
			$sPk=reset($oModelMeta->_arrTableMeta['pk_']);
			$oModelMeta->deleteWhere(array($sPk=>array('in',$sId)));
			if($oModelMeta->isError()){
				$this->E($oModelMeta->getErrorMessage());
			}else{
				$this->aForeverdelete_deep($sId);
				if(C::isAjax()){
					$this->A('',Q::L('删除记录成功','Controller'),1);
				}else{
					$this->S(Q::L('删除记录成功','Controller'));
				}
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}
	
	protected function aForeverdelete_deep($sId){}
	
	public function forbid($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('status',0,$sModel,$sId,$bApp);
	}
	
	protected function aForbid(){}

	public function closeitem($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('status',13,$sModel,$sId,$bApp);
	}
	
	protected function aCloseitem(){}

	public function openitem($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('status',11,$sModel,$sId,$bApp);
	}
	
	protected function aOpenitem(){}
	
	public function resume($sModel=null,$sId=null,$bApp=false){
		$this->change_status_('status',1,$sModel,$sId,$bApp);
	}

	protected function aResume(){}
	
	protected function change_status_($sField='status',$nStatus=1,$sModel=null,$sId=null,$bApp=false){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($sId)){
			$sId=Q::G('id');
		}

		$nPage=$this->get_referer_page();
		if(!empty($sId)){
			$oModelMeta=null;
			eval('$oModelMeta='.ucfirst($sModel).'Model::M();');
			$sPk=reset($oModelMeta->_arrTableMeta['pk_']);
			$oModelMeta->updateWhere(array($sModel.'_'.$sField=>$nStatus),array($sPk=>$sId));
			if($oModelMeta->isError()){
				$this->E($oModelMeta->getErrorMessage());
			}else{
				if($bApp===false){
					$arrUrl=array();
					if(Q::G('recycle_')==1){
						$arrUrl['recycle_']=1;
					}

					$this->assign('__JumpUrl__',Q::U($sModel.'/index'.($nPage?'?page='.$nPage:''),$arrUrl));
				}else{
					$arrUrl=array('controller'=>$sModel);
					if($nPage>1){
						$arrUrl['page']=$nPage;
					}
					if(Q::G('recycle_')==1){
						$arrUrl['recycle_']=1;
					}

					$this->assign('__JumpUrl__',Admin_Extend::base($arrUrl));
				}
				
				if($nStatus){
					switch($nStatus){
						case 1;
							$this->aResume();
							$this->S(Q::L('恢复成功','Controller'));
							break;
						case 13;
							$this->aCloseitem();
							$this->S(Q::L('关闭成功','Controller'));
							break;
						case 11;
							$this->aOpenitem();
							$this->S(Q::L('打开成功','Controller'));
							break;
						case 9;
							return true;
							break;
					}
				}else{
					$this->aForbid();
					$this->S(Q::L('禁用成功','Controller'));
				}
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function clear_recycle($sModel=null,$sField='status'){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		$oModelMeta=null;
		eval('$oModelMeta='.ucfirst($sModel).'Model::M();');
		$oModelMeta->deleteWhere(array($sModel.'_'.$sField=>CommonModel::STATUS_RECYLE));
		if($oModelMeta->isError()){
			$this->E($oModelMeta->getErrorMessage());
		}

		$this->S(Q::L('清空回收站成功','Controller'));
	}
	
	public function save_sort($sModel=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		$sMoveResult=Q::G('moveResult','P');
		if(!empty($sMoveResult)){
			$oModel=null;
			eval('$oModel=new '.ucfirst($sModel).'Model();');
			$oDb=$oModel->getDb()->getConnect();
			$arrCol=explode(',',$sMoveResult);
			$oDb->startTransaction();
			
			$bResult=true;
			foreach($arrCol as $val){
				$val=explode(':',$val);

				$oModel=null;
				eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$val[0].')->query();');
				$oModel->{$sModel.'_sort'}=$val[1];
				try{
					$oModel->save('update');
				}catch(Exception $e){
					$bResult=false;
					$sErrorMessage=$e->getMessage();
					break;
				}
				
				if($oModel->isError()){
					$bResult=false;
					$sErrorMessage=$oModel->getErrorMessage();
					break;
				}
			}
			
			$oDb->commit();
			if($bResult!==false){
				$this->S(Q::L('更新成功','Controller'));
			}else{
				$oDb->rollback();
				$this->E($sErrorMessage);
			}
		}else{
			$this->E(Q::L('没有可以排序的数据','Controller'));
		}
	}

	public function view_preview($sModel=null,$nId=null,$bDidplay=true){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id','G');
		}
		
		$nId=$this->getPostInt($nId);
		if(!empty($nId)){
			$oModel=null;
			eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
			if(method_exists($this,'AViewpreviewObject_')){
				call_user_func(array($this,'AViewpreviewObject_'),$oModel);
			}
			
			if(!empty($oModel->{$sModel.'_id'})){
				$this->assign('oValue',$oModel);
				$this->assign('nId',$nId);

				if($bDidplay===true){
					$this->display($sModel.'+viewpreview');
				}
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function save_preview($sModel=null,$nId=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id','G');
		}
		
		$nId=$this->getPostInt($nId);
		if(!empty($nId)){
			$oModel=null;
			eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
			
			if(!empty($oModel->{$sModel.'_id'})){
				$sField=$sModel.'_'.trim(Q::G('field'));
				$sFieldValue=trim(Q::G('fieldvalue'));
				$oModel->{$sField}=$sFieldValue;
				$oModel->save('update');
				if($oModel->isError()){
					exit(
						json_encode(array('status'=>0,'message'=>$oModel->getErrorMessage()))
					);
				}else{
					exit(
						json_encode(array('status'=>1,'message'=>Q::L('数据更新成功','Controller')))
					);
				}
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function save_previewstatus($sModel=null,$nId=null){
		if(empty($sModel)){
			$sModel=MODULE_NAME;
		}
		
		if(empty($nId)){
			$nId=Q::G('id','G');
		}
		
		$nId=$this->getPostInt($nId);
		if(!empty($nId)){
			$oModel=null;
			eval('$oModel='.ucfirst($sModel).'Model::F(\''.$sModel.'_id=?\','.$nId.')->query();');
			
			if(!empty($oModel->{$sModel.'_id'})){
				$sField=$sModel.'_status';
				$nStatus=intval(Q::G('status'));
				$oModel->{$sField}=$nStatus;
				$oModel->save('update');
				if($oModel->isError()){
					exit(
						json_encode(array('status'=>0,'message'=>$oModel->getErrorMessage()))
					);
				}else{
					exit(
						json_encode(array('status'=>1,'message'=>Q::L('数据更新成功','Controller')))
					);
				}
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}
	
	public function seccode(){
		Core_Extend::seccode();
	}
	
	public function check_seccode($bSubmit=false){
		$sSeccode=trim(Q::G('seccode'));
		if(empty($sSeccode)){
			$this->E(Q::L('你验证码不能为空','__COMMON_LANG__@Common'));
		}
		
		$bResult=Core_Extend::checkSeccode($sSeccode);
		if(!$bResult){
			$this->E(Q::L('你输入的验证码错误','__COMMON_LANG__@Common'));
		}
		
		if($bSubmit===false){
			$this->S(Q::L('验证码正确','__COMMON_LANG__@Common'));
		}
	}
	
	public function get_referer_page(){
		$nPage=0;
		
		if(!empty($_SERVER['HTTP_REFERER'])){
			parse_str(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_QUERY),$arrUrldata);
			if(isset($arrUrldata['page']) && !empty($arrUrldata['page'])){
				$nPage=$arrUrldata['page'];
			}
		}
		
		return $nPage;
	}

	public function check_appdevelop(){
		if(!Q::C('APP_DEVELOP')){
			$this->E(Q::L('应用开发尚未开启，请打开配置文件设置 APP_DEVELOP 的值为1','Controller'));
		}
	}

	protected function getPostInt($sValue){
		if(!empty($sValue) && !Core_Extend::isPostInt($sValue)){
			$sValue="'{$sValue}'";
		}
		
		return $sValue;
	}
	
	protected function getTime_($sField,&$arrMap,$sStartField='start_time',$sEndField='end_time'){
		$sStartTime=trim(Q::G($sStartField));
		$sEndTime=trim(Q::G($sEndField));

		if(!empty($sStartTime) && !empty($sEndTime)){
			$arrMap[$sField]=array('between',array(strtotime($sStartTime),strtotime($sEndTime)));
		}else{
			if(!empty($sStartTime)){
				$arrMap[$sField]=array('egt',strtotime($sStartTime));
			}
			if(!empty($sEndTime)){
				$arrMap[$sField]=array('elt',strtotime($sEndTime));
			}
		}
	}

	protected function getAudit_($sField,&$arrMap){
		$arrSearch=$_GET;
		foreach(array('app','c','a','controller','action','id','page') as $nKey){
			if(isset($arrSearch[$nKey])){
				unset($arrSearch[$nKey]);
			}
		}
		if(empty($arrSearch)){
			$arrMap[$sField]=array('in','0,11');
		}
	}
	
}
