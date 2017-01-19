<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分设置控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class CreditoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$arrExtendCredits=unserialize($GLOBALS['_option_']['extend_credit']);
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('arrExtendCredits',$arrExtendCredits);
		$this->display();
	}

	public function update_option(){
		$arrExtendCredits=$_POST['options']['extend_credit'];

		$arrSaveExtendCredits=array();
		foreach($arrExtendCredits as $sKey=>$arrExtendCredit){
			if(!isset($arrExtendCredit['title'])){
				$arrExtendCredit['title']='';
			}

			foreach(array('available','unit','initcredits','lowerlimit','ratio','allowexchangein','allowexchangeout') as $sValue){
				if(!isset($arrExtendCredit[$sValue]) || $arrExtendCredit[$sValue]<0){
					$arrExtendCredit[$sValue]=0;
				}

				$arrExtendCredit[$sValue]=intval($arrExtendCredit[$sValue]);
			}

			if($arrExtendCredit['available']==1 && !$arrExtendCredit['title']){
				$this->E(Q::L('启用的积分名称不能为空','Controller'));
			}

			$arrSaveExtendCredits[$sKey]=$arrExtendCredit;
		}

		$oOptionModel=OptionModel::F('option_name=?','extend_credit')->getOne();
		$oOptionModel->option_value=serialize($arrSaveExtendCredits);
		$oOptionModel->save('update');
		unset($_POST['options']['extend_credit']);

		$this->cache_creditrule();
		parent::update_option();
	}

	public function policytable(){
		$arrWhere=array();

		$nTotalRecord=CreditruleModel::F()->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['admin_list_num']);
		$arrCreditrules=CreditruleModel::F()->where($arrWhere)->all()->order('creditrule_id ASC')->limit($oPage->S(),$oPage->N())->query();

		$this->assign('sPageNavbar',$oPage->P());
		$this->assign('arrCreditrules',$arrCreditrules);

		$arrAvailableExtendCredits=array();
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
		$this->display();
	}

	public function edit($sModel=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('id','G'));

		if(!empty($nId)){
			$oCreditruleModel=CreditruleModel::F('creditrule_id=?',$nId)->query();
			if(!empty($oCreditruleModel['creditrule_id'])){
				$arrAvailableExtendCredits=array();
				$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
				$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
				$this->assign('oValue',$oCreditruleModel);
				$this->assign('nId',$nId);
				$this->display('creditoption+edit');
			}else{
				$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('id');
		for($nI=1;$nI<=8;$nI++){
			$sCreditType='creditrule_extendcredit'.$nI;
			if(isset($_POST[$sCreditType])){
				if(!Credit_Extend::checkCredit($_POST[$sCreditType])){
					$this->E(Q::L('各项积分增减允许的范围为 -99～+99','Controller'));
				}else{
					$_POST[$sCreditType]=intval($_POST[$sCreditType]);
				}
			}
		}
		
		$oCreditruleModel=CreditruleModel::F('creditrule_id=?',$nId)->query();
		$oCreditruleModel->save('update');
		if(!$oCreditruleModel->isError()){
			$this->cache_creditrule();
			$this->S(Q::L('数据更新成功','Controller'));
		}else{
			$this->E($oCreditruleModel->getErrorMessage());
		}
	}

	protected function cache_creditrule(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('creditrule');
	}

}
