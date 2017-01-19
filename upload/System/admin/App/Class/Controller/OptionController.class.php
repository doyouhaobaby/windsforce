<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   配置处理控制器($$)*/

!defined('Q_PATH') && exit;

class OptionController extends AController{
	
	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

	public function update_option(){
		if(isset($_POST['options'])){
			$arrOptions=Q::G('options','P');
		}else{
			$arrOptions=array();
		}

		foreach($arrOptions as $sKey=>$val){
			$val=trim($val);
			$oOptionModel=OptionModel::F('option_name=?',$sKey)->getOne();
			$oOptionModel->option_value=$val;
			$oOptionModel->save('update');
			if($oOptionModel->isError()){
				$this->E($oOptionModel->getErrorMessage());
			}
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('option');

		// 保存管理员设置
		$sUid=trim(Q::G('admin_userid','P'));

		$arrUserid=explode(',',$sUid);
		$arrUserid=array_unique($arrUserid);
		if(!in_array(1,$arrUserid)){
			$arrUserid[]=1;
		}

		$sUid=trim(implode(',',$arrUserid),',');
		Core_Extend::changeAppconfig('ADMIN_USERID',$sUid);

		$this->S(Q::L('配置更新成功','Controller'));
	}

	public function admin(){
		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}

		// 读取超级管理员信息
		$sUid=Q::C('ADMIN_USERID');
		$arrUsers=UserModel::F()->where(array('user_id'=>array('in',$sUid)))->order('user_id DESC')->getAll();
		
		$this->assign('arrUsers',$arrUsers);
		$this->assign('sUid',$sUid);
		
		$this->index();
	}

	public function show(){
		$this->index();
	}

}
