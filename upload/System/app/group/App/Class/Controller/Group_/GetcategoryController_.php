<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   AJAX取得分类控制器($$)*/

!defined('Q_PATH') && exit;

class Getcategory_C_Controller extends InitController{

	public function index(){
		$nGid=intval(Q::G('gid','P'));
		if(empty($nGid)){
			echo '';
		}

		echo "<option value=\"0\">".Q::L('默认分类','Controller')."</option>";

		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$nGid)->order('grouptopiccategory_sort ASC')->getAll();
		foreach($arrGrouptopiccategorys as $arrValue){
			echo "<option value=\"{$arrValue['grouptopiccategory_id']}\">{$arrValue['grouptopiccategory_name']}</option>";
		}
	}

}