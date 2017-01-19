<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台个人中心管理($$)*/

!defined('Q_PATH') && exit;

class SpaceadminController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();
	}

	public function index(){
		$this->child('Spaceadmin@Information/Index','index');
	}

	public function change_info(){
		$this->child('Spaceadmin@Information/Change','index');
	}

	public function usersign(){
		$this->child('Spaceadmin@Information/Usersign','index');
	}

	public function avatar(){
		$this->child('Spaceadmin@Avatar/Index','index');
	}

	public function avatar_upload(){
		$this->child('Spaceadmin@Avatar/Upload','index');
	}

	public function avatar_savecrop(){
		$this->child('Spaceadmin@Avatar/Savecrop','index');
	}

	public function avatar_un(){
		$this->child('Spaceadmin@Avatar/Un','index');
	}

	public function password(){
		$this->child('Spaceadmin@Password/Index','index');
	}

	public function change_pass(){
		$this->child('Spaceadmin@Password/Change','index');
	}

	public function socia(){
		$this->child('Spaceadmin@Socia/Index','index');
	}

	public function socia_account(){
		$this->child('Spaceadmin@Socia','account');
	}

	public function tag(){
		$this->child('Spaceadmin@Hometag/Index','index');
	}

	public function add_hometag(){
		$this->child('Spaceadmin@Hometag/Add','index');
	}

	public function delete_hometag(){
		$this->child('Spaceadmin@Hometag/Delete','index');
	}

	public function promotion(){
		$this->child('Spaceadmin@Promotion/Index','index');
	}

	public function verifyemail(){
		$this->child('Spaceadmin@Verifyemail/Index','index');
	}

	public function dorevifyemail(){
		$this->child('Spaceadmin@Verifyemail/Do','index');
	}

	public function checkrevifyemail(){
		$this->child('Spaceadmin@Verifyemail/Check','index');
	}

	public function unrevifyemail(){
		$this->child('Spaceadmin@Verifyemail/Un','index');
	}

	public function rating(){
		$this->child('Spaceadmin@Rating/Index','index');
	}

	public function creditrule(){
		$this->child('Spaceadmin@Rating/Creditrule','index');
	}

	public function creditlog(){
		$this->child('Spaceadmin@Rating/Creditlog','index');
	}

	public function creditrulelog(){
		$this->child('Spaceadmin@Rating/Creditrulelog','index');
	}

	public function transfer(){
		$this->child('Spaceadmin@Rating/Transfer','index');
	}

	public function do_transfer(){
		$this->child('Spaceadmin@Rating/Dotransfer','index');
	}

	public function exchange(){
		$this->child('Spaceadmin@Rating/Exchange','index');
	}

	public function do_exchange(){
		$this->child('Spaceadmin@Rating/Doexchange','index');
	}

}
