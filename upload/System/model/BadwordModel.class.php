<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   词语过滤模型($$)*/

!defined('Q_PATH') && exit;

class BadwordModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'badword',
			'check'=>array(
				'badword_find'=>array(
					array('require',Q::L('待过滤的词语不能为空','__COMMON_LANG__@Common')),
				),
			),
			'autofill'=>array(
				array('badword_findpattern','findPattern','all','callback'),
				array('badword_admin','userName','create','callback'),
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

	public function truncateBadword(){
		$oDb=$this->getDb();
		return $oDb->query("TRUNCATE ".$GLOBALS['_commonConfig_']['DB_PREFIX'].'badword');
	}

	public function addBadword($sFind,$sReplacement,$sAdmin,$nType=1){
		if(trim($sFind)){
			$sFind=trim($sFind);
			$sReplacement=trim($sReplacement);
			$sFindpattern=$this->patternFind($sFind);
			$arrBadwordData=array(
				'badword_find'=>$sFind ,
				'badword_replacement'=>$sReplacement,
				'badword_admin'=>$sAdmin,
				'badword_findpattern'=>$sFindpattern,
			);

			$oBadword=self::F('badword_find=?',$sFind)->getOne();
			if(empty($oBadword['badword_id'])){
				$oBadword=new self($arrBadwordData);
				$oBadword->setAutofill(false);
				$oBadword->save();
			}else{
				if($nType==1){
					$oBadword->changeProp($arrBadwordData);
					$oBadword->setAutofill(false);
					$oBadword->save('update');
				}elseif($nType==2){
					return true;
				}
			}
			if($oBadword->isError()){
				$this->_sErrorMessage=$oBadword->getErrorMessage();
				return false;
			}
		}

		return $oBadword->badword_id;
	}

	protected function beforeSave_(){
		$this->badword_find=C::text($this->badword_find);
		$this->badword_replacement=C::text($this->badword_replacement);
		$this->badword_findpattern=$this->patternFind($this->badword_find);
	}

	protected function findPattern(){
		return $this->patternFind(trim(Q::G('badword_find','P')));
	}

	protected function patternFind($sFind){
		$sFind=preg_quote($sFind,"/'");
		$sFind=str_replace("\\","\\\\",$sFind);
		$sFind=str_replace("'","\\'",$sFind);
		return '/'.preg_replace("/\\\{(\d+)\\\}/",".{0,\\1}",$sFind).'/is';
	}

}
