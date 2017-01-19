<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模版对象类（Learn JC!）($$)*/

!defined('Q_PATH') && exit;

class TemplateObj{

	private $_oTemplate;
	private $_oParser;
	private $_oCompiler;
	private $_sSourceStream;
	private $_sCompiledStream;
	public $_arrChildTemplateObj=array();
	private $_sTemplateFile='';
	private $_nStartLine=0;
	private $_nEndLine=0;
	private $_nStartByte=0;
	private $_nEndByte=0;
	private $_nStartByteInLine=0;
	private $_nEndByteInLine=0;
	protected $_arrGet=array();
	protected $_arrSet=array();
	const LOCAL_MIN=1;
	const LOCAL_IN=1;
	const LOCAL_OUT=2;
	const LOCAL_FRONT=3;
	const LOCAL_BEHIND=4;
	const LOCAL_MAX=1;
	protected $_sContent='';

	public function __construct($sSourceStream){
		$this->setSource($sSourceStream);
		$this->setCompiled($sSourceStream);
		$this->_arrGet['getTemplateFile']=&$this->_sTemplateFile;
		$this->_arrGet['getStartLine']=&$this->_nStartLine;
		$this->_arrGet['getEndLine']=&$this->_nEndLine;
		$this->_arrGet['getStartByte']=&$this->_nStartByte;
		$this->_arrGet['getEndByte']=&$this->_nEndByte;
		$this->_arrGet['getStartByteInLine']=&$this->_nStartByteInLine;
		$this->_arrGet['getEndByteInLine']=&$this->_nEndByteInLine;
		$this->_arrSet['setTemplateFile']=&$this->_sTemplateFile;
		$this->_arrSet['setStartLine']=&$this->_nStartLine;
		$this->_arrSet['setEndLine']=&$this->_nEndLine;
		$this->_arrSet['setStartByte']=&$this->_nStartByte;
		$this->_arrSet['setEndByte']=&$this->_nEndByte;
		$this->_arrSet['setStartByteInLine']=&$this->_nStartByteInLine;
		$this->_arrSet['setEndByteInLine']=&$this->_nEndByteInLine;
	}

	public function setTemplate(Template $oTempate){
		$oOldVal=$this->_oTemplate;
		$this->_oTemplate=$oTempate;
		return $oOldVal;
	}

	public function getTemplate(){
		return $this->_oTemplate;
	}

	public function setParser($oParser){
		$oOldVar=$this->_oParser;
		$this->_oParser=$oParser;
		return $oOldVar;
	}

	public function getParser(){
		return $this->_oParser;
	}

	public function setCompiler($Compiler){
		if(is_string($Compiler)){
			$oCompiler=Q::instance($Compiler);
		}else{
			$oCompiler=$Compiler;
		}

		$oOldVar=$this->_oCompiler;
		$this->_oCompiler=$oCompiler;

		return $oOldVar;
	}

	public function getCompiler(){
		return $this->_oCompiler;
	}

	public function getSource(){
		return $this->_sSourceStream;
	}

	protected function setSource($sSource){
		$sOldValue=$this->_sSourceStream;
		$this->_sSourceStream=$sSource;
		return $sOldValue;
	}

	public function getCompiled(){
		return $this->_sCompiledStream;
	}

	public function setCompiled($sStream){
		$sOldVar=$this->_sCompiledStream;
		$this->_sCompiledStream=$sStream;

		return $sOldVar;
	}

	public function __call($sMethod,$arrArgs){// 查询属性
		if(isset($this->_arrGet[$sMethod])){
			return $this->_arrGet[$sMethod];
		}

		// 设置属性
		if(isset($this->_arrSet[$sMethod])){
			if(!isset($arrArgs[0])){
				Q::E(Q::L('缺少设置内容','__QEEPHP__@Q'));
			}

			$sOldValue=$this->_arrSet[$sMethod];
			$this->_arrSet[$sMethod]=$arrArgs[0];

			return $sOldValue;
		}

		Q::E(Q::L('正在访问未知的方法：%s','__QEEPHP__@Q',null,$sMethod));
	}

	public function compareLocal(TemplateObj $oTemplateObj){
		// 前
		if($oTemplateObj->getEndByte()<=$this->getStartByte()){
			return self::LOCAL_FRONT;
		}

		// 后
		if($oTemplateObj->getStartByte()>=$this->getEndByte()){
			return self::LOCAL_BEHIND;
		}

		// 内
		if($oTemplateObj->getStartByte()>=$this->getStartByte()){
			return self::LOCAL_IN;
		}

		// 外
		if($oTemplateObj->getStartByte()<= $this->getStartByte()){
			return self::LOCAL_OUT;
		}

		Q::E(Q::L('不支持交叉Template对象。','__QEEPHP__@Q'));
	}

	public function addTemplateObj(TemplateObj $oTemplateObj){
		$arrNewList=array();

		foreach($this->_arrChildTemplateObj as $oMyTemplateObj){
			if(!($oMyTemplateObj instanceof TemplateObj)){
				Q::E('$oMyTemplateObj must is an instance of class TemplateObj');
			}
			
			if($oTemplateObj){
				$nLocal=$oMyTemplateObj->compareLocal($oTemplateObj);
				switch($nLocal){
					case self::LOCAL_FRONT:
						$arrNewList[]=$oTemplateObj;// 插入到当前位置
						$arrNewList[]=$oMyTemplateObj;
						$oTemplateObj=null;
						break;
					case self::LOCAL_BEHIND:
						$arrNewList[]=$oMyTemplateObj;
						break;
					case self::LOCAL_IN:
						$oMyTemplateObj->addTemplateObj($oTemplateObj);
						$arrNewList[]=$oMyTemplateObj;
						$oTemplateObj=null;
						break;
					case self::LOCAL_OUT:
						$oTemplateObj->addTemplateObj($oMyTemplateObj);
						break;
				}
			}else{
				$arrNewList[]=$oMyTemplateObj;
			}
		}

		if($oTemplateObj){// 加入到最后
			$arrNewList[]=$oTemplateObj;
		}

		$this->_arrChildTemplateObj=$arrNewList;
	}

	public function removeTemplateObj($nIdx){
		if(isset($this->_arrChildTemplateObj[$nIdx])){
			$oRet=$this->_arrChildTemplateObj[$nIdx];
			unset($this->_arrChildTemplateObj[$nIdx]);
			return $oRet;
		}
	}

	public function getTemplateObj($nIdx){
		return isset($this->_arrChildTemplateObj[$nIdx])? $this->_arrChildTemplateObj[$nIdx]: null;
	}

	public function locate($sTemplateStream, $nStart){
		$sSourceStream=$this->getSource();
		if(empty($sSourceStream)){// 空对象
			$this->setStartByte(-1);
			$this->setEndByte(-1);
			$this->setStartLine(-1);
			$this->setEndLine(-1);
			$this->setEndLine(-1);
			$this->setStartByteInLine(-1);
			$this->setEndByteInLine(-1);
			return;
		}

		$nTotalByte=strlen($sTemplateStream);

		// 起止字节位置
		$nStartByte=strpos($sTemplateStream,$sSourceStream,$nStart);
		$nEndByte=$nStartByte+strlen($sSourceStream)-1;
		$this->setStartByte($nStartByte);
		$this->setEndByte($nEndByte);

		// 起止行数
		$nStartLine=($nStartByte<=0)?0:substr_count($sTemplateStream,"\n",0,$nStartByte);
		$nEndLine =($nEndByte<=0)?0:substr_count($sTemplateStream,"\n",0,$nEndByte);
		$this->setStartLine($nStartLine);
		$this->setEndLine($nEndLine);

		// 在行上的起止位置&&起始点 所在行 的行首位置
		$nLineHeadOfStart=strrpos(substr($sTemplateStream,0,$nStartByte),"\n")+1;

		// 结束点 所在行 的行首位置
		$nLineHeadOfEnd=strrpos(substr($sTemplateStream,0,$nEndByte),"\n")+1;
		$nStartInLine=$nStartByte-$nLineHeadOfStart;
		$nEndInLine=$nEndByte-$nLineHeadOfEnd;
		$this->setStartByteInLine($nStartInLine);
		$this->setEndByteInLine($nEndInLine);
	}

	public function compile_(){
		// 编译自己
		while(($oCompiler=$this->setCompiler(null))!==null){
			$oCompiler->compile($this);
		}
	}

	public function getLocationDescription(){
		return Q::L('行: %s; 列: %s; 文件: %s','__QEEPHP__@Q',null,$this->getStartLine(),$this->getStartByteInLine(),$this->getTemplateFile());
	}

	protected function replaceCompiled($nStart,$nLen,&$sNewContent){
		$sCompiled=$this->getCompiled();
		$sCompiled=substr_replace($sCompiled,$sNewContent,$nStart,$nLen);
		$this->setCompiled($sCompiled);
	}

	public function compile(){
		$arrChildTemplateObj=$this->_arrChildTemplateObj;
		while(!empty($arrChildTemplateObj)){
			$oChildTemplateObj=array_pop($arrChildTemplateObj);
			if(!($oChildTemplateObj instanceof TemplateObj)){
				Q::E('$oChildTemplateObj must is an instance of class TemplateObj');
			}
			
			$oChildTemplateObj->compile();// 编译子对象
			$nStart=$oChildTemplateObj->getStartByte()-$this->getStartByte();// 置换对象
			$nLen=$oChildTemplateObj->getEndByte()-$oChildTemplateObj->getStartByte()+1;
			$sCompiled=$oChildTemplateObj->getCompiled();
			$this->replaceCompiled($nStart,$nLen,$sCompiled);
		}
		TemplateObj::compile_();// 编译自己
	}

	public function setContent($sContent){
		$this->_sContent=$sContent;
	}

	public function getContent(){
		return $this->_sContent;
	}

}

class TemplateNode extends TemplateObj{

	private $_sNodeName;

	public function __construct($sSource,$sNodeName){
		parent::__construct($sSource);
		$this->_sNodeName=$sNodeName;
	}

	public function getNodeName(){
		return $this->_sNodeName;
	}

	public function getAttribute(){
		foreach($this->_arrChildTemplateObj as $oChild){
			if(C::isKindOf($oChild,'TemplateNodeAttribute')){
				return $oChild;
			}
		}

		Q::E(Q::L('没有头标签','__QEEPHP__@Q'));
	}

	public function getBody(){
		while(!empty($this->_arrChildTemplateObj)){
			$oChild=array_pop($this->_arrChildTemplateObj);
			if(get_class($oChild)=='TemplateObj'){
				return $oChild;
			}
		}

		return null;
	}

}

class TemplateNodeAttribute extends TemplateObj{

	public $_arrAttributes=array();
	private $_arrAttributeOriginNames=array();

	public function setAttribute($sName,$sValue){
		$sOriginName=$sName;
		$sName=strtolower($sName);
		$this->_arrAttributeOriginNames[$sName]=$sOriginName;// 属性名原文
		$sOldValue=isset($this->_arrAttributes[$sName])?$this->_arrAttributes[$sName]:null;// 属性名&属性值对
		$this->_arrAttributes[$sName]=$sValue;

		return $sOldValue;
	}

	public function getAttribute($sName){
		return isset($this->_arrAttributes[$sName])?$this->_arrAttributes[$sName]:null;
	}

	public function getAttributeOriginName($sAttrName){
		return isset($this->_arrAttributeOriginNames[$sAttrName])?$this->_arrAttributeOriginNames[$sAttrName]:null;
	}

}

class TemplateNodeTag extends TemplateObj{

	const TYPE_HEAD=1;
	const TYPE_TAIL=2;
	private $_sName;
	private $_nType;
	private $_sAttributeSource;
	public function __construct($sSource,$sName,$nType){
		if(!in_array($nType,array(self::TYPE_HEAD,self::TYPE_TAIL))){
			Q::E(Q::L('参数 $nType 必须为 TemplateNodeTag::TYPE_HEAD 或 TemplateNodeTag::TYPE_TAIL','__QEEPHP__@Q'));
		}

		parent::__construct($sSource);

		$this->_sName=$sName;
		$this->_nType=$nType;
	}

	public function getTagType(){
		return $this->_nType;
	}

	public function getTagName(){
		return $this->_sName;
	}

	public function getTagTopName(){
		list($sTopName,)=explode(':',$this->_sName);
		return $sTopName;
	}

	public function setTagAttributeSource($sAttributeSource){
		$sOldValue=$this->_sAttributeSource;
		$this->_sAttributeSource=$sAttributeSource;
		return $sOldValue;
	}

	public function getTagAttributeSource(){
		return $this->_sAttributeSource;
	}

	public function matchTail(TemplateNodeTag $oTailTag){
		if($oTailTag->getTagType()!=self::TYPE_TAIL){
			Q::E(Q::L('参数 $oTailTag 必须是一个尾标签','__QEEPHP__@Q'));
		}

		$sTailName=$oTailTag->getTagName();
		return preg_match("/^{$sTailName}/i",$this->getTagName());
	}

}
