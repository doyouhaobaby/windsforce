<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除标签($$)*/

!defined('Q_PATH') && exit;

class Delete_C_Controller extends InitController{

	public function index(){
		$nUserId=$GLOBALS['___login___']['user_id'];
		$nHometagId=intval(Q::G('id'));

		Model::M_('hometagindex')->deleteWhere(array('hometag_id'=>$nHometagId,'user_id'=>$nUserId));

		// 更新标签数量
		$oHometag=HometagModel::F('hometag_id=?',$nHometagId)->getOne();
		if(!empty($oHometag['hometag_id'])){
			$nTagIdCount=Model::F_('hometagindex','hometag_id=?',$nHometagId)->all()->getCounts();
			$oHometag->hometag_count=$nTagIdCount;
			$oHometag->save('update');
			if($oHometag->isError()){
				$this->E($oHometag->getErrorMessage());
			}
		}

		$this->S(Q::L('删除用户标签成功','Controller'));
	}

}
