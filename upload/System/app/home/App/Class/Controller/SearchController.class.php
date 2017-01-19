<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点搜索功能($$)*/

!defined('Q_PATH') && exit;

class SearchController extends InitController{

	public function init__(){
		parent::init__();

		if($GLOBALS['_option_']['allow_search']==0){
			$this->E(Q::L('系统关闭了搜索功能','Controller'));
		}
	}
	
	public function index(){
		$this->child('Search@Index','index');
	}
	
	public function user(){
		$this->child('Search@User','index');
	}

	public function result(){
		$this->child('Search@Index','result');
	}
	
}
