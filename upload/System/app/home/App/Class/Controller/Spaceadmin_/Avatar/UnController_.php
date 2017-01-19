<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除头像($$)*/

!defined('Q_PATH') && exit;

class Un_C_Controller extends InitController{

	public function index(){
		require_once(Core_Extend::includeFile('function/Avatar_Extend'));
		
		try{
			Avatar_Extend::deleteAvatar();
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$this->S(Q::L('删除头像成功了','Controller'));
	}

}
