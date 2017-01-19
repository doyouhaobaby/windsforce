<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主页杂项控制器($$)*/

!defined('Q_PATH') && exit;

class MiscController extends InitController{

	public function district(){
		$this->child('Misc@District','index');
	}
	
	public function district_php(){
		$this->child('Misc@District','php');
	}

	public function newpmnum(){
		$this->child('Misc@Newpmnum','index');
	}

	public function style(){
		$this->child('Misc@Style','index');
	}

	public function extendstyle(){
		$this->child('Misc@Extendstyle','index');
	}

	public function init_system(){
		$this->child('Misc@Initsystem','index');
	}

	public function music(){
		$this->child('Misc@Media','index');
	}	
	
	public function video(){
		$this->child('Misc@Media','video');
	}

	public function avatar(){
		$this->child('Misc@Avatar','index');
	}

	public function dialogstyle(){
		$this->child('Misc@Dialogstyle','index');
	}

	public function newnoticenum(){
		$this->child('Misc@Newnoticenum','index');
	}

	public function ubb(){
		$this->child('Misc@Ubb','index');
	}

	public function stealth_online(){
		$this->child('Misc@Stealthonline','index');
	}

	public function socia_login(){
		$this->child('Misc@Socialogin','index');
	}

	public function thumb(){
		$this->child('Misc@Thumb','index');
	}

	public function baidumap(){
		$this->child('Misc@Baidumap','index');
	}

}
