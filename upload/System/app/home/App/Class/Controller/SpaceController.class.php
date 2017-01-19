<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人空间显示($$)*/

!defined('Q_PATH') && exit;

class SpaceController extends InitController{

	public function index(){
		$sType=trim(Q::G('type','G'));
		$this->assign('sType',$sType);

		if(empty($sType)){
			$sId=trim(Q::G('id','G'));
			if(!in_array($sId,array('ratings'))){
				$this->child('Space@Base','index');
			}else{
				$this->{$sId}();
			}
		}else{
			if(method_exists($this,$sType)){
				$this->{$sType}();
			}else{
				Q::E(sprintf('method %s not exists',$sType));
			}
		}
	}

	public function rating(){
		$this->child('Space@Rating','index');
	}

	public function feed(){
		$this->child('Space@Feed','index');
	}
	
	public function ratings(){
		$this->child('Space@Ratings','index');
	}

	public function guestbook(){
		$this->child('Space@Guestbook','index');
	}

	public function add_userguestbook(){
		$this->child('Space@Adduserguestbook','index');
	}

	public function apps(){
		$this->child('Space@Apps','index');
	}

}
