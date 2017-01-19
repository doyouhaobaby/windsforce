<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   过滤词语缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheBadword{

	public static function cache(){
		$arrSaveData=array();

		$arrBadwordDatas=Model::F_('badword')
			->setColumns('badword_findpattern,badword_replacement,badword_id')
			->order('badword_id ASC')
			->getAll();
		if(is_array($arrBadwordDatas)){
			foreach($arrBadwordDatas as $nKey=>$oBadwordData){
				$arrSaveData[$oBadwordData['badword_id']]['regex']=$oBadwordData['badword_findpattern'];
				$arrSaveData[$oBadwordData['badword_id']]['value']=$oBadwordData['badword_replacement'];
			}
		}

		Core_Extend::saveSyscache('badword',$arrSaveData);
	}

}
