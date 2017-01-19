<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   词语过滤($$)*/

!defined('Q_PATH') && exit;

class BadwordController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.badword_find']=array('like',"%".Q::G('badword_find')."%");
		$arrMap['A.badword_admin']=array('like',"%".Q::G('badword_admin')."%");
		$arrMap['A.badword_replacement']=array('like',"%".Q::G('badword_replacement')."%");
	}

	public function bIndex_(){
		$this->assign('arrOptions',$GLOBALS['_option_']);
	}

	public function check_find(){
		$sBadwordFind=trim(Q::G('badword_find'));
		if(!$sBadwordFind){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['badword_find']=$sBadwordFind;

		$oBadword=BadwordModel::F()->where($arrWhere)->setColumns('badword_id')->getOne();
		if(empty($oBadword['badword_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function muit_add(){
		$this->display();
	}

	public function update_option(){
		$oOptionController=new OptionController();
		$oOptionController->update_option();
	}

	public function muit_insert(){
		$sBadwords=Q::G('badwords','P');
		if($sBadwords==''){
			$this->E(Q::L('导入词汇不能为空','Controller'));
		}

		$oBadword=Q::instance('BadwordModel');
		
		$nType=Q::G('type','P');
		if($nType==0){
			$bResult=$oBadword->truncateBadword();
			if($bResult===false){
				$this->E(Q::L('清空badword数据出错','Controller'));
			}

			$nType=1;
		}

		$arrValues=explode("\n",str_replace(array("\r","\n\n"),array("\r","\n"),$sBadwords));
		foreach($arrValues as $sValue){
			$arrValueTwo=explode("=",$sValue);
			if(!isset($arrValueTwo[1])){
				$arrValueTwo[1]='*';
			}

			$arrUserData=$GLOBALS['___login___'];
			$bResult=$oBadword->addBadword($arrValueTwo[0],$arrValueTwo[1],$arrUserData['user_name'],$nType);
			if($bResult===false){
				$this->E($oBadword->getErrorMessage());
			}
		}

		$this->S(Q::L('导入数据成功','Controller'));
	}

	public function export(){
		$arrBadwords=BadwordModel::F()->all()->query();

		$sString='';
		if(is_array($arrBadwords)){
			foreach($arrBadwords as $oBadword){
				$sString.=$oBadword['badword_find'].'='.$oBadword['badword_replacement']."\r\n";
			}
		}

		$sName='BADWORD_'.date('Y_m_d_H_i_s',CURRENT_TIMESTAMP).'.txt';
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename="'.$sName.'"');
		if(preg_match("/MSIE([0-9].[0-9]{1,2})/",$_SERVER['HTTP_USER_AGENT'])){
			header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
			header('Pragma: public');
		}else{
			header('Pragma: no-cache');
		}

		echo $sString;
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("badword");
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	protected function aForeverdelete_deep($sId){
		$this->aInsert();
	}
	
	protected function aForeverdelete($sId){
		$this->aInsert();
	}

}
