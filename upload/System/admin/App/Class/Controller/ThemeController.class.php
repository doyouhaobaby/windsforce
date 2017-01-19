<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   模板管理控制器($$)*/

!defined('Q_PATH') && exit;

class ThemeController extends AController{
	
	public function filter_(&$arrMap){
		$arrMap['A.theme_name']=array('like',"%".Q::G('theme_name')."%");
		$arrMap['A.theme_dirname']=array('like',"%".Q::G('theme_dirname')."%");
		$arrMap['A.theme_copyright']=array('like',"%".Q::G('theme_copyright')."%");
	}

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function bUpdate_($sThemeDirname=''){
		if(empty($sThemeDirname)){
			$_POST['theme_dirname']=ucfirst($_POST['theme_dirname']);
			$sThemeDirname=trim(Q::G('theme_dirname','P'));
		}
			
		$sThemeDirname=WINDSFORCE_PATH.'/user/theme/'.$sThemeDirname;
		if(!is_dir($sThemeDirname)){
			$this->E(Q::L('模板目录 %s 不存在','Controller',null,str_replace(WINDSFORCE_PATH,'{WINDSFORCE_PATH}',$sThemeDirname)));
		}
	}
	
	public function bInsert_(){
		$this->bUpdate_();
	}
	
	public function bForeverdelete_(){
		$this->bForeverdelete_deep_();
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_theme($nId)){
				$this->E(Q::L('系统模板无法删除','Controller'));
			}
		}
	}

	public function bEdit_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_theme($nId)){
			$this->E(Q::L('系统模板无法编辑','Controller'));
		}
	}

	public function is_system_theme($nId){
		if($nId==1){
			return true;
		}
		return false;
	}
	
}
