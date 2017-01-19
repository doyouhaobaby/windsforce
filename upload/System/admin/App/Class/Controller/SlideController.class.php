<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   系统首页广告设置($$)*/

!defined('Q_PATH') && exit;

class SlideController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.slide_title']=array('like',"%".Q::G('slide_title')."%");
		$arrMap['A.slide_url']=array('like',"%".Q::G('slide_url')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bIndex_(){
		$arrOptionData=$GLOBALS['_option_'];
		$this->assign('arrOptions',$arrOptionData);
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');
		$nSlideduration=$arrOptions['slide_duration'];
		$nSlideDelay=intval($arrOptions['slide_delay']);

		if($nSlideduration<0.1 || $nSlideduration>1){
			$_POST['options']['slide_duration']=0.3;
		}

		if($nSlideDelay<1){
			$_POST['options']['slide_delay']=5;
		}

		$oOptionController=new OptionController();
		$oOptionController->update_option();
	}

	public function bEdit_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_slide($nId)){
			$this->E(Q::L('系统幻灯片无法编辑','Controller'));
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_slide($nId)){
				$this->E(Q::L('系统幻灯片无法删除','Controller'));
			}
		}
	}
	
	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("slide");
	}

	public function afterInputChangeAjax($sName=null){
		$this->aInsert();
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function aForeverdelete_deep($sId){
		$this->aInsert();
	}

	public function aForeverdelete($sId){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}
	
	protected function aResume(){
		$this->aInsert();
	}

	public function is_system_slide($nId){
		$nId=intval($nId);

		$oSlide=SlideModel::F('slide_id=?',$nId)->setColumns('slide_id,slide_issystem')->getOne();
		if(empty($oSlide['slide_id'])){
			return false;
		}

		if($oSlide['slide_issystem']==1){
			return true;
		}

		return false;
	}

}
