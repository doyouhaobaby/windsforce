<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点新鲜事评论控制器($$)*/

!defined('Q_PATH') && exit;

class HomefreshcommentController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.homefreshcomment_name']=array('like','%'.Q::G('homefreshcomment_name').'%');
		$arrMap['A.homefreshcomment_content']=array('like','%'.Q::G('homefreshcomment_content').'%');

		// 新鲜事检索
		$nFid=intval(Q::G('fid','G'));
		if($nFid){
			$oHomefresh=HomefreshModel::F('homefresh_id=?',$nFid)->getOne();
			if(!empty($oHomefresh['homefresh_id'])){
				$arrMap['A.homefresh_id']=$nFid;
				$this->assign('oHomefresh',$oHomefresh);
			}
		}

		// 用户检索
		$nUid=intval(Q::G('uid','G'));
		if($nUid){
			$oUser=UserModel::F('user_id=?',$nUid)->getOne();
			if(!empty($oUser['user_id'])){
				$arrMap['A.user_id']=$nUid;
				$this->assign('oUser',$oUser);
			}
		}
		
		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		parent::index('homefreshcomment',false);
		$this->display(Admin_Extend::template('home','homefreshcomment/index'));
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::forbid('homefreshcomment',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=Q::G('value');
		parent::resume('homefreshcomment',$nId,true);
	}

	public function add(){
		$this->E(Q::L('后台无法添加新鲜事评论','__APPHOME_COMMON_LANG__@Controller'));
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		parent::edit('homefreshcomment',$nId,false);
		$this->display(Admin_Extend::template('home','homefreshcomment/add'));
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('homefreshcomment',$nId);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);

		// 将新鲜事评论子评论的父级ID改为当前的评论的父级ID(节点移位)
		foreach($arrIds as $nId){
			$oHomefreshcomment=HomefreshcommentModel::F('homefreshcomment_id=?',$nId)->getOne();
			if(!empty($oHomefreshcomment['homefreshcomment_id'])){
				$arrHomefreshchildcomments=HomefreshcommentModel::F('homefreshcomment_parentid=?',$nId)->getAll();
				if(is_array($arrHomefreshchildcomments)){
					foreach($arrHomefreshchildcomments as $oHomefreshchildcomment){
						$oHomefreshchildcomment->homefreshcomment_parentid=$oHomefreshcomment['homefreshcomment_parentid'];
						$oHomefreshchildcomment->save('update');
						if($oHomefreshchildcomment->isError()){
							$this->E($oHomefreshchildcomment->getErrorMessage());
						}
					}
				}
			}
		}
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('homefreshcomment',$sId);
	}

	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		parent::foreverdelete('homefreshcomment',$sId,true);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 更新新鲜事评论数量
		foreach($arrIds as $nId){
			// 更新评论数量
			$oHomefresh=Q::instance('HomefreshModel');
			$oHomefresh->updateHomefreshcommentnum($sId);
			if($oHomefresh->isError()){
				$oHomefresh->getErrorMessage();
			}
		}
	}
	
}
