<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉控制器($$)*/

!defined('Q_PATH') && exit;

class UserappealController extends InitController{

	public function index(){
		$this->child('Userappeal@Index','index');
	}

	public function step2(){
		$this->child('Userappeal@Step2','index');
	}

	public function step3(){
		$this->child('Userappeal@Step3','index');
	}

	public function step4(){
		$this->child('Userappeal@Step4','index');
	}
	
	public function tocomputer(){
		$this->child('Userappeal@Tocomputer','index');
	}

	public function tomail(){
		$this->child('Userappeal@Tomail','index');
	}

	public function retrieve(){
		$this->child('Userappeal@Retrieve','index');
	}

	public function schedule(){
		$this->child('Userappeal@Schedule','index');
	}

	public function schedule_result(){
		$this->child('Userappeal@Scheduleresult','index');
	}

}
