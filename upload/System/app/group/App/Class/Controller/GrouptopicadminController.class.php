<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组操作控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopicadminController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();

		$nGroupid=intval(Q::G('groupid'));
		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($nGroupid);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
	}
	
	public function deletetopic_dialog(){
		$this->child('Grouptopicadmin@Deletetopicdialog','index');
	}

	public function deletetopic(){
		$this->child('Grouptopicadmin@Deletetopic','index');
	}

	public function closetopic_dialog(){
		$this->child('Grouptopicadmin@Closetopicdialog','index');
	}

	public function closetopic(){
		$this->child('Grouptopicadmin@Closetopic','index');
	}

	public function sticktopic_dialog(){
		$this->child('Grouptopicadmin@Sticktopicdialog','index');
	}

	public function sticktopic(){
		$this->child('Grouptopicadmin@Sticktopic','index');
	}

	public function digesttopic_dialog(){
		$this->child('Grouptopicadmin@Digesttopicdialog','index');
	}

	public function digesttopic(){
		$this->child('Grouptopicadmin@Digesttopic','index');
	}

	public function recommendtopic_dialog(){
		$this->child('Grouptopicadmin@Recommendtopicdialog','index');
	}

	public function recommendtopic(){
		$this->child('Grouptopicadmin@Recommendtopic','index');
	}

	public function hidetopic_dialog(){
		$this->child('Grouptopicadmin@Hidetopicdialog','index');
	}

	public function hidetopic(){
		$this->child('Grouptopicadmin@Hidetopic','index');
	}
	
	public function audittopic_dialog(){
		$this->child('Grouptopicadmin@Audittopicdialog','index');
	}

	public function audittopic(){
		$this->child('Grouptopicadmin@Audittopic','index');
	}

	public function movetopic_dialog(){
		$this->child('Grouptopicadmin@Movetopicdialog','index');
	}

	public function movetopic(){
		$this->child('Grouptopicadmin@Movetopic','index');
	}

	public function categorytopic_dialog(){
		$this->child('Grouptopicadmin@Categorytopicdialog','index');
	}

	public function categorytopic(){
		$this->child('Grouptopicadmin@Categorytopic','index');
	}

	public function tagtopic_dialog(){
		$this->child('Grouptopicadmin@Tagtopicdialog','index');
	}

	public function tagtopic(){
		$this->child('Grouptopicadmin@Tagtopic','index');
	}

	public function colortopic_dialog(){
		$this->child('Grouptopicadmin@Colortopicdialog','index');
	}

	public function colortopic(){
		$this->child('Grouptopicadmin@Colortopic','index');
	}

	public function uptopic_dialog(){
		$this->child('Grouptopicadmin@Uptopicdialog','index');
	}

	public function uptopic(){
		$this->child('Grouptopicadmin@Uptopic','index');
	}

	public function deletecomment_dialog(){
		$this->child('Grouptopicadmin@Deletecommentdialog','index');
	}

	public function deletecomment(){
		$this->child('Grouptopicadmin@Deletecomment','index');
	}

	public function hidecomment_dialog(){
		$this->child('Grouptopicadmin@Hidecommentdialog','index');
	}

	public function hidecomment(){
		$this->child('Grouptopicadmin@Hidecomment','index');
	}

	public function stickreplycomment_dialog(){
		$this->child('Grouptopicadmin@Stickreplycommentdialog','index');
	}

	public function stickreplycomment(){
		$this->child('Grouptopicadmin@Stickreplycomment','index');
	}

	public function auditcomment_dialog(){
		$this->child('Grouptopicadmin@Auditcommentdialog','index');
	}

	public function auditcomment(){
		$this->child('Grouptopicadmin@Auditcomment','index');
	}

}
