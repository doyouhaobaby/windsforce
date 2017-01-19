<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   加载更多新鲜事($$)*/

!defined('Q_PATH') && exit;

class Gethomefresh_C_Controller extends InitController{

	public function index(){
		$nPage=intval(Q::G('page','G'));
		$nCid=intval(Q::G('cid','G'));

		if($nPage<2){
			$nPage=2;
		}
		$nPage=$nPage-1;

		$nHomenewhomefreshnum=intval($GLOBALS['_option_']['home_newhomefresh_num']);
		if($nHomenewhomefreshnum<2){
			$nHomenewhomefreshnum=2;
		}

		$arrWhere=array();
		$arrWhere['A.homefresh_status']=1;
		if($nType && in_array($nType,array(1,2,3,4,5))){
			$arrWhere['A.homefresh_type']=$nType;
		}
		if($nCid){
			$arrWhere['A.homefreshcategory_id']=$nCid;
		}
		
		$arrHomefreshs=Model::F_('homefresh','@A')
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,,A.homefresh_viewnum,A.homefresh_type,A.homefresh_thumb,A.homefreshcategory_id')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->joinLeft(Q::C('DB_PREFIX').'homefreshcategory AS C','C.homefreshcategory_name','A.homefreshcategory_id=C.homefreshcategory_id')
			->where($arrWhere)
			->order('A.homefresh_id DESC')
			->limit($nPage*$nHomenewhomefreshnum,$nHomenewhomefreshnum)
			->asArray()
			->getAll();
			
		$sGoodCookie=Q::cookie('homefresh_goodnum');
		$arrGoodCookie=explode(',',$sGoodCookie);

		$this->assign('arrGoodCookie',$arrGoodCookie);
		$this->assign('arrHomefreshs',$arrHomefreshs);
		$this->assign('nPage',$nPage);
		$this->display('public+gethomefresh');
	}

}
