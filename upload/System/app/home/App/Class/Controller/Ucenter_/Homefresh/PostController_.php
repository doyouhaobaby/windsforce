<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加新鲜事高级版($$)*/

!defined('Q_PATH') && exit;

class Post_C_Controller extends InitController{

	public function index(){
		try{
			$arrData=array();

			$oLasthomefresh=HomefreshModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->order('create_dateline DESC')->getOne();
			if(!empty($oLasthomefresh['homefresh_id'])){
				$arrData['lasttime']=$oLasthomefresh['create_dateline'];
			}
			
			Core_Extend::checkSpam($arrData);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 禁止前台发布新鲜事
		if($GLOBALS['_cache_']['home_option']['homefresh_frontadd']==0 && !Core_Extend::isAdmin()){
			$this->E(Q::L('前台禁止发布新鲜事','Controller'));
		}

		$nType=intval(Q::G('model','G'));
		if(!in_array($nType,array(1,2,3,4,5))){
			$this->E(Q::L('不存在的新鲜事类型','Controller'));
		}

		// 载入文件分类
		Core_Extend::loadCache('category');

		Core_Extend::getSeo($this,array('title'=>$this->title_($nType)));

		$this->assign('nType',$nType);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);
		$this->display('homefresh+add'.$nType);
	}

	protected function title_($nType){
		$sTitle=Q::L('分享','Controller');

		switch($nType){
			case 1:
				$sTitle.=Q::L('文字','Controller');
				break;
			case 2:
				$sTitle.=Q::L('音乐','Controller');
				break;
			case 3:
				$sTitle.=Q::L('图片','Controller');
				break;
			case 4:
				$sTitle.=Q::L('视频','Controller');
				break;
			case 5:
				$sTitle.=Q::L('电影','Controller');
				break;
			//case 6:
				//$sTitle.=Q::L('购物','Controller');
				//break;
		}

		return $sTitle;
	}

}
