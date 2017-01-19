<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   编译器列表（Learn JC!）($$)*/

!defined('Q_PATH') && exit;

/**
 * 节点编译器列表
 */
abstract class TemplateNodeCompilerBase{

	protected $_arrNotNullAttributes=array();
	protected $_arrComparison=array(
		' nheq '=>' !== ',
		' heq '=>' === ',
		' neq '=>' != ',
		' eq '=>' == ',
		' egt '=>' >= ',
		' gt '=>' > ',
		' elt '=>' <= ',
		' lt '=>' < '
	);

	public function __construct(){}

	static public function attributeTextToBool($sTxtValue){
		return !in_array(strtolower($sTxtValue),array('false','0','off','no'));
	}

	public function checkNode(TemplateNode $oNode){
		$oAttribute=$oNode->getAttribute();

		if(!($oAttribute instanceof TemplateNodeAttribute)){
			Q::E('$oAttribute must is an instance of class TemplateNodeAttribute');
		}

		foreach($this->_arrNotNullAttributes as $sAttributeName){
			$sAttributeName=strtolower($sAttributeName);
			if($oAttribute->getAttribute($sAttributeName)===null){
				Q::E(Q::L('Template节点“%s”缺少必须的属性：“%s”','__QEEPHP__@Q',null,$oNode->getNodeName(),$sAttributeName));
			}
		}

		return true;
	}

	public function regCompilers($sTag,$sClass){
		$sParentNode=str_replace('templatenodecompiler_','',strtolower(get_class($this)));
		TemplateNodeParser::regCompilers($sParentNode.':'.$sTag,$sClass);
	}

	public function parseCondition($sCondition){
		$sCondition=str_ireplace(array_keys($this->_arrComparison),array_values($this->_arrComparison),$sCondition);// 替换掉系统识别比较字符
		$sCondition=preg_replace('/\$(\w+):(\w+)\s/is','$\\1->\\2 ',$sCondition);// 解析: to ->
		switch(strtolower($GLOBALS['_commonConfig_']['TMPL_VAR_IDENTIFY'])){// 解析对
			case 'array':// 识别为数组
				$sCondition=preg_replace('/\$(\w+)\.(\w+)\s/is','$\\1["\\2"] ',$sCondition);
				break;
			case 'obj':// 识别为对象
				$sCondition=preg_replace('/\$(\w+)\.(\w+)\s/is','$\\1->\\2 ',$sCondition);
				break;
			default:// 自动判断数组或对象 只支持二维
				$sCondition=preg_replace('/\$(\w+)\.(\w+)\s/is','(is_array($\\1)?$\\1["\\2"]:$\\1->\\2)',$sCondition);
		}

		return $sCondition;
	}

	public function parseVar($sContent){
		if('q.'==strtolower(substr($sContent,0,2))){
			return $this->parseQ($sContent);// 特殊变量
		}elseif(strpos($sContent,'.')){
			$arrVars=explode('.', $sContent);
			switch(strtolower($GLOBALS['_commonConfig_']['TMPL_VAR_IDENTIFY'])){
				case 'array': // 识别为数组
					$sContent='$'.$arrVars[0].'[\''.$arrVars[1].'\']'.($this->arrayHandler($arrVars));
					break;
				case 'obj': // 识别为对象
					$sContent='$'.$arrVars[0].'->'.$arrVars[1].($this->arrayHandler($arrVars,2));
					break;
				default:// 自动判断数组或对象 支持多维
					$sContent='is_array($'.$arrVars[0].')?$'.$arrVars[0].'[\''.$arrVars[1].'\']'.($this->arrayHandler($arrVars)).':$'.$arrVars[0].'->' .$arrVars[1].($this->arrayHandler($arrVars,2));
					break;
			}
		}elseif(strpos($sContent,':')){
			$sContent='$'.str_replace(':','->',$sContent);// 额外的对象方式支持
		}elseif(!defined($sContent)){
			$sContent='$'.$sContent;
		}

		return $sContent;
	}

	public function parseQ($sVar){
		$arrVars=explode('.',$sVar);// 解析‘.’

		$arrVars[1]=strtoupper(trim($arrVars[1]));
		$nLen=count($arrVars);
		$sAction="\$_@";
		if($nLen >= 3){// cookie,session,get等等
			if(in_array(strtoupper($arrVars[1]), array('COOKIE', 'SESSION', 'GET', 'POST', 'SERVER','ENV','REQUEST'))){// PHP常用系统变量 < 忽略大小写 >
				$sCode=str_replace('@',$arrVars[1],$sAction).$this->arrayHandler($arrVars);// 替换调名称, 并将使用arrayHandler函数获取下标, 支持多维 ，以$demo[][][]方式
			}elseif(strtoupper($arrVars[1])=='LANG'){
				$sCode='Q::L(\''.strtoupper($arrVars[2]).(isset($arrVars[3])?'.'.$arrVars[3]:'').'\')';
			}elseif(strtoupper($arrVars[1])=='CONFIG'){
				$sCode='Q::C(\''.strtoupper($arrVars[2]).(isset($arrVars[3])?'.'.$arrVars[3]:'').'\')';
			}elseif( strtoupper($arrVars[1])=='CONST'){
				$sCode=strtoupper($arrVars[2]);
			}else{
				$sCode='';
			}
		}elseif($nLen===2){
			if(strtoupper($arrVars[1])=='NOW' or strtoupper($arrVars[1])=='TIME'){// 时间
				$sCode="date('Y-m-d H:i:s',time())";
			}elseif(strtoupper($arrVars[1])=='VERSION' || strtoupper($arrVars[1])=='QEEPHP_VERSION'){
				$sCode='QEEPHP_VERSION';
			}elseif(strtoupper($arrVars[1])=='LEFTTAG' || strtoupper($arrVars[1])=='LEFT'){
				$sCode='"{"';
			}elseif(strtoupper($arrVars[1])=='RIGHTTAG' || strtoupper($arrVars[1])=='RIGHT'){
				$sCode='"}"';
			}elseif(strtoupper($arrVars[1])=='TEMPLATE' || strtoupper($arrVars[1])=='BASENAME'){
				$sCode='__TMPL_FILE_NAME__';
			}else{
				$sCode=$arrVars[1];
			}
		}

		return $sCode;
	}

	public function parseVarFunction($sName,$arrVar){
		$nLen=count($arrVar);// 对变量使用函数

		$arrNot=explode(',',$GLOBALS['_commonConfig_']['TEMPLATE_NOT_ALLOWS_FUNC']);// 取得模板禁止使用函数列表
		for($nI=0; $nI<$nLen; $nI++){
			if(0===stripos($arrVar[$nI],'default=')){
				$arrArgs=explode('=',$arrVar[$nI],2);
			}else{
				$arrArgs=explode('=',$arrVar[$nI]);
			}

			$arrArgs[0]=trim($arrArgs[0]);// 模板函数过滤
			switch(strtolower($arrArgs[0])){
				case 'default':// 特殊模板函数
					$sName='('.$sName.')? ('.$sName.'): '.$arrArgs[1];
					break;
				default:// 通用模板函数
					if(!in_array($arrArgs[0],$arrNot)){
						if(isset($arrArgs[1])){
							if(strstr($arrArgs[1],'**')){
								$arrArgs[1]=str_replace('**',$sName,$arrArgs[1]);
								$sName="$arrArgs[0]($arrArgs[1])";
							}else{
								$sName="$arrArgs[0]($sName,$arrArgs[1])";
							}
					}elseif(!empty($arrArgs[0])){
						$sName="$arrArgs[0]($sName)";
					}
				}
			}
		}

		return $sName;
	}

	public function arrayHandler(&$arrVars,$nType=1,$nGo=2){
		$nLen=count($arrVars);

		$sParam='';
		if($nType==1){// 类似$hello['test']['test2']
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.="['{$arrVars[$nI]}']";
			}
		}elseif($nType=='2'){// 类似$hello->test1->test2
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.="->{$arrVars[$nI]}";
			}
		}elseif($nType=='3'){// 类似$hello.test1.test2
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.=".{$arrVars[$nI]}";
			}
		}

		return $sParam;
	}

}

class TemplateNodeAttributeParser extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oAttribute){
		$sAttributeSource=trim($oAttribute->getCompiled());

		self::escapeCharacter($sAttributeSource);

		// 正则匹配
		$arrRegexp[]="/(([^=\s]+)=)?\*([^\']+)\*/";// xxx=*yyy* 或 *yyy* 格式
		$arrRegexp[]="/(([^=\s]+)=)?\"([^\"]+)\"/";// xxx="yyy" 或 "yyy" 格式
		$arrRegexp[]="/(([^=\s]+)=)?'([^\']+)'/";// xxx='yyy' 或 'yyy' 格式
		$arrRegexp[]="/(([^=\s]+)=)?([^\s]+)/";// xxx=yyy 或 yyy 格式
		$nNameIdx=2;
		$nValueIdx=3;
		$nDefaultIdx=0;
		foreach($arrRegexp as $sRegexp){
			if(preg_match_all($sRegexp,$sAttributeSource,$arrRes)){
				foreach($arrRes[0] as $nIdx=>$sAttribute){
					$sName=$arrRes[$nNameIdx][$nIdx];
					$sValue=&$arrRes[$nValueIdx][$nIdx];
					if(empty($sName)){
						$sName='condition'.$nDefaultIdx++;
					}
					self::escapeCharacter($sValue,false);
					$oAttribute->setAttribute($sName,$sValue);
					$sAttributeSource=str_replace($sAttribute,'',$sAttributeSource);
				}
			}
		}
	}

	static public function escapeCharacter(&$sTxt,$bEsc=true){
		if($sTxt=='""'){
			$sTxt='';
		}
		
		if($bEsc){// 转义
			$sTxt=str_replace('\\\\','\\',$sTxt);// 转义 \
			$sTxt=str_replace("\\'",'~~{#!`!#}~~',$sTxt);// 转义 '
			$sTxt=str_replace('\\"','~~{#!``!#}~~',$sTxt);// 转义 "
			$sTxt=str_replace('\\$','~~{#!S!#}~~',$sTxt);// 转义 $
			$sTxt=str_replace('\\.','~~{#!dot!#}~~',$sTxt);// 转义 .
		}else{// 还原
			$sTxt=str_replace('.','->',$sTxt);// 成员访问符 '->' 符号与  节点的边界符号('<>')冲突，以 . 替代 属性中 出现的 ->
			$sTxt=str_replace('[greater]','>',$sTxt);
			$sTxt=str_replace('[less]','<',$sTxt);
			$sTxt=str_replace("~~{#!`!#}~~","'",$sTxt);// 还原 '
			$sTxt=str_replace('~~{#!``!#}~~','"',$sTxt);// 还原 "
			$sTxt=str_replace('~~{#!S!#}~~','$',$sTxt);// 还原 $
			$sTxt=str_replace('~~{#!dot!#}~~','.',$sTxt);// 还原 .
		}

		return $sTxt;
	}

	static public function regToCompiler(){}

}

class TemplateNodeCompiler_tpl extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){}

	public function compile(TemplateObj $oObj){}

	static public function regToCompiler(){
		TemplateNodeParser::regCompilers('tpl',__CLASS__);
	}

}
TemplateNodeCompiler_tpl::regToCompiler();

class TemplateNodeCompiler_tpl_assign extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='name';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return true;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);
		$oAttribute=$oObj->getAttribute();// 节点属性
		$sName=$oAttribute->getAttribute('name');// 各项属性
		$sName=$this->parseVar($sName);

		$sValue=$oAttribute->getAttribute('value');
		if($sValue===null){
			$sValue='';
		}

		if('$'==substr($sValue,0,1)){
			$sValue=$this->parseVar(substr($sValue,1));
		}else{
			$sValue='\''.$sValue.'\'';
		}

		$sCompiled='<?php '.$sName.'='.$sValue.'; ?>';// 编译
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('assign',__CLASS__);
		$oParent->regCompilers('assign',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_assign::regToCompiler();

class TemplateNodeCompiler_tpl_else extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){
		return in_array(strtolower($sNodeName),array('else','tpl:else'));
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);
		$oObj->setCompiled("<?php else:?>");
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('else',__CLASS__);
		$oParent->regCompilers('else',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_else::regToCompiler();

class TemplateNodeCompiler_tpl_elseif extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag( $sNodeName){
		return in_array(strtolower($sNodeName),array('elseif','tpl:elseif'));
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sCondition=$oAttribute->getAttribute('condition');// 循环条件
		$sCondition=str_replace('->','.',$sCondition);
		$sCondition=$this->parseCondition($sCondition);
		$sCondition=str_replace(':','->',$sCondition);
		$sCondition=str_replace('+','::',$sCondition);
		$sCondition=str_replace('^',':',$sCondition);
		$sCondition=str_replace('.','->',$sCondition);
		$oObj->setCompiled("<?php elseif({$sCondition}):?>");
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('elseif',__CLASS__);
		$oParent->regCompilers('elseif',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_elseif::regToCompiler();

class TemplateNodeCompiler_tpl_foreach extends TemplateNodeCompilerBase{

	static private $_nForeachId=0;

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='for';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sFor=$oAttribute->getAttribute('for');// 各项属性
		$sKey=$oAttribute->getAttribute('key');
		$sValue=$oAttribute->getAttribute('value');
		$sIndex=$oAttribute->getAttribute('index');

		if($sKey===null){
			$sKey='key';
		}

		if($sValue===null){
			$sValue='value';
		}

		if($sIndex===null){
			$sIndex='i';
		}

		$sFor=str_replace('->','.',$sFor);
		$sFor=$this->parseCondition($sFor);
		$sKey=str_replace('->','.',$sKey);
		$sKey=$this->parseCondition($sKey);
		$sValue=str_replace('->','.',$sValue);
		$sValue=$this->parseCondition($sValue);
		$sIndex=str_replace('->','.',$sIndex);
		$sIndex=$this->parseCondition($sIndex);
		$sFor=trim($sFor);
		$sFor=str_replace('.','->',$sFor);
		$sKey=trim($sKey);
		$sValue=trim($sValue);
		$sIndex=trim($sIndex);

		$oBody=$oObj->getBody();// 循环体
		$sBody=$oBody->getCompiled();

		// 编译
		$sCompiled="<?php \${$sIndex}=1;?>
<?php if(is_array(\${$sFor})):foreach(\${$sFor} as \${$sKey}=>\${$sValue}):?>
{$sBody}
<?php \${$sIndex}++;?>
<?php endforeach;endif;?>";
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('foreach',__CLASS__);
		$oParent->regCompilers('foreach',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_foreach::regToCompiler();

class TemplateNodeCompiler_tpl_if extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='condition';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sCondition=$oAttribute->getAttribute('condition');// 条件 表达式
		$sCondition=str_replace('->','.',$sCondition);
		$sCondition=$this->parseCondition($sCondition);
		$sCondition=str_replace(':','->',$sCondition);
		$sCondition=str_replace('+','::',$sCondition);
		$sCondition=str_replace('^',':',$sCondition);
		$oBody=$oObj->getBody();// 条件 体
		$sBody=$oBody->getCompiled();
		$sCompiled="<?php if({$sCondition}):?>{$sBody}<?php endif;?>";
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('if',__CLASS__);
		$oParent->regCompilers('if',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_if::regToCompiler();

class TemplateNodeCompiler_tpl_include extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='file';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return true;
	}
	
	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sFilename=$oAttribute->getAttribute('file');// 文件名
		$nNotchild=$oAttribute->getAttribute('notchild');// 是否载入子模板缓存
		$nNotchild=$nNotchild==1?1:0;
		$sFilename=str_replace('->','.',$sFilename);// 将 -> 还原为 .
		$sFilename=$this->parseCondition($sFilename); // 替换一下，防止迁移的时候由于物理路径的原因，需要重新生成编译文件
		$sFilename=str_replace(C::tidyPath(Q_PATH),'Q_PATH.\'',C::tidyPath($sFilename));
		$sFilename=str_replace(C::tidyPath(TEMPLATE_PATH),'TEMPLATE_PATH.\'',C::tidyPath($sFilename));
		$sFilename=rtrim($sFilename,'\'');
		$sFilename=strpos($sFilename,'$')===0 || strpos($sFilename,'(')?$sFilename:$sFilename.'\'';
		
		if(strpos($sFilename,':\\') || strpos($sFilename,'/')===0){
			$sFilename='\''.$sFilename;
		}

		$sCompiled="<!--<####incl*".md5($sFilename)."*ude####>-->";
		$sCompiled.="<?php \$this->includeChildTemplate({$sFilename},__FILE__,\"".str_replace('$','\$',$sFilename)."\");?>";
		$sCompiled.="<!--</####incl*".md5($sFilename)."*ude####/>-->";
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	public function parseCondition($sTmplPublicName){
		$arrTemplateInfo=array();

		if(strpos($sTmplPublicName,'(')){// 静态方法直接返回数据
			return $sTmplPublicName;
		}elseif(''==$sTmplPublicName){// 如果模板文件名为空 按照默认规则定位
			$arrTemplateInfo=array('file'=>MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].ACTION_NAME.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']);
		}else if(!strpos($sTmplPublicName,':\\') && strpos($sTmplPublicName,'/')!==0 &&
			substr($sTmplPublicName,0,1)!='$' && !is_file($sTmplPublicName)
		){//支持加载变量文件名&D:\phpcondition\......排除绝对路径分析&/home1/...... 排除Linux环境下面的绝对路径分析
			if(strpos($sTmplPublicName,'@')){// 分析主题
				$arrArray=explode('@',$sTmplPublicName);
				$arrTemplateInfo['theme']=ucfirst(strtolower(array_shift($arrArray)));
				$sTmplPublicName=array_shift($arrArray);
			}

			$arrValue=explode('+',$sTmplPublicName);
			$sTmplModuleName=$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/' && $arrValue[0]=='public'?'Public':$arrValue[0];
			$sTmplPublicName=str_replace($arrValue[0].'+',$sTmplModuleName.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'],$sTmplPublicName);// 分析文件&模块和操作
			$arrTemplateInfo['file']=$sTmplPublicName.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX'];
		}

		if(!empty($arrTemplateInfo)){
			$sPath=!empty($arrTemplateInfo['theme']) || $GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?
				dirname(dirname(__TMPL_FILE_PATH__)):
				dirname(__TMPL_FILE_PATH__);
			return $sPath.'/'.(!empty($arrTemplateInfo['theme'])?$arrTemplateInfo['theme'].'/':'').$arrTemplateInfo['file'];
		}else{
			if(substr($sTmplPublicName,0,1)=='$'){// 返回变量
				return $sTmplPublicName;
			}else{
				return $sTmplPublicName;
			}
		}
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('include',__CLASS__);
		$oParent->regCompilers('include',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_include::regToCompiler();

class TemplateNodeCompiler_tpl_lang extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
	}

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sLange=$oAttribute->getAttribute('lang');// 指定语言
		$sLange=$sLange?'\''.ucfirst($sLange).'\'':'null';
		$sPackage=$oAttribute->getAttribute('package');// 指定语言包
		$sPackage=$sPackage?'\''.ucfirst($sPackage).'\'':'null';
		$sArgs='';// 参数
		$oAttribute=$oObj->getAttribute();// 节点属性
		foreach($oAttribute->_arrAttributes as $sAttributeName=>$sVarExpress){// 变量
			$sAttributeName=$oAttribute->getAttributeOriginName($sAttributeName);
			if(preg_match('/^condition\d{1,2}$/',$sAttributeName)){// 匿名属性用作语句的参数
				$sConditionValue=str_replace('->','.',$sVarExpress);
				$sConditionValue=$this->parseCondition($sConditionValue);

				if(!empty($sConditionValue)){
					$sArgs.=',"'.$sConditionValue.'"';
				}
			}
		}

		$oBody=$oObj->getBody();// 句子

		if(!is_object($oBody)){
			Q::E("{$sPackage} sentence can not to be empty");
		}

		$sSentence=addcslashes(stripslashes(trim($oBody->getCompiled())),'"');
		$sCompiled="<?php echo Q::L(\"{$sSentence}\",{$sPackage},{$sLange}{$sArgs});?>";
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('lang',__CLASS__);
		$oParent->regCompilers('lang',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_lang::regToCompiler();

class TemplateNodeCompiler_tpl_loop extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='start';
	}

	static public function queryCanbeSingleTag( $sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sStart=$oAttribute->getAttribute('start');// 循环条件
		$sEnd=$oAttribute->getAttribute('end');
		$sVar=$oAttribute->getAttribute('var');
		$sStep=$oAttribute->getAttribute('step');
		$sType=$oAttribute->getAttribute('type');

		if($sEnd===NULL){// 缺省值
			$sEnd='0';
		}

		if($sVar===NULL){
			$sVar='nLoopValue'.rand(0,99999);
		}

		if($sStep===NULL){
			$sStep='1';
		}

		if($sType=='-'){
			$sComparison='>=';
			$sIncreaseDecrease='-=';
		}else{
			$sComparison='<=';
			$sIncreaseDecrease='+=';
		}

		$sStartVarName='$nLoopStart'.rand(0,99999);// 循环记号
		$sEndVarName='$nLoopEnd'.rand(0,99999);
		$oBody=$oObj->getBody();// 循环体
		$sBody=$oBody->getCompiled();
		$sCompiled="<?php {$sStartVarName}={$sStart};{$sEndVarName}={$sEnd};
for(\${$sVar}={$sStartVarName};\${$sVar}{$sComparison}{$sEndVarName};\${$sVar}{$sIncreaseDecrease}{$sStep}):?>
{$sBody}
<?php endfor;?>";

		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('loop',__CLASS__);
		$oParent->regCompilers('loop',__CLASS__);
		TemplateNodeParser::regCompilers('for',__CLASS__);
		$oParent->regCompilers('for',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_loop::regToCompiler();

class TemplateNodeCompiler_tpl_php extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$oBody=$oObj->getBody();// 条件体
		$sBody=$oBody->getCompiled();
		$sBody=$this->parseCondition($sBody);
		$sCompiled="<?php {$sBody} ?>";
		$oObj->setCompiled($sCompiled);
		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('php',__CLASS__);
		$oParent->regCompilers('php',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_php::regToCompiler();

class TemplateNodeCompiler_tpl_volist extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='name';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$oBody=$oObj->getBody();// 循环体
		$sBody=$oBody->getCompiled();
		$sName=$oAttribute->getAttribute('name');// 各项属性
		$sId=$oAttribute->getAttribute('id');
		$sEmpty=$oAttribute->getAttribute('empty');
		$sI=$oAttribute->getAttribute('i');
		$sKey=$oAttribute->getAttribute('key');
		$nMod=$oAttribute->getAttribute('mod');
		$nLength=$oAttribute->getAttribute('length');
		$nOffset=$oAttribute->getAttribute('offset');

		if($sI===null){// 默认解析
			$sI='i';
		}

		if($sKey===null){
			$sKey='key';
		}

		if($nMod===null){
			$nMod=2;
		}

		if(preg_match("/[^\d-.,]/",$nMod)){
			$nMod='$'.$nMod;
		}

		if($sEmpty===null){
			$sEmpty='';
		}

		if($nLength===null){
			$nLength='';
		}

		if($nOffset===null){
			$nOffset='';
		}

		$sName=$this->parseVar($sName);
		$sCompiled='<?php if(is_array('.$sName.')):$'.$sI.'=0;';

		if(''!=$nLength){
			$sCompiled.='$arrList=array_slice('.$sName.','.$nOffset.','.$nLength.');';
		}elseif(''!=$nOffset){
			$sCompiled.='$arrList=array_slice('.$sName.','.$nOffset.');';
		}else{
			$sCompiled.='$arrList='.$sName.';';
		}

		$sCompiled.='if(count($arrList)==0):echo"'.$sEmpty.'";';
		$sCompiled.='else:';
		$sCompiled.='foreach($arrList as $'.$sKey.'=>$'.$sId.'):';
		$sCompiled.='++$'.$sI.';';
		$sCompiled.='$mod=($'.$sI.'%'.$nMod.')?>';
		$sCompiled.=$sBody;
		$sCompiled.='<?php endforeach;endif;else:echo "'.$sEmpty.'";endif;?>';
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('volist',__CLASS__);
		$oParent->regCompilers('volist',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_volist::regToCompiler();

class TemplateNodeCompiler_tpl_while extends TemplateNodeCompilerBase{

	public function __construct(){
		parent::__construct();
		$this->_arrNotNullAttributes[]='condition';
	}

	static public function queryCanbeSingleTag($sNodeName){
		return false;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sCondition=$oAttribute->getAttribute('condition');// 循环条件
		$sCondition=$this->parseCondition($sCondition);
		$oBody=$oObj->getBody();// 循环体
		$sBody=$oBody->getCompiled();
		$oObj->setCompiled("<?php while({$sCondition}):?>{$sBody}<?php endwhile;?>");
		$oObj->setCompiler(null);
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('while',__CLASS__);
		$oParent->regCompilers('while',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_while::regToCompiler();

class TemplateNodeCompiler_tpl_tagphp extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){
		return true;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sPos=$oAttribute->getAttribute('condition');// 条件表达式
		if(strtolower($sPos)=='start'){
			$sRet='<'.'?php echo "&lt;?php";?'.'>';
		}else{
			$sRet='<'.'?php echo "?&gt;";?'.'>';
		}
		$oObj->setCompiled($sRet);

		return $sRet;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_tpl');
		TemplateNodeParser::regCompilers('tagphp',__CLASS__);
		$oParent->regCompilers('tagphp',__CLASS__);
	}

}
TemplateNodeCompiler_tpl_tagphp::regToCompiler();

/** 载入扩展标签库 */
if(defined('QEEPHP_TEMPLATE_COMPILERS_EXTEND') && is_dir(QEEPHP_TEMPLATE_COMPILERS_EXTEND.'/Node')){
	$arrFiles=C::listDir(QEEPHP_TEMPLATE_COMPILERS_EXTEND.'/Node',true,true);
	foreach($arrFiles as $sFile){
		if(C::getExtName($sFile,2)=='php'){
			require_once($sFile);
		}
	}
}

/**
 * The demo node
 * 
 * (注释版节点)
 * < 模板中使用 “<!--<demo:if />-->”或者“<!--<demo:demochild />-->”，结果为“-- DEMO END --” >
 * < 模板中使用 “<!--<demo:if condition="start"/>-->”，结果为“-- DEMO START --” >
 */
class TemplateNodeCompiler_demo extends TemplateNodeCompilerBase{

	static public function queryCanbeSingleTag($sNodeName){}

	public function compile(TemplateObj $oObj){}

	static public function regToCompiler(){
		TemplateNodeParser::regCompilers('demo',__CLASS__);
		// this can be other name
		// TemplateNodeParser::regCompilers('mydemo',__CLASS__);
	}

}
TemplateNodeCompiler_demo::regToCompiler();

class TemplateNodeCompiler_demochild extends TemplateNodeCompiler_demo{

	static public function queryCanbeSingleTag($sNodeName){
		return true;
	}

	public function compile(TemplateObj $oObj){
		$this->checkNode($oObj);

		$oAttribute=$oObj->getAttribute();// 节点属性
		$sPos=$oAttribute->getAttribute('condition');// 条件表达式
		if(strtolower($sPos)=='start'){
			$sRet='-- DEMO START --';
		}else{
			$sRet='-- DEMO END --';
		}
		$oObj->setCompiled($sRet);

		return $sRet;
	}

	static public function regToCompiler(){
		$oParent=Q::instance('TemplateNodeCompiler_demo');
		//TemplateNodeParser::regCompilers('demochild',__CLASS__);// demochild标签，可以不要
		$oParent->regCompilers('if',__CLASS__);// demo::if 和demo::demochild互为别名，结果一样
		$oParent->regCompilers('demochild',__CLASS__);
	}

}
TemplateNodeCompiler_demochild::regToCompiler();
/** demo end */

/** 
 * 代码编译器
 */
abstract class TemplateCodeCompilerBase{

	public function __construct(){
		$this->init_();
	}

	public function init_(){}

	public function arrayHandler(&$arrVars,$nType=1,$nGo=2){
		$nLen=count($arrVars);

		$sParam='';
		if($nType==1){// 类似$hello['test']['test2']
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.="['{$arrVars[$nI]}']";
			}
		}elseif($nType=='2'){// 类似$hello->test1->test2
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.="->{$arrVars[$nI]}";
			}
		}elseif($nType=='3'){// 类似$hello.test1.test2
			for($nI=$nGo;$nI<$nLen;$nI++){
				$sParam.=".{$arrVars[$nI]}";
			}
		}

		return $sParam;
	}

}

/* while循环 */
class TemplateCodeCompiler_while extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode('<'."?php while({$sContent}):".'?>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('while',__CLASS__);
	}

}
TemplateCodeCompiler_while::regToCompiler();

/* 变量 */
class TemplateCodeCompiler_variable extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sContent=!empty($sContent)?$this->parseContent($sContent):NULL;
		$sCompiled=TemplateRevertParser::encode($sContent);
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	public function parseContent($sContent){
		$sContent=str_replace(':','->',$sContent);// 以|分割字符串,数组第一位是变量名字符串,之后的都是函数参数&&函数{$hello|md5}

		$arrVar=explode('|',$sContent);
		$sVar=array_shift($arrVar);// 弹出第一个元素,也就是变量名
		if(strtolower(substr($sContent,0,2))=='q.'){// 系统变量
			$sName=$this->parseQ($sVar);
		}elseif(strpos($sVar,'.')){// 访问数组元素或者属性
			$arrVars=explode('.',$sVar);
			if(substr($arrVars['1'],0,1)=="'" or substr($arrVars['1'],0,1)=='"' or substr($arrVars['1'],0,1)=="$"){
				$sName='$'.$arrVars[0].'.'.$arrVars[1].($this->arrayHandler($arrVars,3));// 特殊的.连接字符串
			}else{
				$bIsObject=FALSE;// 解析对象的方法
				if(substr($sContent,-1)==')'){
					$bIsObject=TRUE;
				}

				if($bIsObject===FALSE){// 非对象
					 switch(strtolower($GLOBALS['_commonConfig_']['TMPL_VAR_IDENTIFY'])) {
						case 'array': // 识别为数组
							$sName='$'.$arrVars[0].'[\''.$arrVars[1].'\']'.($this->arrayHandler($arrVars));
							break;
						case 'obj':  // 识别为对象
							$sName='$'.$arrVars[0].'->'.$arrVars[1].($this->arrayHandler($arrVars,2));
							break;
						default:  // 自动判断数组或对象 支持多维
							$sName='is_array($'.$arrVars[0].')?$'.$arrVars[0].'[\''.$arrVars[1].'\']'.($this->arrayHandler($arrVars)).' :$'.$arrVars[0].'->'.$arrVars[1].($this->arrayHandler($arrVars,2));
							break;
					}
				}else{
					$sName='$'.$arrVars[0].'->'.$arrVars[1].($this->arrayHandler($arrVars,2));
				}
			}
			$sVar=$arrVars[0];
		}elseif(strpos($sVar,'[')){// $hello['demo'] 方式访问数组
			$sName="$".$sVar;
			preg_match('/(.+?)\[(.+?)\]/is',$sVar,$arrArray);
			$sVar=$arrArray[1];
		}else{
			$sName="\$$sVar";
		}

		if(count($arrVar)>0){// 如果有使用函数
			$sName=$this->parseVarFunction($sName,$arrVar);// 传入变量名,和函数参数继续解析,这里的变量名是上面的判断设置的值
		}

		$sName=str_replace('^',':',$sName);

		$sCode=!empty($sName)?"<?php echo({$sName});?>":'';

		return $sCode;
	}

	public function parseQ($sVar){
		$arrVars=explode('.',$sVar);// 解析‘.’

		$arrVars[1]=strtoupper(trim($arrVars[1]));
		$nLen=count($arrVars);
		$sAction="\$_@";
		if($nLen>=3){// cookie,session,get等等
			if(in_array(strtoupper($arrVars[1]),array('COOKIE','SESSION','GET','POST','SERVER','ENV','REQUEST'))){// PHP常用系统变量 < 忽略大小写 >
				$sCode=str_replace('@',$arrVars[1],$sAction).$this->arrayHandler($arrVars);// 替换调名称,并将使用arrayHandler函数获取下标,支持多维 ，以$demo[][][]方式
			}elseif(strtoupper($arrVars[1])=='LANG'){
				$sCode='Q::L(\''.addslashes(stripslashes($arrVars[2])).'\''.(isset($arrVars[3])?',\''.$arrVars[3].'\'':',null').(isset($arrVars[4])?',\''.$arrVars[4].'\'':',null').')';
			}elseif(strtoupper($arrVars[1])=='CONFIG'){
				$sCode='Q::C(\''.strtoupper($arrVars[2]).(isset($arrVars[3])?'.'.$arrVars[3]:'').'\')';
			}elseif( strtoupper($arrVars[1])=='CONST'){
				$sCode=strtoupper($arrVars[2]);
			}else{
				$sCode='';
			}
		}elseif($nLen===2){// 时间
			if(strtoupper($arrVars[1])=='NOW' or strtoupper($arrVars[1])=='TIME'){
				$sCode="date('Y-m-d H:i:s',time())";
			}elseif(strtoupper($arrVars[1])=='VERSION' || strtoupper($arrVars[1])=='QEEPHP_VERSION'){
				$sCode='QEEPHP_VERSION';
			}elseif(strtoupper($arrVars[1])=='LEFTTAG' || strtoupper($arrVars[1])=='LEFT'){
				$sCode='"{"';
			}elseif(strtoupper($arrVars[1])=='RIGHTTAG' || strtoupper($arrVars[1])=='RIGHT'){
				$sCode='"}"';
			}elseif(strtoupper($arrVars[1])=='TEMPLATE' || strtoupper($arrVars[1])=='BASENAME'){
				$sCode='__TMPL_FILE_NAME__';
			}else{
				$sCode=$arrVars[1];
			}
		}

		return $sCode;
	}

	public function parseVarFunction($sName,$arrVar){
		$nLen=count($arrVar);

		// 取得模板禁止使用函数列表
		$arrNot=explode(',',$GLOBALS['_commonConfig_']['TEMPLATE_NOT_ALLOWS_FUNC']);
		for($nI=0;$nI<$nLen;$nI++){
			if(0===stripos($arrVar[$nI],'default=')){
				$arrArgs=explode('=',$arrVar[$nI],2);
			}else{
				$arrArgs=explode('=',$arrVar[$nI]);
			}

			$arrArgs[0]=trim($arrArgs[0]);
			$arrArgs[0]=str_replace('+','::',$arrArgs[0]);
			if(isset($arrArgs[1])){
				$arrArgs[1]=str_replace('->',':',$arrArgs[1]);
			}

			switch(strtolower($arrArgs[0])) {
				case 'default':// 特殊模板函数
					$sName='('.$sName.')?('.$sName.'):'.$arrArgs[1];
					break;
				default:// 通用模板函数
					if(!in_array($arrArgs[0],$arrNot)){
						if(isset($arrArgs[1])){
							if(strstr($arrArgs[1],'**')){
								$arrArgs[1]=str_replace('**',$sName,$arrArgs[1]);
								$sName="$arrArgs[0]($arrArgs[1])";
							}else{
								$sName="$arrArgs[0]($sName,$arrArgs[1])";
							}
					}elseif(!empty($arrArgs[0])){
						$sName="$arrArgs[0]($sName)";
					}
				}
			}
		}

		return $sName;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('$',__CLASS__);
	}

}
TemplateCodeCompiler_variable::regToCompiler();

/* PHP脚本 */
class TemplateCodeCompiler_script extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode('<'."?php {$sContent};?".'>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('~',__CLASS__);
	}

}
TemplateCodeCompiler_script::regToCompiler();

/* 注释 */
class TemplateCodeCompiler_note extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode(' ');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('#',__CLASS__);
	}

}
TemplateCodeCompiler_note::regToCompiler();

/* Javascript初始标签 */
class TemplateCodeCompiler_js_code extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sCompiled=TemplateRevertParser::encode("<script type=\"text/javascript\">");
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('script',__CLASS__);
	}

}
TemplateCodeCompiler_js_code::regToCompiler();

/* if标签 */
class TemplateCodeCompiler_if extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sContent=$this->parseContent($sContent);
		$sCompiled=TemplateRevertParser::encode('<'."?php {$sContent}:?".'>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	public function parseContent($sContent){
		$sContent=str_replace(':','->',$sContent);

		$arrArray=explode(' ',$sContent);
		$bObj=false;
		$arrParam=array();
		foreach($arrArray as $sV){
			if(strpos($sV,'.')>0){
				$arrArgs=explode('.',$sV);
				$arrParam[]=$arrArgs[0].($this->arrayHandler($arrArgs,1,1));// 以$hello['hello1']['hello2']['hello2']方式
				$arrParamTwo[]=$arrArgs[0].($this->arrayHandler($arrArgs,2,1));// 以$hello->'hello1->'hello2'->'hello2'方式
				$bObj=true;
			}else{
				$arrParam[]=$sV;
				$arrParamTwo[]=$sV;
			}
		}

		if($bObj){
			$sStr='is_array('.$arrArgs[0].')'.'?'.join(' ',$arrParam).':'.join(' ',$arrParamTwo);
		}else{
			$sStr=join(' ',$arrParam);
		}

		$sStr=str_replace('+','::',$sStr);
		$sStr=str_replace('^',':',$sStr);

		return "if({$sStr})";
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('if',__CLASS__);
	}

}
TemplateCodeCompiler_if::regToCompiler();

/* foreach循环 */
class TemplateCodeCompiler_foreach extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sContent=$this->parseContent($sContent);
		$sCompiled=TemplateRevertParser::encode('<'."?php {$sContent}:".'?>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	public function parseContent($sContent){
		preg_match_all('/\\$([\S]+)/',$sContent,$arrArray);

		$arrArray=$arrArray[1];
		$nNum=count($arrArray);
		if($nNum>0){
			if($nNum==2){
				$sResult="\${$arrArray[1]}";
			}elseif($nNum==3){
				$sResult="\${$arrArray[1]}=>\${$arrArray[2]}";
			}else{
				Q::E(Q::L('foreach,list的参数错误。','__QEEPHP__@Q'));
			}
			
			return "if(is_array(\${$arrArray[0]})):foreach(\${$arrArray[0]} as $sResult)";
		}
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('foreach',__CLASS__);
		TemplateCodeParser::regCompilers('list',__CLASS__);
	}

}
TemplateCodeCompiler_foreach::regToCompiler();

/* for循环 */
class TemplateCodeCompiler_for extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode('<'."?php for({$sContent}):".'?>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('d*for',__CLASS__);
	}

}
TemplateCodeCompiler_for::regToCompiler();

/* 部分常用结束标签 */
class TemplateCodeCompiler_endtag extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sContent=$this->parseContent($sContent);
		$sCompiled=TemplateRevertParser::encode($sContent);
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	public function parseContent($sContent){
		// do while 处理
		if(trim(substr($sContent,0,7))=='dowhile'){
			$sContent=trim(substr($sContent,7));
			$sContent="<?php }while({$sContent});?>";
		}

		switch($sContent){
			case 'list':
			case 'foreach':
				$sContent='<?php endforeach;endif;?>';
				break;
			case 'd*for':
				$sContent='<?php endfor;?>';
				break;
			case 'while':
				$sContent='<?php endwhile;?>';
				break;
			case 'script':
				$sContent='</script>';
				break;
			case 'if':
				$sContent='<?php endif;?>';
				break;
			case 'style':
				$sContent='</style>';
				break;
		}

		return $sContent;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('/',__CLASS__);
	}

}
TemplateCodeCompiler_endtag::regToCompiler();

/* elseif标签 */
class TemplateCodeCompiler_elseif extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sContent=$this->parseContent($sContent);
		$sCompiled=TemplateRevertParser::encode('<'."?php {$sContent}:?".'>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	public function parseContent($sContent){
		$sContent=str_replace(':','->',$sContent);

		$arrArray=explode(' ',$sContent);
		$bObj=false;
		$arrParam=array();
		foreach($arrArray as $sV){
			if(strpos($sV,'.') > 0){
				$arrArgs =explode('.',$sV);
				$arrParam[]=$arrArgs[0].($this->arrayHandler($arrArgs,1,1));// 以$hello['hello1']['hello2']['hello2']方式
				$arrParamTwo[]=$arrArgs[0].($this->arrayHandler($arrArgs,2,1));// 以$hello->'hello1->'hello2'->'hello2'方式
				$bObj=true;
			}else{
				$arrParam[]=$sV;
				$arrParamTwo[]=$sV;
			}
		}

		if($bObj){
			$sStr='is_array('.$arrArgs[0].')'.'?'.join(' ',$arrParam).' : '.join(' ',$arrParamTwo);
		}else{
			$sStr=join(' ',$arrParam);
		}

		$sStr=str_replace('+','::',$sStr);
		$sStr=str_replace('^',':',$sStr);

		return "elseif({$sStr})";
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('elseif',__CLASS__);
	}

}
TemplateCodeCompiler_elseif::regToCompiler();

/* else标签 */
class TemplateCodeCompiler_else extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sCompiled=TemplateRevertParser::encode('<'."?php else:?".'>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('d*else',__CLASS__);
	}

}
TemplateCodeCompiler_else::regToCompiler();

/* PHP echo标签 */
class TemplateCodeCompiler_echo extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode('<'."?php echo({$sContent});".'?>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers(':',__CLASS__);
	}

}
TemplateCodeCompiler_echo::regToCompiler();

/* PHP do while */
class TemplateCodeCompiler_do_while extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sCompiled=TemplateRevertParser::encode('<'."?php do{".'?>');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('dowhile',__CLASS__);
	}

}
TemplateCodeCompiler_do_while::regToCompiler();

/* CSS内嵌开始标签 */
class TemplateCodeCompiler_css_style extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sCompiled=TemplateRevertParser::encode("<style type=\"text/css\">");
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('style',__CLASS__);
	}

}
TemplateCodeCompiler_css_style::regToCompiler();

/** 载入扩展标签库 */
if(defined('QEEPHP_TEMPLATE_COMPILERS_EXTEND') && is_dir(QEEPHP_TEMPLATE_COMPILERS_EXTEND.'/Code')){
	$arrFiles=C::listDir(QEEPHP_TEMPLATE_COMPILERS_EXTEND.'/Code',true,true);
	foreach($arrFiles as $sFile){
		if(C::getExtName($sFile,2)=='php'){
			require_once($sFile);
		}
	}
}

/**
 * The demo node
 *
 * (注释版节点)
 * < 模板中使用 “<!--{demo}-->”或者“<!--{demotest}-->”，结果为“-- DEMO TEST --” >
 */
class TemplateCodeCompiler_demotest extends TemplateCodeCompilerBase{

	public function compile(TemplateObj $oObj){
		$sContent=$oObj->getContent();
		$sCompiled=TemplateRevertParser::encode('-- DEMO TEST --');
		$oObj->setCompiled($sCompiled);
		$oObj->setCompiler(null);

		return $sCompiled;
	}

	static public function regToCompiler(){
		TemplateCodeParser::regCompilers('demo',__CLASS__);// demo和demotest等价
		TemplateCodeParser::regCompilers('demotest',__CLASS__);
	}

}
TemplateCodeCompiler_demotest::regToCompiler();

/**
 * 全局编译器
 */
class TemplateGlobalCompiler{

	static private $_oGlobalInstance;

	public function __construct(){}

	public function compile(TemplateObj $oObj){
		$sCompiled=TemplateGlobalParser::encode($oObj->getCompiled());
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function getGlobalInstance(){
		if(!self::$_oGlobalInstance){
			self::$_oGlobalInstance=new TemplateGlobalCompiler();
		}

		return self::$_oGlobalInstance;
	}

}

/**
 * 全局恢复编译
 */
class TemplateGlobalRevertCompiler{

	static private $_oGlobalInstance;

	public function compile(TemplateObj $oObj){
		$sCompiled=$oObj->getContent();
		$sCompiled=base64_decode($sCompiled);
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function getGlobalInstance(){
		if(!self::$_oGlobalInstance){
			self::$_oGlobalInstance=new TemplateGlobalRevertCompiler();
		}

		return self::$_oGlobalInstance;
	}

}

/**
 * Php编译器
 */
class TemplatePhpCompiler{

	static private $_oGlobalInstance;

	public function __construct(){}

	public function compile(TemplateObj $oObj){
		$sCompiled = $oObj->getCompiled();// 获取编译内容

		if($GLOBALS['_commonConfig_']['PHP_OFF']===false){// 是否允许模板中存在php代码
			$arrPreg[]='/<\?(=|php|)(.+?)\?>/is';
			$arrReplace[]='&lt;?\\1\\2?&gt;';
			$sCompiled=preg_replace($arrPreg,$arrReplace,$sCompiled);
		}
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function getGlobalInstance(){
		if(!self::$_oGlobalInstance){
			self::$_oGlobalInstance=new TemplatePhpCompiler();
		}

		return self::$_oGlobalInstance;
	}

}

/**
 * 恢复编译
 */
class TemplateRevertCompiler{

	static private $_oGlobalInstance;

	public function compile(TemplateObj $oObj){
		$sCompiled=$oObj->getCompiled();
		$sCompiled=base64_decode($sCompiled);
		$oObj->setCompiled($sCompiled);

		return $sCompiled;
	}

	static public function getGlobalInstance(){
		if(!self::$_oGlobalInstance){
			self::$_oGlobalInstance=new TemplateRevertCompiler();
		}

		return self::$_oGlobalInstance;
	}

}
