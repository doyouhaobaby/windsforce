<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模板分析器（Learn JC!）($$)*/

!defined('Q_PATH') && exit;

/* 导入编译器和模板对象 */
require_once(Q_PATH.'/Template/TemplateObj_.php');
require_once(Q_PATH.'/Template/TemplateCompilers_.php');

abstract class TemplateObjParserBase{

	static public $_arrCompilers;
	public $_sLeftTag;
	public $_sRightTag;

	static public function escapeCharacter($sTxt){
		$sTxt=str_replace('$','\$',$sTxt);
		$sTxt=str_replace('/','\/',$sTxt);
		$sTxt=str_replace('?','\\?',$sTxt);
		$sTxt=str_replace('*','\\*',$sTxt);
		$sTxt=str_replace('.','\\.',$sTxt);
		$sTxt=str_replace('!','\\!',$sTxt);
		$sTxt=str_replace('-','\\-',$sTxt);
		$sTxt=str_replace('+','\\+',$sTxt);
		$sTxt=str_replace('(','\\(',$sTxt);
		$sTxt=str_replace(')','\\)',$sTxt);
		$sTxt=str_replace('[','\\[',$sTxt);
		$sTxt=str_replace(']','\\]',$sTxt);
		$sTxt=str_replace(',','\\,',$sTxt);
		$sTxt=str_replace('{','\\{',$sTxt);
		$sTxt=str_replace('}','\\}',$sTxt);
		$sTxt=str_replace('|','\\|',$sTxt);

		return $sTxt;
	}

	static public function regCompilers($sTag,$sClass){}

}

class TemplateCodeParser extends TemplateObjParserBase{

	public function __construct(){
		if($GLOBALS['_commonConfig_']['TEMPLATE_TAG_NOTE']===true){
			$this->_sLeftTag='<!--{';
			$this->_sRightTag='}-->';
		}else{
			$this->_sLeftTag='{';
			$this->_sRightTag='}';
		}
	}

	static public function regToParser(){
		Template::$_arrParses['Template.Code']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){
		self::$_arrCompilers['_code_'][$sTag]=$sClass;
	}

	protected function getRegexpParsingTemplateObj(array $arrCompilerNames){
		$sNames=implode('|',$arrCompilerNames);
		return "/".$this->_sLeftTag."({$sNames})(|.+?)".$this->_sRightTag."/s";
	}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		foreach(self::$_arrCompilers['_code_'] as $sObjType=>$sCompilers){
			$arrNames[]=TemplateObjParserBase::escapeCharacter($sObjType);// 处理一些正则表达式中有特殊意义的符号
		}

		if(!count($arrNames)){// 没有任何编译器
			return;
		}

		$sRegexp=$this->getRegexpParsingTemplateObj($arrNames);
		if($sRegexp===null){
			return;
		}

		$arrRes=array();// 分析
		if(preg_match_all($sRegexp,$sCompiled,$arrRes)){
			$nStartFindPos=0;
			foreach($arrRes[0] as $nIdx=>&$sObjectOriginTxt){
				$sObjType=trim($arrRes[1][$nIdx]);
				$sContent=trim($arrRes[2][$nIdx]);
				$oCompiler=Q::instance(self::$_arrCompilers['_code_'][$sObjType]);
				$oTemplateObj=new TemplateObj($sObjectOriginTxt);// 创建对象
				$oTemplateObj->locate($sCompiled,$nStartFindPos);// 定位
				$oTemplateObj->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTemplateObj->getEndByte()+1;
				$oTemplateObj->setTemplate($oTemplate);// 进一步装配Template对象&所属模版
				$oTemplateObj->setParser($this);// 分析器
				$oTemplateObj->setCompiler($oCompiler);// 编译器
				$oTemplateObj->setContent($sContent);// 内容
				$oTemplate->putInTemplateObj($oTemplateObj);// 将Template对象加入到对象树中
			}
		}
	}

}

class TemplateGlobalParser extends TemplateObjParserBase{

	public function __construct(){
		if($GLOBALS['_commonConfig_']['TEMPLATE_TAG_NOTE']===true){
			$this->_sLeftTag='(?:<\!--<|<\!--\{)';
			$this->_sRightTag='(?:\}-->|>-->)';
		}else{
			$this->_sLeftTag='[<\{]';
			$this->_sRightTag='[\}>]';
		}
	}

	static public function regToParser(){
		Template::$_arrParses['Template.Global']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){}

	static public function encode($sContent){
		$nRand=rand(0,99999999);
		return "__##TemplateGlobalParser##START##{$nRand}:".base64_encode($sContent).'##END##TemplateGlobalParser##__';
	}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		$oCompiler=TemplateGlobalCompiler::getGlobalInstance();
		$sLeftTag=$this->_sLeftTag;
		$sRightTag=$this->_sRightTag;

		$arrRes=array();// 分析
		if(preg_match_all("/{$sLeftTag}tagself{$sRightTag}(.+?){$sLeftTag}\/tagself{$sRightTag}/isx",$sCompiled,$arrRes)){
			$nStartFindPos=0;
			foreach($arrRes[1] as $nIndex=>$sEncode){
				$sSource=trim($arrRes[0][$nIndex]);
				$sContent=trim($arrRes[1][$nIndex]);
				$oTemplateObj=new TemplateObj($sSource);// 创建对象
				$oTemplateObj->setCompiled($sContent);
				$oTemplateObj->locate($sCompiled,$nStartFindPos);// 定位
				$oTemplateObj->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTemplateObj->getEndByte()+1;
				$oTemplateObj->setTemplate($oTemplate);// 进一步装配Template对象&所属模版
				$oTemplateObj->setParser($this);// 分析器
				$oTemplateObj->setCompiler($oCompiler);// 编译器
				$oTemplateObj->setContent($sSource);// 内容
				$oTemplate->putInTemplateObj($oTemplateObj);// 将Template对象加入到对象树中
			}
		}
	}

}

class TemplateGlobalRevertParser extends TemplateObjParserBase{

	static public function regToParser(){
		Template::$_arrParses['Template.GlobalRevert']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		$oCompiler=TemplateGlobalRevertCompiler::getGlobalInstance();
		$arrRes=array();// 分析
		if(preg_match_all('/__##TemplateGlobalParser##START##\d+:(.+?)##END##TemplateGlobalParser##__/',$sCompiled,$arrRes)){
			$nStartFindPos=0;
			foreach($arrRes[1] as $nIndex=>$sEncode){
				$sSource=$arrRes[0][$nIndex];
				$sContent=$arrRes[1][$nIndex];
				$oTemplateObj=new TemplateObj($sSource);// 创建对象
				$oTemplateObj->locate($sCompiled,$nStartFindPos);// 定位
				$oTemplateObj->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTemplateObj->getEndByte()+1;
				$oTemplateObj->setTemplate($oTemplate);// 进一步装配Template对象&所属模版
				$oTemplateObj->setParser($this);// 分析器
				$oTemplateObj->setCompiler($oCompiler);// 编译器
				$oTemplateObj->setContent($sContent);
				$oTemplate->putInTemplateObj($oTemplateObj);// 将Template对象加入到对象树中
			}
		}
	}

}

class TemplateNodeParser extends TemplateObjParserBase{

	private $_oNodeTagStack;

	public function __construct(){
		if($GLOBALS['_commonConfig_']['TEMPLATE_TAG_NOTE']===true){
			$this->_sLeftTag='<\!--<';
			$this->_sRightTag='>-->';
		}else{
			$this->_sLeftTag='<';
			$this->_sRightTag='>';
		}
	}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		$this->findNodeTag($oTemplate,$sCompiled,$sTemplatePath);// 查找分析Node的标签
		$this->packagingNode($oTemplate,$sCompiled);// 用标签组装Node
	}

	protected function findNodeTag(Template $oTemplate,&$sTemplateStream,$sTemplatePath){
		$this->_oNodeTagStack=new Stack('TemplateNodeTag');// 设置一个栈
		foreach(self::$_arrCompilers['_node_'] as $sObjType=>$sCompilers){// 所有一级节点名称
			$arrNames[]=TemplateObjParserBase::escapeCharacter($sObjType);// 处理一些正则表达式中有特殊意义的符号
		}

		if(!count($arrNames)){// 没有 任何编译器
			return;
		}

		$sLeftTag=$this->_sLeftTag;
		$sRightTag=$this->_sRightTag;

		$sNames=implode('|',$arrNames);
		$sRegexp="/{$sLeftTag}(\/?)(({$sNames})(:[^\s\>\}]+)?)(\s[^>\}]*?)?\/?{$sRightTag}/isx";
		
		$nNodeNameIdx=2;// 标签名称位置
		$nNodeTopNameIdx=3;// 标签顶级名称位置
		$nTailSlasheIdx=1;// 尾标签斜线位置
		$nTagAttributeIdx=5;// 标签属性位置
		if(preg_match_all($sRegexp,$sTemplateStream,$arrRes)){// 依次创建标签对象
			$nStartFindPos=0;
			foreach($arrRes[0] as $nIdx=>&$sTagSource){
				$sNodeName=$arrRes[$nNodeNameIdx][$nIdx];
				$sNodeTopName=$arrRes[$nNodeTopNameIdx][$nIdx];
				$nNodeType=($arrRes[$nTailSlasheIdx][$nIdx]==='/')?(TemplateNodeTag::TYPE_TAIL):(TemplateNodeTag::TYPE_HEAD);
				$sNodeName=strtolower($sNodeName);// 将节点名称统一为小写
				$sNodeTopName=strtolower($sNodeTopName);
				$oTag=new TemplateNodeTag($sTagSource,$sNodeName,$nNodeType);// 创建对象
				if($oTag->getTagType()==TemplateNodeTag::TYPE_HEAD){// 头标签，创建一个
					$oTag->setTagAttributeSource($arrRes[$nTagAttributeIdx][$nIdx]);
				}
				$oTag->locate($sTemplateStream,$nStartFindPos);// 定位
				$oTag->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTag->getEndByte()+1;
				$oTag->setTemplate($oTemplate);// 进一步装配Template对象&所属模版
				$oTag->setParser($this);// 分析器
				$oTag->setCompiler(null);// 将属性分析器设为第一个编译器
				$this->_oNodeTagStack->in($oTag);// 加入到标签栈
			}
		}
	}

	protected function packagingNode(Template $oTemplate,&$sTemplateStream){
		$oTailStack=new Stack('TemplateNodeTag');// 尾标签栈
		while(($oTag=$this->_oNodeTagStack->out())!==null){// 载入节点属性分析器&依次处理所有标签
			if($oTag->getTagType()==TemplateNodeTag::TYPE_TAIL){// 尾标签，加入到$oTailStack中
				$oTailStack->in($oTag);
				continue;
			}

			$oCompiler=$this->queryCompilerByNodeName($oTag->getTagName());// 查询到对该节点负责的编译器
			$oTailTag=$oTailStack->out();// 从尾标签栈取出一项
			if(!$oTailTag or !$oTag->matchTail($oTailTag)){// 单标签节点
				$callback=array(get_class($oCompiler),'queryCanbeSingleTag');
				if(!call_user_func_array($callback,array($oTag->getTagName()))){
					Q::E(Q::L('%s 类型节点 必须成对使用，没有找到对应的尾标签; %s','__QEEPHP__@Q',null,$oTag->getTagName(),$oTag->getLocationDescription()));
				}

				if($oTailTag){// 退回栈中
					$oTailStack->in($oTailTag);
				}

				$oNode=new TemplateNode($oTag->getSource(),$oTag->getTagName());// 创建节点
				$oNode->setStartByte($oTag->getStartByte());// 装配节点
				$oNode->setEndByte($oTag->getEndByte());
				$oNode->setStartLine($oTag->getStartLine());
				$oNode->setEndLine($oTag->getEndLine());
				$oNode->setStartByteInLine($oTag->getStartByteInLine());
				$oNode->setEndByteInLine($oTag->getEndByteInLine());
				$oNode->setTemplateFile($oTag->getTemplateFile());
			}else{// 成对标签
				$nStart=$oTag->getStartByte();// 根据头标签开始和尾标签结束，取得整个节点内容
				$nLen=$oTailTag->getEndByte()-$nStart+1;
				$sNodeSource=substr($sTemplateStream,$nStart,$nLen);
				$oNode=new TemplateNode($sNodeSource,$oTag->getTagName());// 创建节点
				$oNode->setStartByte($oTag->getStartByte());// 装配节点
				$oNode->setEndByte($oTailTag->getEndByte());
				$oNode->setStartLine($oTag->getStartLine());
				$oNode->setEndLine($oTailTag->getEndLine());
				$oNode->setStartByteInLine($oTag->getStartByteInLine());
				$oNode->setEndByteInLine($oTailTag->getEndByteInLine());
				$oNode->setTemplateFile($oTag->getTemplateFile());
				$nStart=$oTag->getEndByte()+1;// 创建Body对象，并加入到对象树中
				$nLen=$oTailTag->getStartByte()-$nStart;
				if($nLen>0){
					$oBody=new TemplateObj(substr($sTemplateStream,$nStart,$nLen));// 创建
					$oBody->locate($sTemplateStream,$nStart);// 定位
					$oBody->setTemplate($oTemplate);// 装配&所属模版
					$oBody->setParser($this);// 分析器
					$oBody->setCompiler(null);// 编译器
					$oNode->addTemplateObj($oBody);// 加入&入树
				}
			}

			if(!($oNode instanceof TemplateNode)){
				Q::E('$oNode must is an instance of class TemplateNode');
			}
			
			$oAttribute=new TemplateNodeAttribute($oTag->getTagAttributeSource());// 为节点属性创建Template对象
			$oAttribute->locate($sTemplateStream,0);// 定位
			$oAttribute->setTemplate($oTemplate);// 所属模版
			$oAttribute->setParser($this);// 分析器
			$oAttribute->setCompiler('TemplateNodeAttributeParser');// 编译器
			$oNode->addTemplateObj($oAttribute);
			$oNode->setTemplate($oTemplate);// 装配节点&所属模版
			$oNode->setParser($this);// 分析器
			$oNode->setCompiler($oCompiler);// 编译器
			$oTemplate->putInTemplateObj($oNode);// 加入到节点树
		}
	}

	public function queryCompilerByNodeName($sNodeName){
		$arrNames=array($sNodeName);
		$oReturnCompiler=null;
		$arrCompilers=self::$_arrCompilers['_node_'];
		while($sName=array_shift($arrNames) and $arrCompilers){// 迭代查询
			if(!isset($arrCompilers[$sName])){
				Q::E('Can not find node:'.$sName);
			}
			
			$oCompiler=Q::instance($arrCompilers[$sName]);;// 查询到编译器
			if(!$oCompiler){
				break;
			}
			$arrCompilers=self::$_arrCompilers['_node_'];
			$oReturnCompiler=$oCompiler;
		}

		return $oReturnCompiler;
	}

	static public function regToParser(){
		Template::$_arrParses['Template.Node']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){
		self::$_arrCompilers['_node_'][$sTag]=$sClass;
	}

}

class TemplatePhpParser extends TemplateGlobalParser{

	static public function regToParser(){
		Template::$_arrParses['Template.Php']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		$oCompiler=TemplatePhpCompiler::getGlobalInstance();
		$arrRes=array();// 分析
		if(preg_match_all("/<\?(=|php|)(.+?)\?>/is",$sCompiled,$arrRes)){
			$nStartFindPos=0;
			foreach($arrRes[1] as $nIndex=>$sEncode){
				$sSource=trim($arrRes[0][$nIndex]);
				$sContent=trim($arrRes[1][$nIndex]);
				$oTemplateObj=new TemplateObj($sSource);// 创建对象
				$oTemplateObj->locate($sCompiled,$nStartFindPos);// 定位
				$oTemplateObj->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTemplateObj->getEndByte()+1;
				$oTemplateObj->setTemplate($oTemplate);// 进一步装配 Template对象&所属模版
				$oTemplateObj->setParser($this);// 分析器
				$oTemplateObj->setCompiler($oCompiler);// 编译器
				$oTemplate->putInTemplateObj($oTemplateObj);// 将Template对象加入到 对象树中
			}
		}
	}

}

class TemplateRevertParser extends TemplateObjParserBase{

	static public function regToParser(){
		Template::$_arrParses['Template.Revert']=__CLASS__;
		return __CLASS__;
	}

	static public function regCompilers($sTag,$sClass){}

	static public function encode($sContent){
		$nRand=rand(0,99999999);
		return "__##TemplateRevertParser##START##{$nRand}:".base64_encode($sContent).'##END##TemplateRevertParser##__';
	}

	public function parse(Template $oTemplate,$sTemplatePath,&$sCompiled){
		$oCompiler=TemplateRevertCompiler::getGlobalInstance();
		$arrRes=array();// 分析
		if(preg_match_all('/__##TemplateRevertParser##START##\d+:(.+?)##END##TemplateRevertParser##__/',$sCompiled,$arrRes)){
			$nStartFindPos=0;
			foreach($arrRes[1] as $nIndex=>$sEncode){
				$sSource=$arrRes[0][$nIndex];
				$oTemplateObj=new TemplateObj($sSource);// 创建对象
				$oTemplateObj->setCompiled($sEncode);
				$oTemplateObj->locate($sCompiled,$nStartFindPos);// 定位
				$oTemplateObj->setTemplateFile($sTemplatePath);
				$nStartFindPos=$oTemplateObj->getEndByte()+1;
				$oTemplateObj->setTemplate($oTemplate);// 进一步装配 Template对象&所属模版
				$oTemplateObj->setParser($this);// 分析器
				$oTemplateObj->setCompiler($oCompiler);// 编译器
				$oTemplate->putInTemplateObj($oTemplateObj);// 将Template对象加入到 对象树中
			}
		}
	}

}
