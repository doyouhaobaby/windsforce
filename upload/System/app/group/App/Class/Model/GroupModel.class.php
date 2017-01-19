<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组模型($$)*/

!defined('Q_PATH') && exit;

class GroupModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'group',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
			),
			'check'=>array(
				'group_name'=>array(
					array('empty'),
					array('max_length',30,Q::L('群组不能超过30个字符','__APPGROUP_COMMON_LANG__@Model')),
					array('number_underline_english',Q::L('群组名只能是由数字，下划线，字母组成','__APPGROUP_COMMON_LANG__@Model')),
					array('groupName',Q::L('群组名已经存在','__APPGROUP_COMMON_LANG__@Model'),'condition'=>'must','extend'=>'callback'),
				),
				'group_nikename'=>array(
					array('require',Q::L('群组别名不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',30,Q::L('群组别名不能超过30个字符','__APPGROUP_COMMON_LANG__@Model'))
				),
				'group_listdescription'=>array(
					array('require',Q::L('群组列表简介不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',300,Q::L('群组列表简介不能超过300个字符','__APPGROUP_COMMON_LANG__@Model'))
				),
				'group_description'=>array(
					array('require',Q::L('群组简介不能为空','__APPGROUP_COMMON_LANG__@Model')),
				),
				'group_sort'=>array(
					array('number',Q::L('序号只能是数字','__APPGROUP_COMMON_LANG__@Model'),'condition'=>'notempty','extend'=>'regex'),
				),
				'group_roleleader'=>array(
					array('require',Q::L('自定义角色组长名字不能为空','__APPGROUP_COMMON_LANG__@Model')),
				),
				'group_roleadmin'=>array(
					array('require',Q::L('自定义角色管理员名字不能为空','__APPGROUP_COMMON_LANG__@Model')),
				),
				'group_roleuser'=>array(
					array('require',Q::L('自定义角色成员名字不能为空','__APPGROUP_COMMON_LANG__@Model')),
				),
			),
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	public function groupName(){
		if(!isset($_POST['group_name'])){
			return true;
		}
		return $this->uniqueField_('group_name','group_id',isset($_POST['gid'])?'gid':'value');
	}
	
	public function afterUpdate($nCategoryId){
		if($nCategoryId<=0){
			return;
		}

		// 更新分类下的小组数量
		$nGroupNums=GroupModel::F('groupcategory_id=? AND group_status=1',$nCategoryId)->all()->getCounts();
		$oGroupCategory=GroupcategoryModel::F('groupcategory_id=?',$nCategoryId)->query();
		$oGroupCategory->groupcategory_count=$nGroupNums;
		$oGroupCategory->save('update');
	}

	public function recommend($nId,$nStatus=0){
		if(!empty($nId)){
			$oModelMeta=self::M();
			$oModelMeta->updateWhere(array('group_isrecommend'=>$nStatus),array('group_id'=>$nId));
			return true;
		}else{
			$this->_sErrorMessage=Q::L('操作项不存在','__APPGROUP_COMMON_LANG__@Model');
			return false;
		}
	}

	protected function beforeSave_(){
		$this->group_name=C::text($this->group_name);
		$this->group_nikename=C::text($this->group_nikename);
		$this->group_description=Core_Extend::replaceAttachment(C::cleanJs($this->group_description));
		$this->group_listdescription=C::text($this->group_listdescription);
		$this->group_path=C::text($this->group_path);
		$this->group_icon=C::text($this->group_icon);
		$this->group_roleleader=C::text($this->group_roleleader);
		$this->group_roleadmin=C::text($this->group_roleadmin);
		$this->group_roleuser=C::text($this->group_roleuser);
		$this->group_color=C::strip($this->group_color);
		$this->group_headerbg=C::text($this->group_headerbg);

		if($this->group_sort<0){
			$this->group_sort=0;
		}
		if($this->group_sort>999){
			$this->group_sort=999;
		}
	}

	public static function isGroupuser($nGroupid,$nUserid){
		$oTrygroupuser=GroupuserModel::F('user_id=? AND group_id=?',$nUserid,$nGroupid)->getOne();
		if(empty($oTrygroupuser['user_id'])){
			return 0;
		}else{
			return 1;
		}
	}

	public function clearToday(){
		$oDb=Db::RUN();
		return $oDb->query("UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX']."group SET group_topiccommenttodaynum='0',group_topictodaynum='0',group_totaltodaynum='0'");
	}

	public function resetUser($nGroupid=0){
		$oGroup=GroupModel::F('group_id=?',$nGroupid)->getOne();
		if(!empty($oGroup['group_id'])){
			$oGroup->group_usernum=GroupuserModel::F('group_id=?',$oGroup['group_id'])->getCounts();
			$oGroup->setAutofill(false);
			$oGroup->save('update');
			if($oGroup->isError()){
				$this->_sErrorMessage=$oGroup->getErrorMessage();
				return false;
			}
		}

		return true;
	}

}
