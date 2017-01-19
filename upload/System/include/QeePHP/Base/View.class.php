<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   视图管理类($$)*/

!defined('Q_PATH') && exit;

class View{

	private $_oPar=null;
	private $_oTemplate;
	static private $_oShareGlobalTemplate;
	private $_sTemplateFile;
	private $_nRuntime;

	public function __construct($oPar,$sTemplate=null,$oTemplate=null){
		if($oTemplate){
			$this->setTemplate($oTemplate);
		}else{
			$this->setTemplate(self::createShareTemplate());
		}

		$this->_sTemplateFile=$sTemplate;
		$this->_oPar=$oPar;

		$this->init__();
	}

	public function init__(){}

	public function parseTemplateFile($sTemplateFile){
		$arrTemplateInfo=array();
		if(empty($sTemplateFile)){
			$sSuffix=$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX'];
			$arrTemplateInfo=array(
				'file'=>MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].ACTION_NAME.$sSuffix
			);
		}elseif(!strpos($sTemplateFile,':\\') && strpos($sTemplateFile,'/')!==0 && !is_file($sTemplateFile)){// D:\phpcondition\......排除绝对路径分析
			if(strpos($sTemplateFile,'@')){// 分析主题
				$arrArray=explode('@',$sTemplateFile);
				$arrTemplateInfo['theme']=ucfirst(strtolower(array_shift($arrArray)));
				$sTemplateFile=array_shift($arrArray);
			}

			$sTemplateFile =str_replace('+',$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'],$sTemplateFile);//模块和操作&分析文件
			$sSuffix=$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX'];
			$arrTemplateInfo['file']=$sTemplateFile.$sSuffix;
		}

		if(!empty($arrTemplateInfo)){
			return $arrTemplateInfo;
		}else{
			return $sTemplateFile;
		}
	}

	public function getTemplate(){
		if(is_null($this->_oTemplate)){
			$this->_oTemplate=self::createShareTemplate();
		}
		return $this->_oTemplate;
	}

	public function setTemplate(Template $oTemplate){
		$oOldValue=$this->_oTemplate;
		$this->_oTemplate=$oTemplate;
		return $oOldValue;
	}

	public function getPar(){
		if($this->_oPar===null){
			return null;
		}else{
			return $this->_oPar;
		}
	}

	static public function createShareTemplate(){
		if(!self::$_oShareGlobalTemplate){
			self::$_oShareGlobalTemplate=new Template();
		}

		return self::$_oShareGlobalTemplate;
	}

	public function display($sTemplateFile='',$sCharset='utf-8',$sContentType='text/html',$bReturn=false){
		header("Content-Type:".$sContentType."; charset=".$sCharset);
		header("Cache-control: private");//支持页面回跳

		if(empty($sTemplateFile)){
			$sTemplateFile=&$this->_sTemplateFile;
		}

		$this->_nRuntime=C::getMicrotime();// 记录模板开始运行时间
		$TemplateFile=$sTemplateFile;
		if(!is_file($sTemplateFile)){
			$TemplateFile=$this->parseTemplateFile($sTemplateFile);
		}

		$oTemplate=$this->getTemplate();
		$oController=$this->getPar();
		$oTemplate->setVar('TheView',$this);
		$oTemplate->setVar('TheController',$oController);
		$sContent=$oTemplate->display($TemplateFile,false);

		if(!C::isAjax()){
			if($GLOBALS['_commonConfig_']['SHOW_RUN_TIME']){
				$sContent.=$this->templateRuntime();
			}
			if($GLOBALS['_commonConfig_']['SHOW_PAGE_TRACE']){
				$sContent.=$this->templateTrace();
			}
		}

		if($bReturn===true){
			return $sContent;
		}else{
			echo $sContent;
			unset($sContent);
		}
	}

	public function templateRuntime($bReturn=false){
		if($bReturn===false){
			$sContent='<div id="qeephp_run_time" class="qeephp_run_time" style="display:none;">';
		}else{
			$sContent='';
		}

		// 总时间
		$nEndTime=microtime(TRUE);
		$nTotalRuntime=number_format(($nEndTime-$GLOBALS['_beginTime_']),3);
		$sContent.="Pro ".$nTotalRuntime." (s)";

		if($GLOBALS['_commonConfig_']['SHOW_DETAIL_TIME']){
			$sContent.="(Tpl:".$this->getMicrotime()." (s)";
		}

		if($GLOBALS['_commonConfig_']['SHOW_DB_TIMES']){
			$oDb=Db::RUN();
			$sContent.=" | ".$oDb->getConnect()->Q()." Q";
		}

		if($GLOBALS['_commonConfig_']['SHOW_GZIP_STATUS']){
			if($GLOBALS['_commonConfig_']['START_GZIP']){
				$sGzipString='on';
			}else{
				$sGzipString='off';
			}
			$sContent.=" | Gz {$sGzipString}";
		}

		if(MEMORY_LIMIT_ON && $GLOBALS['_commonConfig_']['SHOW_USE_MEM']){
			$nStartMem=array_sum(explode(' ',$GLOBALS['_startUseMems_']));
			$nEndMem=array_sum(explode(' ',memory_get_usage()));
			$sContent.=' | Mem:'. C::changeFileSize($nEndMem-$nStartMem);
		}

		if($bReturn===false){
			$sContent.="</div>";
		}

		return $sContent;
	}

	public function templateTrace(){
		$arrTrace=array();

		$arrTrace['ThePage']=$_SERVER['REQUEST_URI'];
		$arrTrace['Runtime']=$this->templateRuntime(true);

		$arrLog=Log::$_arrLog;
		$arrTrace[Q::L('日志记录','__QEEPHP__@Q')]=count($arrLog)?Q::L('%d条日志','__QEEPHP__@Q',null,count($arrLog)).'<br/>'.implode('<br/>',$arrLog):Q::L('无日志记录','__QEEPHP__@Q');

		$arrFiles= get_included_files();
		$arrTrace[Q::L('加载文件','__QEEPHP__@Q')]=count($arrFiles).str_replace("\n",'<br/>',substr(substr(print_r($arrFiles,true),7),0,-2));

		ob_start();
		include Q_PATH.'/Resource_/Template/PageTrace.template.php';
		$sContent=ob_get_contents();
		ob_end_clean();

		return $sContent;
	}

	public function getMicrotime(){
		return round(C::getMicrotime()-$this->_nRuntime,5);
	}

	public function assign($Name,$Value=null){
		$oTemplate=$this->getTemplate();
		return $oTemplate->setVar($Name,$Value);
	}

	public function get($Name){
		return $this->getVar($Name);
	}

	public function getVar($Name){
		$oTemplate=$this->getTemplate();
		return $oTemplate->getVar($Name);
	}

}
