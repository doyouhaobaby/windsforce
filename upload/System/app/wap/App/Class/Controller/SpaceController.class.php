<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Wap个人空间显示($$)*/

!defined('Q_PATH') && exit;

class SpaceController extends WInitController{

	public function index(){
		$sType=trim(Q::G('type','G'));
		$this->assign('sType',$sType);

		if(empty($sType)){
			$this->child('Space@Base','index');
		}else{
			if(method_exists($this,$sType)){
				$this->{$sType}();
			}else{
				Q::E(sprintf('method %s not exists',$sType));
			}
		}
	}

}
