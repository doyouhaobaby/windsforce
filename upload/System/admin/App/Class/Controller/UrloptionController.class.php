<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   URL美化控制器($$)*/

!defined('Q_PATH') && exit;

class UrloptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$arrUrlModels=array(
			array('name'=>Q::L('普通模式','Controller'),'value'=>0),
			array('name'=>Q::L('PATHINFO模式','Controller'),'value'=>1),
			array('name'=>Q::L('REWRITE模式','Controller'),'value'=>2),
			array('name'=>Q::L('兼容模式','Controller'),'value'=>3),
		);
		
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('arrUrlModels',$arrUrlModels);
		$this->display();
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');
		$nUrlmodel=intval($arrOptions['url_model']);
		$sUrldomain=trim($arrOptions['url_domain']);

		if(!in_array($nUrlmodel,array(0,1,2,3))){
			$nUrlmodel=1;
		}

		// 修改URL模式设置
		Core_Extend::updateOption(
			array(
				'url_model'=>$nUrlmodel,
				'url_domain'=>$sUrldomain
			)
		);
		
		Core_Extend::changeAppconfig(
			array(
				'URL_MODEL'=>$nUrlmodel,
				'URL_DOMAIN'=>$sUrldomain
			)
		);

		// 需要删除导航缓存
		$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND'];
		$bAllowMem=Core_Extend::memory('check');

		$bAllowMem && self::memory('delete','nav');

		$sCachefile=WINDSFORCE_PATH.'/~@~/data/~@nav.php';
		$bIsFilecache && (is_file($sCachefile) && @unlink($sCachefile));

		$this->S(Q::L('修改URL模式成功','Controller'));
	}

}
