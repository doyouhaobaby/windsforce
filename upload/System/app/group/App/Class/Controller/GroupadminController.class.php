<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组管理控制器($$)*/

!defined('Q_PATH') && exit;

class GroupadminController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();

		$nGroupid=intval(Q::G('gid'));
		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($nGroupid);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		if(!Groupadmin_Extend::checkAdminGroupRbac($nGroupid)){
			$this->E(Q::L('你没有管理小组的权限','Controller'));
		}
	}
	
	public function index(){
		$this->child('Groupadmin@Index','index');
	}

	public function save(){
		$this->child('Groupadmin@Save','index');
	}

	public function headerbg(){
		$this->child('Groupadmin@Headerbg','index');
	}

	public function headerbg_add(){
		$this->child('Groupadmin@Headerbgadd','index');
	}
	
	public function headerbg_delete(){
		$this->child('Groupadmin@Headerbgdelete','index');
	}

	public function icon(){
		$this->child('Groupadmin@Icon','index');
	}

	public function icon_add(){
		$this->child('Groupadmin@Iconadd','index');
	}

	public function icon_delete(){
		$this->child('Groupadmin@Icondelete','index');
	}

	public function category(){
		$this->child('Groupadmin@Category','index');
	}
	
	public function category_update(){
		$this->child('Groupadmin@Categoryupdate','index');
	}

	public function topiccategory(){
		$this->child('Groupadmin@Topiccategory','index');
	}

	public function topiccategory_add(){
		$this->child('Groupadmin@Topiccategoryadd','index');
	}

	public function updatetopiccategory(){
		$this->child('Groupadmin@Updatetopiccategory','index');
	}

	public function topiccategory_update(){
		$this->child('Groupadmin@Topiccategoryupdate','index');
	}

	public function topiccategory_delete(){
		$this->child('Groupadmin@Topiccategorydelete','index');
	}

	public function user_delete(){
		$this->child('Groupadmin@Userdelete','index');
	}

	public function admins_update(){
		$this->child('Groupadmin@Adminsupdate','index');
	}

}
