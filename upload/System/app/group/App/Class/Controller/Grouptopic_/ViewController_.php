<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   查看帖子控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息函数库 */
require(Core_Extend::includeFile('function/Profile_Extend'));

/** 导入home应用配置值 */
if(!Q::classExists('HomeoptionModel')){
	require_once(WINDSFORCE_PATH.'/System/app/home/App/Class/Model/HomeoptionModel.class.php');
}

/** 载入home应用配置信息 */
if(!isset($GLOBALS['_cache_']['home_option'])){
	Core_Extend::loadCache('home_option');
}

class View_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('id','G')); // 帖子ID
		$nPage=intval(Q::G('page','G')); // 分页数量
		
		// 帖子是否为新风格
		$nStyle=Q::cookie('group_grouptopicstyle')?intval(Q::cookie('group_grouptopicstyle')):$GLOBALS['_cache_']['group_option']['group_grouptopicstyle'];
		if(!in_array($nStyle,array(1,2))){
			$nStyle=1;
		}

		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nId)
			->join(Q::C('DB_PREFIX').'group AS B','B.group_icon,B.group_totaltodaynum,B.group_isopen,B.group_topicnum,B.group_topiccomment,B.group_usernum,B.group_listdescription,B.group_nikename,B.group_name,B.group_color,B.group_isrecommend,B.group_headerbg,B.group_ispost','B.group_id=A.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
			->join(Q::C('DB_PREFIX').'userprofile AS D','D.userprofile_gender,D.userprofile_qq,D.userprofile_site','A.user_id=D.user_id')
			->join(Q::C('DB_PREFIX').'user AS E','E.create_dateline AS user_create_dateline,E.user_lastlogintime,E.user_nikename,E.user_name,E.user_sign','A.user_id=E.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS F','F.*','A.user_id=F.user_id')
			->joinLeft(Q::C('DB_PREFIX').'online AS G','G.online_isstealth,G.online_ip','A.user_id=G.user_id')
			->join(Q::C('DB_PREFIX').'grouptopiccontent AS H','H.grouptopic_content','A.grouptopic_id=H.grouptopic_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->E(Q::L('你访问的主题不存在或已删除','Controller'));
		}

		// 验证小组权限
		try{
			Groupadmin_Extend::checkGroup($arrGrouptopic);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 判断邮件等外部地址过来的查找评论地址
		$nIsolationCommentid=intval(Q::G('isolation_commentid','G'));
		if($nIsolationCommentid){
			$result=GrouptopiccommentModel::getCommenturlByid($nIsolationCommentid);
			if($result===false){
				$this->E(Q::L('该条回帖已被删除、屏蔽或者尚未通过审核','Controller'));
			}

			C::urlGo($result);
			exit();
		}

		// 取得用户是否加入了小组 && 用户在小组中的角色
		$arrGroupuser=Group_Extend::getGroupuser($arrGrouptopic['group_id']);

		// 更新点击量
		Model::M_('grouptopic')->updateWhere(array('grouptopic_views'=>$arrGrouptopic['grouptopic_views']+1),'grouptopic_id=?',$nId);
		$arrGrouptopic['grouptopic_views']++;

		// 帖子缩略图
		if($arrGrouptopic['grouptopic_thumb']){
			$arrGrouptopic['grouptopic_content']='<div class="grouptopicthumb"><div class="grouptopicthumb_title">'.Q::L('主题缩略图','Controller').'</div><p>[attachment]'.$arrGrouptopic['grouptopic_thumb'].'[/attachment]</p></div>'.$arrGrouptopic['grouptopic_content'];
		}

		// 读取帖子标签
		$arrGrouptopictags=Model::F_('grouptopictagindex','@A','A.grouptopic_id=?',$arrGrouptopic['grouptopic_id'])
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'grouptopictag AS B','B.grouptopictag_name,B.grouptopictag_count','A.grouptopictag_id=B.grouptopictag_id')
			->order('B.create_dateline DESC')
			->getAll();

		// 判断用户是否回复过帖子
		if($arrGrouptopic['grouptopic_onlycommentview']==1){
			$bHavecomment=false;
			if($GLOBALS['___login___']!==false){
				if($arrGrouptopic['user_id']==$GLOBALS['___login___']['user_id']){
					$bHavecomment=true;
				}else{
					$arrTrygrouptopiccomment=Model::F_('grouptopiccomment','user_id=? AND grouptopic_id=?',$GLOBALS['___login___']['user_id'],$arrGrouptopic['grouptopic_id'])
						->setColumns('grouptopiccomment_id')
						->getOne();
					if(!empty($arrTrygrouptopiccomment['grouptopiccomment_id'])){
						$bHavecomment=true;
					}
				}
			}
			$this->assign('bHavecomment',$bHavecomment);
		}
		
		// 回复列表
		$arrWhere=array();
		$arrWhere['A.grouptopiccomment_status']=1;
		$arrWhere['A.grouptopic_id']=$arrGrouptopic['grouptopic_id'];
		
		if(Groupadmin_Extend::checkCommentadminRbac($arrGrouptopic,array('group@grouptopicadmin@auditcomment'))){
			$arrWhere['A.grouptopiccomment_status']=array('neq',CommonModel::STATUS_RECYLE);
		}

		$nTotalComment=Model::F_('grouptopiccomment','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalComment,$GLOBALS['_cache_']['group_option']['grouptopic_listcommentnum'],'@group://topic@?id='.$arrGrouptopic['grouptopic_id']);
		$arrComments=Model::F_('grouptopiccomment','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'userprofile AS D','D.userprofile_gender,D.userprofile_qq,D.userprofile_site','A.user_id=D.user_id')
			->join(Q::C('DB_PREFIX').'user AS E','E.create_dateline AS user_create_dateline,E.user_lastlogintime,E.user_nikename,E.user_name,E.user_sign','A.user_id=E.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS F','F.*','A.user_id=F.user_id')
			->joinLeft(Q::C('DB_PREFIX').'online AS G','G.online_isstealth,G.online_ip','A.user_id=G.user_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccomment AS H','H.grouptopiccomment_id AS parent_grouptopiccomment_id,H.user_id AS parent_user_id,H.grouptopiccomment_name AS parent_grouptopiccomment_name,H.grouptopiccomment_content AS parent_grouptopiccomment_content,H.grouptopic_id AS parent_grouptopic_id','A.grouptopiccomment_parentid=H.grouptopiccomment_id')
			->order('A.grouptopiccomment_status ASC,A.grouptopiccomment_stickreply DESC,A.create_dateline '.($arrGrouptopic['grouptopic_ordertype']==1?'DESC':'ASC'))
			->limit($oPage->S(),$oPage->N())
			->distinct(true)
			->getAll();

		// 读取回帖回收站数量
		if(Core_Extend::isAdmin()){
			$nTotalRecyclebinComment=Model::F_('grouptopiccomment')->where(array('grouptopic_id'=>$arrGrouptopic['grouptopic_id'],'grouptopiccomment_status'=>CommonModel::STATUS_RECYLE))
				->all()
				->getCounts();
			$this->assign('nTotalRecyclebinComment',$nTotalRecyclebinComment);
		}

		Core_Extend::getSeo($this,array(
			'title'=>$arrGrouptopic['grouptopic_title'].' - '.$arrGrouptopic['group_nikename'].Q::L('小组','Controller'),
			'keywords'=>$arrGrouptopic['grouptopic_title'].','.$arrGrouptopic['group_nikename'].Q::L('小组','Controller')));
		
		$this->assign('nStyle',$nStyle);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('arrGroup',$arrGrouptopic);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('arrGrouptopictags',$arrGrouptopictags);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrComments',$arrComments);
		$this->assign('nPage',$nPage);
		if($nStyle==2){
			$this->display('grouptopic+viewnew');
		}else{
			$this->display('grouptopic+view');
		}
	}

	public function get_commentfloor($nIndex,$nEverynum){
		$nPage=intval(Q::G('page','G'));
		if($nPage>=2){
			$nIndex=($nPage-1)*$nEverynum+$nIndex;
		}

		switch($nIndex){
			case 1:
				return Q::L('沙发','Controller');
				break;
			case 2:
				return Q::L('板凳','Controller');
				break;
			case 3:
				return Q::L('地板','Controller');
				break;
			default:
				return $nIndex;
				break;
		}
	}

}
