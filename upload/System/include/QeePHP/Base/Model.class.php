<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模型（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class Model implements ArrayAccess{

	protected $_sErrorMessage;
	protected $_arrProp=array();
	protected $_sClassName;
	protected static $_arrMeta;
	protected $_bAutofill=true;
	protected $_arrChangeProp=array();

	public function __construct($Data=null,$sName='',$bSelect=false){
		// 设置模型名字
		if(empty($sName)){
			$sName=get_class($this);
		}
		$this->_sClassName=$sName;

		// 判断是否存在Meta对象，否则创建
		if(!isset(self::$_arrMeta[$this->_sClassName])){
			self::$_arrMeta[$this->_sClassName]=ModelMeta::instance($this->_sClassName);
		}
		$oMeta=self::$_arrMeta[$this->_sClassName];
		if($Data!==null){
			$this->changeProp($Data,null,$bSelect);
		}
	}

	public function save($sSaveMethod='save',$Data=null){
		if($Data!==null){
			$this->changeProp($Data);
		}

		// 表单自动填充
		$this->makePostData();

		$this->beforeSave_();

		// 程序通过内置方法统一实现
		switch(strtolower($sSaveMethod)){
			case 'create':
				$this->create_();
				break;
			case 'update':
				$this->update_();
				break;
			case 'replace':
				$this->replace_();
				break;
			case 'save':
				default:
				$arrPkValue=$this->id(true);
				if(!is_array($arrPkValue)){
					if(empty($arrPkValue)){// 单一主键的情况下，如果 $arrPkValue 为空，则 create，否则 update
						$this->create_();
					}else{
						$this->update_();
					}
				}else{
					$this->replace_();// 复合主键的情况下，则使用 replace 方式
				}
				break;
		}

		$this->afterSave_();
		return $this;
	}

	public function id($bChange=false){
		$arrId=array();
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['pk_'] as $sName){
			if($bChange===true){
				if(!in_array($sName,$this->_arrChangeProp)){
					$arrId[$sName]=$this->{$sName};
				}
			}else{
				$arrId[$sName]=$this->{$sName};
			}
		}

		if(count($arrId)==1){
			$arrId=reset($arrId);
		}

		if(!empty($arrId)){
			return $arrId;
		}else{
			return null;
		}
	}

	public function changeProp($Prop,$Value=null,$bSelect=false){
		if(!is_array($Prop)){
			$Prop=array($Prop=>$Value);
		}

		foreach($Prop as $sPropName=>$Value){// 将数组赋值给对象属性
			if(!in_array($sPropName,self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'])){
				continue;
			}
			$this->_arrProp[$sPropName]=$Value;
			if($bSelect===false){
				$this->_arrChangeProp[]=$sPropName;
			}
		}
		return $this;
	}

	public function get($sPropName){
		return $this->__get($sPropName);
	}

	public function &__get($sPropName){
		$this->checkProp_($sPropName);
		return $this->_arrProp[$sPropName];
	}

	public function set($sPropName,$Value){
		$this->__set($sPropName,$Value);
	}

	public function __set($sPropName,$Value){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=$Value;
		$this->_arrChangeProp[]=$sPropName;
	}

	public function setAutofill($bAutofill=true){
		$this->_bAutofill=$bAutofill;
	}

	public function __isset($sPropName){
		return array_key_exists($sPropName,$this->_arrProp);
	}

	public function offsetExists($sPropName){
		return array_key_exists($sPropName,$this->_arrProp);
	}

	public function offsetSet($sPropName,$Value){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=$Value;
	}

	public function offsetGet($sPropName){
		$this->checkProp_($sPropName);
		return $this->_arrProp[$sPropName];
	}

	public function offsetUnset($sPropName){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=null;
	}

	public function getClassName(){
		return $this->_sClassName;
	}

	public function getMeta(){
		return self::$_arrMeta[$this->_sClassName];
	}

	public function getTableEnter(){
		return self::$_arrMeta[$this->_sClassName]->_oTable;
	}

	public function getDb(){
		return self::$_arrMeta[$this->_sClassName]->_oTable->getDb();
	}

	public function hasProp($sPropName){
		return isset(self::$_arrMeta[$this->_sClassName]->_arrProp[$sPropName]);
	}

	public function destroy(){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$sPk=reset($oMeta->_arrTableMeta['pk_']);
		$value=$this->{$sPk};

		if(empty($value)){
			Q::E('Primary key does not exist');
		}

		// 确定删除当前对象的条件
		if(count($oMeta->_arrTableMeta['pk_'])>1){
			$where=$value;
		}else{
			$where=array($sPk=>$value);
		}

		// 从数据库中删除当前对象
		$bResult=$oMeta->_oTable->delete($where);
		if($bResult===false){
			$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
			return false;
		}
	}

	public function toArray(){
		$arrData=array();
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'] as $sPropName){
			$arrData[$sPropName]=$this->{$sPropName};
		}
		return $arrData;
	}

	static function F_($sClass){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return ModelMeta::instance($sClass,true)->findByArgs($arrArgs);
	}

	static function M_($sClass){
		return ModelMeta::instance($sClass,true);
	}

	public function isError(){
		return !empty($this->_sErrorMessage);
	}

	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}

	protected function method_($sMethod){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->__call($sMethod,$arrArgs);
	}

	protected function makePostData(){
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'] as $sField){
			if(!in_array($sField,$this->_arrChangeProp) && isset($_POST[$sField])){
				$this->_arrProp[$sField]=trim($_POST[$sField]);
				$this->_arrChangeProp[]=$sField;
			}
		}
	}

	protected function create_(){
		$oMeta=self::$_arrMeta[$this->_sClassName];

		// 自动填充
		if($this->_bAutofill===true){
			$this->autofill_('create');
		}
		foreach($oMeta->_arrTableMeta['default'] as $sPropName=>$defaultVal){
			if(!isset($this->_arrProp[$sPropName]) || $this->_arrProp[$sPropName]===null){
				$this->_arrProp[$sPropName]=$defaultVal;
			}
		}

		$this->beforeCreate_();
		if($this->check_('create',true)===false){// 进行create验证
			return false;
		}

		// 准备要保存到数据库的数据
		$arrSaveData=array();
		foreach($this->_arrProp as $sPropName=>$sValue){
			// 过滤NULL值
			if($sValue!==null){
				$arrSaveData[$sPropName]=$sValue;
			}
		}

		// 将名值对保存到数据库
		$arrPkValue=$oMeta->_oTable->insert($arrSaveData);
		if($arrPkValue===false){
			$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
			return false;
		}

		// 将获得的主键值指定给对象
		foreach($arrPkValue as $sFieldName=>$sFieldValue){
			$this->_arrProp[$sFieldName]=$sFieldValue;
		}

		$this->afterCreate_();
	}

	protected function update_(){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		if($this->_bAutofill===true){// 这里允许update和all
			$this->autofill_('update');
		}

		$this->beforeUpdate_();
		if($this->check_('update',true)===false){// 进行update验证
			return false;
		}

		$arrSaveData=array();
		foreach($this->_arrProp as $sPropName=>$value){
			if(in_array($sPropName,$this->_arrChangeProp)){
				$arrSaveData[$sPropName]=$value;
			}
		}

		if(!empty($arrSaveData)){
			$arrConditions=array();
			foreach($oMeta->_arrTableMeta['pk_'] as $sFieldName){
				if(isset($arrSaveData[$sFieldName])){
					unset($arrSaveData[$sFieldName]);
				}
				if(!empty($this->_arrProp[$sFieldName])){
					$arrConditions[$sFieldName]=$this->_arrProp[$sFieldName];
				}
			}

			if(!empty($arrSaveData) && !empty($arrConditions)){
				$bResult=$oMeta->_oTable->update($arrSaveData,$arrConditions);
				if($bResult===false){
					$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
					return false;
				}
			}
		}

		$this->afterUpdate_();
	}

	protected function replace_(){
		try{
			$bResult=$this->create_();// 数据库本身并不支持 replace 操作，所以只能是通过insert操作来模拟
		}catch(Exception $e){
			$this->update_();
		}
	}

	protected function autofill_($sMode='create'){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$arrFieldToProp=$arrField=self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'];// 我们要求数据库字段都以小写为准

		// 兼容大小写，字段必须全部为小写&任何时候使用当前时间戳进行填充
		if(in_array('dateline',$arrField)){
			$this->changeProp('dateline',CURRENT_TIMESTAMP);
		}
		if($sMode=='create' and in_array('create_dateline',$arrField)){// 创建对象的时候
			$this->changeProp('create_dateline',CURRENT_TIMESTAMP);
		}
		if($sMode=='update' and in_array('update_dateline',$arrField)){// 更新对象的时候
			$this->changeProp('update_dateline',CURRENT_TIMESTAMP);
		}

		$arrFillProps=$oMeta->_arrAutofill;
		$arrData=$this->_arrProp;
		foreach($arrFillProps as $arrValue){
			$sField=array_key_exists(0,$arrValue)?$arrValue[0]:''; // 字段
			$sContent=array_key_exists(1,$arrValue)?$arrValue[1]:''; // 内容
			$sCondition=array_key_exists(2,$arrValue)?$arrValue[2]:''; // 填充条件
			$sExtend=array_key_exists(3,$arrValue)?$arrValue[3]:''; // 附加规则

			if($sContent=='datetime'){
				$sContent=date('Y-m-d H:i:s',CURRENT_TIMESTAMP);
			}elseif($sContent=='timestamp'){
				$sContent=CURRENT_TIMESTAMP;
			}elseif($sContent=='date_'){
				$sContent=date('Y-m-d',CURRENT_TIMESTAMP);
			}elseif($sContent=='time_'){
				$sContent= date('H:i:s',CURRENT_TIMESTAMP);
			}

			// 自动填充类型处理,处理类型为空，那么为all
			if($sCondition=='' || $sMode==$sCondition){
				if($sExtend){// 调用附加规则
					switch($sExtend){
						case 'function':// 使用函数进行填充 字段的值作为参数
						case 'callback': // 使用回调方法
							$arrArgs=isset($arrValue[4])?$arrValue[4]:array();// 回调参数
							if(isset($arrData[$sField])){
								array_unshift($arrArgs,$arrData[$sField]);
							}

							if('function'==$sExtend){// funtion回调
								if(function_exists($sContent)){
									$arrData[$sField]=call_user_func_array($sContent,$arrArgs);
								}else{
									Q::E('Function is not exist');
								}
							}else{
								if(is_array($sContent)){
									if(!is_callable($sContent,false)){
										Q::E('Callback is not exist');
									}
									$arrData[$sField]=call_user_func_array($sContent,$arrArgs);
								}else{
									$arrData[$sField]=call_user_func_array(array(&$this,$sContent),$arrArgs);
								}
							}
							break;
						case "field":
							$arrData[$sField]=$arrData[$sContent];
							break;
						case "string":
							$arrData[$sField]=strval($sContent);
							break;
					}
				}else{
					$arrData[$sField]=$sContent;
				}
			}
		}
		$this->_arrProp=$arrData;
		return $this->_arrProp;
	}

	protected function checkProp_($sPropName){
		if(!in_array($sPropName,self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'])){
			Q::E(Q::L('属性：%s不存在。','__QEEPHP__@Q',null,$sPropName));
		}
	}

	protected function check_($sMode){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$arrCheckProps=$oMeta->_arrTableMeta['field'];
		foreach($arrCheckProps as $key=>$sValue){
			if(in_array($sValue,$oMeta->_arrTableMeta['pk_'])){
				unset($arrCheckProps[$key]);
			}
		}
		$arrCheckProps=array_flip($arrCheckProps);

		$arrError=$oMeta->check($this->_arrProp,$arrCheckProps,$sMode);
		if(!empty($arrError)){
			$sErrorMessage='<span class="QModelList">';
			foreach($arrError as $sField=>$arrValue){
				foreach($arrValue as $sK=>$sV){
					$sErrorMessage.=$sV.'<br/>';
				}
			}
			$sErrorMessage.='</span>';
			$this->_sErrorMessage=$sErrorMessage;
			return false;
		}
	}

	protected function beforeCreate_(){}

	protected function afterCreate_(){}

	protected function beforeUpdate_(){}

	protected function afterUpdate_(){}

	protected function beforeSave_(){}

	protected function afterSave_(){}

}
