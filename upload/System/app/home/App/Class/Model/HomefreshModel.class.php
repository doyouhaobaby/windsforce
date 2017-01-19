<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事模型($$)*/

!defined('Q_PATH') && exit;

class HomefreshModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homefresh',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('homefresh_username','userName','create','callback'),
				array('homefresh_ip','getIp','create','callback'),
			),
			'check'=>array(
				'homefresh_title'=>array(
					array('require',Q::L('新鲜事标题不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',300,Q::L('新鲜事标题最大长度为300','__APPHOME_COMMON_LANG__@Model'))
				),
				'homefresh_message'=>array(
					array('require',Q::L('新鲜事内容不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',100000,Q::L('新鲜事内容最大长度为100000','__APPHOME_COMMON_LANG__@Model'))
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

	public function getHomefreshComments($nHomefreshId){
		if(empty($nHomefreshId)){
			return 0;
		}
		return HomecommentModel::F('homefresh_id=?',$nHomefreshId)->all()->getCounts();
	}

	protected function beforeSave_(){
		$this->homefresh_title=C::text($this->homefresh_title);
		$this->homefresh_message=Core_Extend::replaceAttachment(C::cleanJs($this->homefresh_message));
		$this->homefresh_username=C::text($this->homefresh_username);
		$this->homefresh_from=C::text($this->homefresh_from);
		//$this->homefresh_attribute=C::strip($this->homefresh_attribute);
		$this->homefresh_thumb=C::text($this->homefresh_thumb);
	}
	
	public function updateHomefreshcommentnum($nHomefreshid){
		$oHomefresh=self::F('homefresh_id=?',$nHomefreshid)->getOne();
		if(!empty($oHomefresh['homefresh_id'])){
			$nHomefreshcommentnum=HomefreshcommentModel::F('homefreshcomment_status=1 AND homefresh_id=?',$nHomefreshid)->all()->getCounts();
			$oHomefresh->homefresh_commentnum=$nHomefreshcommentnum;
			$oHomefresh->save('update');
			if($oHomefresh->isError()){
				$this->_setErrorMessage=$oHomefresh->getErrorMessage();
				return false;
			}
		}

		return true;
	}

	public function getHomefreshnumByUserid($nUserid){
		return HomefreshModel::F('user_id=? AND homefresh_status=1',$nUserid)->all()->getCounts();
	}

	public function parseMusicString($sMusic){
		$sMusic=substr(trim($sMusic),0,-4);
		$sMusic=explode('[WF]',$sMusic);//分隔

		$arrTitleList=Q::G('title_list');
		$arrAuthorList=Q::G('author_list');
		$arrArtistList=Q::G('artist_list');
		if(is_array($sMusic)){
			foreach($sMusic as $nKey=>$sData){
				$arrResult=explode('|',$sData);
				$arrResult[5]=($arrResult[5]!=Q::L('艺术家','__APPHOME_COMMON_LANG__@Model'))?isset($arrArtistList[$nKey])?$arrArtistList[$nKey]:$arrResult[5]:'';
				
				$arrData[] = array(
					'type'=>$arrResult[0],
					'img'=>$arrResult[1],
					'pid'=>$arrResult[2],
					'title'=>isset($arrTitleList[$nKey])?$arrTitleList[$nKey]:$arrResult[3],
					'url'=>$arrResult[4],
					'artist'=>$arrResult[5],
					'author'=>isset($arrAuthorList[$nKey])?$arrAuthorList[$nKey]:$arrResult[6]
				);
			}
		}
		$arrData=$this->assocUnique($arrData,'pid'); //数组去重
		return $arrData;
	}

	public function parseVideoString($sVideos){
		$sVideos=substr(trim($sVideos),0,-4);
		$arrVideos=explode('[WF]',$sVideos); //分隔

		$arrCustomename=Q::G('item');
		$arrData=array();
		if(is_array($arrVideos)){
			foreach($arrVideos as $nKey=>$sValue){
				$arrResult=explode('|',$sValue);
				$arrData[]=array('type'=>$arrResult[0],'img'=>$arrResult[1],'pid'=>$arrResult[2],'title'=>$arrCustomename[$nKey],'url'=>$arrResult[4]);
			}
			return $arrData;
		}
		return '';
		
	}

	public function parseMovieString($arrMovie){
		$arrResult=array();
		if(is_array($arrMovie)){
			foreach($arrMovie as $sDate){
				$arrResult[]=json_decode($sDate,true);
			}
		}

		return $arrResult;
	}

	private function assocUnique($arrValue,$sKey){
		$arrTemp=array();
		foreach($arrValue as $sTempKey=>$arrTempVale){
			if(in_array($arrTempVale[$sKey],$arrTemp)){
				unset($arrValue[$sTempKey]);
			}else{
				$arrTemp[]=$arrTempVale[$sKey];
			}
		}
		sort($arrValue);
		return $arrValue;
	}

}
