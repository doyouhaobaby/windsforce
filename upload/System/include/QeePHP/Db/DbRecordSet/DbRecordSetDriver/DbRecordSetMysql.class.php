<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   MySQL 数据库记录集($$)*/

!defined('Q_PATH') && exit;

class DbRecordSetMysql extends DbRecordSet{

	public function free(){
		// 获取查询结果指针
		$hResult=$this->getQueryResultHandle();

		if($hResult){
			mysql_free_result($hResult);
		}
		$this->setQueryResultHandle(null);
	}

	public function fetch(){
		$hResult=$this->getQueryResultHandle();

		if($this->_nFetchMode==Db::FETCH_MODE_ASSOC){// 以关联数组的方式返回数据库结果记录
			$arrRow=mysql_fetch_assoc($hResult);
			if($this->_bResultFieldNameLower && $arrRow){
				$arrRow=array_change_key_case($arrRow,CASE_LOWER);
			}
		}else{// 以索引数组的方式返回结果记录
			$arrRow=mysql_fetch_array($hResult);
		}

		return $arrRow;
	}

}
