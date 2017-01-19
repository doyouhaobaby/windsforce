<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组控制器($$)*/

!defined('Q_PATH') && exit;

class GroupController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.group_name']=array('like','%'.Q::G('group_name').'%');
		$arrMap['A.group_nikename']=array('like','%'.Q::G('group_nikename').'%');
		$arrMap['A.group_usernum']=array('egt',intval(Q::G('group_usernum')));
		$arrMap['A.group_topiccomment']=array('egt',intval(Q::G('group_topiccomment')));
		$arrMap['A.group_topicnum']=array('egt',intval(Q::G('group_topicnum')));

		// 群组是否推荐
		$nGroupIsrecommend=Q::G('group_isrecommend');
		if($nGroupIsrecommend!==null && $nGroupIsrecommend!=''){
			$arrMap['A.group_isrecommend']=$nGroupIsrecommend;
		}

		// 时间设置
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function index($sModel=null,$bDisplay=true){
		$this->bAdd_();
		parent::index('group',false);
		$this->display(Admin_Extend::template('group','group/index'));
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."groupcategory AS B','B.groupcategory_name','A.groupcategory_id=B.groupcategory_id')";
	}

	public function bAdd_(){
		$oGroupcategory=Q::instance('GroupcategoryModel');
		$oGroupcategoryTree=$oGroupcategory->getGroupcategoryTree();
		$this->assign('oGroupcategoryTree',$oGroupcategoryTree);
	}

	protected function AInsertObject_($oModel){
	}

	public function edit($sMode=null,$nId=null,$bDidplay=true){
		$nId=intval(Q::G('value','G'));
		$this->bAdd_();
		parent::edit('group',$nId,false);
		$this->display(Admin_Extend::template('group','group/add'));
	}
	
	public function bEdit_(){
		$this->bAdd_();
	}
	
	public function add(){
		$this->bAdd_();
		$this->display(Admin_Extend::template('group','group/add'));
	}

	public function check_name(){
		$sGroupName=trim(Q::G('group_name'));
		$nId=intval(Q::G('value'));

		if(!$sGroupName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['group_name']=$sGroupName;
		if($nId){
			$arrWhere['group_id']=array('neq',$nId);
		}

		$oGroup=GroupModel::F()->where($arrWhere)->setColumns('group_id')->getOne();
		if(empty($oGroup['group_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function update($sModel=null,$nId=null){
		$nId=Q::G('value');
		parent::update('group',$nId);
	}

	public function insert($sModel=null,$nId=null){
		$nId=Q::G('value');

		// 自动填充
		$_POST['group_roleleader']='组长';
		$_POST['group_roleadmin']='管理员';
		$_POST['group_roleuser']='成员';

		parent::insert('group',$nId);
	}

	public function bForeverdelete_deep_(){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 小组有帖子不能删除
		foreach($arrIds as $nId){
			$nTotalgrouptopic=GrouptopicModel::F('group_id=?',$nId)->all()->getCounts();
			if($nTotalgrouptopic>0){
				$this->E(Q::L('小组存在帖子，请先删除帖子','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}
	}
	
	public function foreverdelete($sModel=null,$sId=null,$bApp=false){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete('group',$sId,true);
	}

	public function foreverdelete_deep($sModel=null,$sId=null){
		$sId=Q::G('value');
		$this->bForeverdelete_deep_();
		parent::foreverdelete_deep('group',$sId);
	}

	protected function aForeverdelete_deep($sId){
		$sId=Q::G('value','G');
		$arrIds=explode(',',$sId);
		
		// 删除小组相关数据
		foreach($arrIds as $nId){
			// 删除小组的帖子分类
			$oGrouptopiccategoryMeta=GrouptopiccategoryModel::M();
			$oGrouptopiccategoryMeta->deleteWhere(array('group_id'=>$nId));

			// 删除小组用户
			$oGroupuserMeta=GroupuserModel::M();
			$oGroupuserMeta->deleteWhere(array('group_id'=>$nId));
		}
	}
	
	public function input_change_ajax($sName=null){
		parent::input_change_ajax('group');
	}

	public function forbid($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::forbid('group',$nId,true);
	}

	public function resume($sModel=null,$sId=null,$bApp=false){
		$nId=intval(Q::G('value','G'));
		parent::resume('group',$nId,true);
	}

	public function clear_recycle($sModel=null,$sField='status'){
		parent::clear_recycle('group',$sField);
	}

	public function recommend(){
		$nId=intval(Q::G('value','G'));

		$oGroup=Q::instance('GroupModel');
		$oGroup->recommend($nId,1);
		if($oGroup->isError()){
			$this->E($oGroup->getErrorMessage());
		}else{
			$this->S(Q::L('推荐成功','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}

	public function unrecommend(){
		$nId=intval(Q::G('value','G'));

		$oGroup=Q::instance('GroupModel');
		$oGroup->recommend($nId,0);
		if($oGroup->isError()){
			$this->E($oGroup->getErrorMessage());
		}else{
			$this->S(Q::L('取消推荐成功','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function icon(){
		$nId=intval(Q::G('value','G'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			// 取得ICON
			$sGroupIcon=Group_Extend::getGroupIcon($oGroup,true);

			$this->assign('sGroupIcon',$sGroupIcon);
			$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_icon_uploadfile_maxsize']));
			$this->assign('oGroup',$oGroup);
			$this->display(Admin_Extend::template('group','group/icon'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function icon_add(){
		$nId=intval(Q::G('value','P'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			require_once(Core_Extend::includeFile('function/Upload_Extend'));
			try{
				// 上传前删除早前的icon
				if(!empty($oGroup['group_icon'])){
					require_once(Core_Extend::includeFile('function/Upload_Extend'));
					Upload_Extend::deleteIcon('group',$oGroup['group_icon']);
					$oGroup->group_icon='';
					$oGroup->save('update');
					if($oGroup->isError()){
						$this->E($oGroup->getErrorMessage());
					}
				}

				// 执行上传
				$sPhotoDir=Upload_Extend::uploadIcon('group');
				$oGroup->group_icon=$sPhotoDir;
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
			
				$this->S(Q::L('图标设置成功','__APPGROUP_COMMON_LANG__@Controller'));
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function delete_icon(){
		$nId=intval(Q::G('value','G'));

		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			if(!empty($oGroup['group_icon'])){
				require_once(Core_Extend::includeFile('function/Upload_Extend'));
				Upload_Extend::deleteIcon('group',$oGroup['group_icon']);
				$oGroup->group_icon='';
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
				
				$this->S(Q::L('图标删除成功','__APPGROUP_COMMON_LANG__@Controller'));
			}else{
				$this->E(Q::L('图标不存在','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}

	public function headerbg(){
		$nId=intval(Q::G('value','G'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			// 读取系统背景
			$arrSystembgs=C::listDir(WINDSFORCE_PATH.'/System/app/group/Static/Images/groupbg',false,true);

			// 取得当前背景
			$sGroupHeaderbg=Group_Extend::getGroupHeaderbg($oGroup['group_headerbg']);

			$this->assign('sGroupHeaderbg',$sGroupHeaderbg);
			$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_headbg_uploadfile_maxsize']));
			$this->assign('oGroup',$oGroup);
			$this->display(Admin_Extend::template('group','group/headerbg'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}

	public function headerbg_add(){
		$nId=intval(Q::G('value','P'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			if($_FILES['headerbg']['error'][0]=='4'){
				if(isset($_POST['group_headerbg'])){
					$oGroup->group_headerbg=intval($_POST['group_headerbg']);
					$oGroup->save('update');
					if($oGroup->isError()){
						$this->E($oGroup->getErrorMessage());
					}
				}
			}else{
				require_once(Core_Extend::includeFile('function/Upload_Extend'));
				try{
					// 上传前删除早前的icon
					if(!empty($oGroup['group_headerbg']) && !Core_Extend::isPostInt($oGroup['group_headerbg'])){
						require_once(Core_Extend::includeFile('function/Upload_Extend'));
						Upload_Extend::deleteIcon('group',$oGroup['group_headerbg']);
						$oGroup->group_headerbg='';
						$oGroup->save('update');
						if($oGroup->isError()){
							$this->E($oGroup->getErrorMessage());
						}
					}

					// 执行上传
					$sPhotoDir=Upload_Extend::uploadIcon('group',array('width'=>940,'height'=>150,'uploadfile_maxsize'=>$GLOBALS['_cache_']['group_option']['group_headbg_uploadfile_maxsize'],'upload_saverule'=>array('Group_Extend','getHeaderbgName')));
					$oGroup->group_headerbg=$sPhotoDir;
					$oGroup->save('update');
					if($oGroup->isError()){
						$this->E($oGroup->getErrorMessage());
					}
				}catch(Exception $e){
					$this->E($e->getMessage());
				}
			}
			
			$this->S(Q::L('群组背景设置成功','__APPGROUP_COMMON_LANG__@Controller'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}

	public function delete_headerbg(){
		$nId=intval(Q::G('value','G'));

		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			if(!empty($oGroup['group_headerbg'])){
				if(!Core_Extend::isPostInt($oGroup['group_headerbg'])){
					require_once(Core_Extend::includeFile('function/Upload_Extend'));
					Upload_Extend::deleteIcon('group',$oGroup['group_headerbg']);
				}
			
				$oGroup->group_headerbg='';
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
				
				$this->S(Q::L('小组背景删除成功','__APPGROUP_COMMON_LANG__@Controller'));
			}else{
				$this->E(Q::L('小组背景不存在','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function groupcategory(){
		$nId=intval(Q::G('value','G'));
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			$this->bAdd_();
			$this->assign('oGroup',$oGroup);
			$this->display(Admin_Extend::template('group','group/groupcategory'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function update_category(){
		$nId=intval(Q::G('value'));
		$nCategoryId=intval(Q::G('groupcategory_id','G'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->getOne();
		if(!empty($oGroup['group_id'])){
			$oGroup->save('update');
			if($oGroup->isError()){
				$this->E($oGroup->getErrorMessage());
			}

			$this->S(Q::L('数据更新成功','Controller'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function topiccategory(){
		$nId=intval(Q::G('value'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			$arrGrouptopiccategorys=GrouptopiccategoryModel::F('group_id=?',$nId)->order('grouptopiccategory_sort ASC')->getAll('grouptopiccategory_id');
			$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
			$this->assign('nValue',$nId);
			$this->display(Admin_Extend::template('group','group/topiccategory'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function add_topiccategory(){
		$nId=intval(Q::G('value'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->query();
		if(!empty($oGroup['group_id'])){
			$oGrouptopiccategory=Q::instance('GrouptopiccategoryModel');
			$oGrouptopiccategory->insertGroupcategory($nId);
			if($oGrouptopiccategory->isError()){
				$this->E($oGrouptopiccategory->getErrorMessage());
			}else{
				$this->S(Q::L('添加帖子分类成功','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function delete_topic_category(){
		$nId=intval(Q::G('value'));
		$nGroupId=intval(Q::G('group_id'));
		
		$oGroupcategory=GrouptopiccategoryModel::F('grouptopiccategory_id=? AND group_id=?',$nId,$nGroupId)->query();
		if(!empty($oGroupcategory['grouptopiccategory_id'])){
			$oGrouptopiccategoryMeta=GrouptopiccategoryModel::M();
			$oGrouptopiccategoryMeta->deleteWhere(array('grouptopiccategory_id'=>$nId));
			if($oGrouptopiccategoryMeta->isError()){
				$this->E($oGrouptopiccategoryMeta->getErrorMessage());
			}

			// 将分类ID重置为0
			Q::instance('GrouptopicModel')->resetCategory($nId);
			
			$this->S(Q::L('删除帖子分类成功','__APPGROUP_COMMON_LANG__@Controller'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function update_topic_category(){
		$nId=intval(Q::G('value'));
		$nGroupId=intval(Q::G('group_id'));
		
		$oGrouptopiccategory=GrouptopiccategoryModel::F('grouptopiccategory_id=? AND group_id=?',$nId,$nGroupId)->query();
		if(!empty($oGrouptopiccategory['grouptopiccategory_id'])){
			$this->assign('oGrouptopiccategory',$oGrouptopiccategory);
			$this->assign('nValue',$nGroupId);
			$this->assign('nCategoryId',$nId);
			$this->display(Admin_Extend::template('group','group/update_topic_category'));
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}
	
	public function update_topiccategory(){
		$nId=intval(Q::G('value'));
		
		$oGroupcategory=GrouptopiccategoryModel::F('grouptopiccategory_id=?',$nId)->order('grouptopiccategory_sort DESC')->query();
		if(!empty($oGroupcategory['grouptopiccategory_id'])){
			$oGroupcategory->save('update');
			if($oGroupcategory->isError()){
				$this->E($oGroupcategory->getErrorMessage());
			}else{
				$this->S(Q::L('更新帖子分类成功','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}else{
			$this->E(Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
	}

	public function user(){
		$nId=intval(Q::G('value'));
		
		$oGroup=GroupModel::F('group_id=?',$nId)->getOne();
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}

		// 读取小组创始人
		$arrGroupleaders=Model::F_('groupuser','@A','group_id=? AND groupuser_isadmin=2',$nId)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->getAll();

		// 读取小组管理员
		$arrGroupadmins=Model::F_('groupuser','@A','group_id=? AND groupuser_isadmin=1',$nId)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->getAll();

		$sGroupleader=$sGroupadmin='';
		foreach($arrGroupleaders as $arrGroupleader){
			$sGroupleader.=$arrGroupleader['user_id'].',';
		}
		foreach($arrGroupadmins as $arrGroupadmin){
			$sGroupadmin.=$arrGroupadmin['user_id'].',';
		}
		
		$sGroupleader=rtrim($sGroupleader,',');
		$sGroupadmin=rtrim($sGroupadmin,',');

		// 读取成员列表
		$arrWhere=array();
		$arrWhere['A.group_id']=$nId;
		$arrWhere['A.groupuser_isadmin']=0;

		$nTotalRecord=Model::F_('groupuser','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['group_option']['group_listusernum']);
		$arrGroupusers=Model::F_('groupuser','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order("A.create_dateline DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		$this->assign('sGroupleader',$sGroupleader);
		$this->assign('arrGroupleaders',$arrGroupleaders);
		$this->assign('sGroupadmin',$sGroupadmin);
		$this->assign('arrGroupadmins',$arrGroupadmins);
		$this->assign('oGroup',$oGroup);
		$this->assign('arrGroupusers',$arrGroupusers);
		$this->assign('sPageNavbar',$oPage->P());
		$this->display(Admin_Extend::template('group','group/user'));
	}

	public function user_add(){
		$nGroupid=intval(Q::G('value'));

		$oGroup=GroupModel::F('group_id=?',$nGroupid)->getOne();
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}

		// 设置完毕后系统统一进行清理
		$arrMaychangeuserid=array();
		
		// 保存小组组长
		$sLeaderUid=trim(Q::G('leader_userid','P'));
		$arrLeaderUserid=explode(',',$sLeaderUid);
		$arrLeaderUserid=Q::normalize($arrLeaderUserid,',',false);

		// 保存前清除旧的用户 && 清除前取得可能变更权限的用户id
		$arrGroupusers=GroupuserModel::F()->where(array('group_id'=>$nGroupid,'groupuser_isadmin'=>2))->getAll();
		if(is_array($arrGroupusers)){
			foreach($arrGroupusers as $oGroupuser){
				$arrMaychangeuserid[]=$oGroupuser['user_id'];
			}
		}

		$oGroupuserMeta=GroupuserModel::M();
		$oGroupuserMeta->deleteWhere(array('group_id'=>$nGroupid,'groupuser_isadmin'=>2));
		if($oGroupuserMeta->isError()){
			$this->E($oGroupuserMeta->getErrorMessage());
		}
		
		if(!empty($arrLeaderUserid)){
			foreach($arrLeaderUserid as $nLeaderUserid){
				$oUser=UserModel::F('user_id=? AND user_status=1',$nLeaderUserid)->getOne();
				if(empty($oUser['user_id'])){
					$this->E(Q::L('用户不存在或者被禁用','__APPGROUP_COMMON_LANG__@Controller'));
				}

				$arrMaychangeuserid[]=$nLeaderUserid;

				$oGroupuser=new GroupuserModel();
				$oGroupuser->user_id=$nLeaderUserid;
				$oGroupuser->group_id=$nGroupid;
				$oGroupuser->groupuser_isadmin=2;
				$oGroupuser->save('create');
				if($oGroupuser->isError()){
					$this->E($oGroupuser->getErrorMessage());
				}

				// 用户被设置为小组长后，判断是否拥有小组长角色
				$oUserrole=UserroleModel::F('user_id=? AND role_id=2',$nLeaderUserid)->getOne();
				if(empty($oUserrole['user_id'])){
					Q::instance('RoleModel')->setGroupUsers(2,array($nLeaderUserid));
				}

				// 更新小组中的用户数量
				Q::instance('GroupModel')->resetUser($nGroupid);
			}
		}

		// 保存管理员
		$sAdminUid=trim(Q::G('admin_userid','P'));
		$arrAdminUserid=explode(',',$sAdminUid);
		$arrAdminUserid=Q::normalize($arrAdminUserid,',',false);

		// 保存前清除旧的用户 && 清除前取得可能变更权限的用户id
		$arrGroupusers=GroupuserModel::F()->where(array('group_id'=>$nGroupid,'groupuser_isadmin'=>3))->getAll();
		if(is_array($arrGroupusers)){
			foreach($arrGroupusers as $oGroupuser){
				$arrMaychangeuserid[]=$oGroupuser['user_id'];
			}
		}

		$oGroupuserMeta=GroupuserModel::M();
		$oGroupuserMeta->deleteWhere(array('group_id'=>$nGroupid,'groupuser_isadmin'=>1));
		if($oGroupuserMeta->isError()){
			$this->E($oGroupuserMeta->getErrorMessage());
		}
		
		if(!empty($arrAdminUserid)){
			foreach($arrAdminUserid as $nAdminUserid){
				$oUser=UserModel::F('user_id=? AND user_status=1',$nAdminUserid)->getOne();
				if(empty($oUser['user_id'])){
					$this->E(Q::L('用户不存在或者被禁用','__APPGROUP_COMMON_LANG__@Controller'));
				}

				$arrMaychangeuserid[]=$nAdminUserid;
				
				$oGroupuser=new GroupuserModel();
				$oGroupuser->user_id=$nAdminUserid;
				$oGroupuser->group_id=$nGroupid;
				$oGroupuser->groupuser_isadmin=1;
				$oGroupuser->save('create');
				if($oGroupuser->isError()){
					$this->E($oGroupuser->getErrorMessage());
				}

				// 用户被设置为小组管理员后，判断是否拥有管理员角色
				$oUserrole=UserroleModel::F('user_id=? AND role_id=3',$nAdminUserid)->getOne();
				if(empty($oUserrole['user_id'])){
					Q::instance('RoleModel')->setGroupUsers(3,array($nAdminUserid));
				}

				// 更新小组中的用户数量
				Q::instance('GroupModel')->resetUser($nGroupid);
			}
		}

		// 保存成员
		$sUserUid=trim(Q::G('user_userid','P'));
		$arrUserUserid=explode(',',$sUserUid);
		$arrUserUserid=Q::normalize($arrUserUserid,',',false);

		if(!empty($arrUserUserid)){
			foreach($arrUserUserid as $nUserUserid){
				$oUser=UserModel::F('user_id=? AND user_status=1',$nUserUserid)->getOne();
				if(empty($oUser['user_id'])){
					$this->E(Q::L('用户不存在或者被禁用','__APPGROUP_COMMON_LANG__@Controller'));
				}

				$oTryGroupuser=GroupuserModel::F('user_id=? AND group_id=?',$nUserUserid,$nGroupid)->getOne();
				if(!empty($oTryGroupuser['user_id'])){
					continue;
				}
				
				$oGroupuser=new GroupuserModel();
				$oGroupuser->user_id=$nUserUserid;
				$oGroupuser->group_id=$nGroupid;
				$oGroupuser->groupuser_isadmin='0';
				$oGroupuser->save('create');
				if($oGroupuser->isError()){
					$this->E($oGroupuser->getErrorMessage());
				}

				// 更新小组中的用户数量
				Q::instance('GroupModel')->resetUser($nGroupid);
			}
		}

		// 设置成功最后一步进行数据清理
		if($arrMaychangeuserid){
			foreach($arrMaychangeuserid as $nMaychangeuserid){
				Group_Extend::chearGroupuserrole($nMaychangeuserid);
			}
		}

		$this->S(Q::L('用户设置成功','__APPGROUP_COMMON_LANG__@Controller'));
	}

	public function delete_groupuser(){
		$nGroupid=intval(Q::G('gid'));
		$nUserid=intval(Q::G('uid'));
		
		$oGroup=GroupModel::F('group_id=?',$nGroupid)->getOne();
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}

		$oUser=UserModel::F('user_id=?',$nUserid)->getOne();
		if(empty($oUser['user_id'])){
			$this->E(Q::L('用户不存在','__APPGROUP_COMMON_LANG__@Controller'));
		}
		
		// 执行删除
		$oGroupuserMeta=GroupuserModel::M();
		$oGroupuserMeta->deleteWhere(array('group_id'=>$nGroupid,'user_id'=>$nUserid));
		if($oGroupuserMeta->isError()){
			$this->E($oGroupuserMeta->getErrorMessage());
		}

		Group_Extend::chearGroupuserrole($nUserid);

		// 更新小组中的用户数量
		Q::instance('GroupModel')->resetUser($nGroupid);
		
		$this->S(Q::L('用户删除成功','__APPGROUP_COMMON_LANG__@Controller'));
	}

}
