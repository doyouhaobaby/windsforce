<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   控制器($$)*/

!defined('Q_PATH') && exit;

class Controller{

	protected $_oView=null;
	public $_bIsError=false;
	public $_oParent=null;

	public function __construct(){
		$this->_oView=new View($this);
	}

	public function init__(){}

	public function assign($Name,$Value=''){
		$this->_oView->assign($Name,$Value);
	}

	public function __set($Name,$Value){
		$this->assign($Name,$Value);
	}

	public function get($sName){
		$sValue=$this->_oView->get($sName);
		return $sValue;
	}

	public function &__get($sName){
		$value=$this->get($sName);
		return $value;
	}

	public function display($sTemplateFile='',$sCharset='utf-8',$sContentType='text/html',$bReturn=false){
		return $this->_oView->display($sTemplateFile,$sCharset,$sContentType,$bReturn);
	}

	public function child($sPath,$sAction){
		$sFile=$this->includeController($sPath,$sClassController);
		require_once($sFile);
		$sClassController=str_replace('Controller','_C_Controller',$sClassController);
		$oClass=new $sClassController($this);
		$oClass->{$sAction}();
	}
	
	protected function includeController($sPath,&$sClassController){
		$sFilepath=APP_PATH.'/App/Class/Controller/';
		$arrValue=explode('@',$sPath);
		if(!isset($arrValue[1])){
			Q::E('IncludeController parameter is error');
		}
		$sFilepath.=$arrValue[0].'_/';
		$arrValue=explode('/',$arrValue[1]);
		$sClassController=array_pop($arrValue).'Controller';
		$sClass=$sClassController.'_.php';
		$sFilepath.=($arrValue?implode('/',$arrValue).'/':'').$sClass;

		if(!is_file($sFilepath)){
			Q::E(sprintf('Include Controller %s failed',$sFilepath));
		}

		return $sFilepath;
	}

	protected function G($sName,$sViewName=null){
		$value=$this->_oView->getVar($sName);
		return $value;
	}

	protected function E($sMessage='',$nDisplay=3,$bAjax=FALSE){
		$this->J($sMessage,0,$nDisplay,$bAjax);
	}

	protected function S($sMessage,$nDisplay=1,$bAjax=FALSE){
		$this->J($sMessage,1,$nDisplay,$bAjax);
	}

	protected function A($Data,$sInfo='',$nStatus=1,$nDisplay=1,$sType=''){
		$arrResult=array();

		$arrResult['status']=$nStatus;
		$arrResult['display']=$nDisplay;
		$arrResult['info']=$sInfo?$sInfo:Q::L('Ajax未指定返回消息','__QEEPHP__@Q');
		$arrResult['data']=$Data;

		if(empty($sType)){
			$sType=$GLOBALS['_commonConfig_']['DEFAULT_AJAX_RETURN'];
		}
		$arrResult['type']=$sType;

		if(strtoupper($sType)=='JSON'){// 返回JSON数据格式到客户端 包含状态信息
			header("Content-Type:text/html; charset=utf-8");
			exit(json_encode($arrResult));
		}elseif(strtoupper($sType)=='XML'){// 返回xml格式数据
			header("Content-Type:text/xml; charset=utf-8");
			exit(C::xmlEncode($arrResult));
		}elseif(strtoupper($sType)=='EVAL'){// 返回可执行的js脚本
			header("Content-Type:text/html; charset=utf-8");
			exit($Data);
		}else{}
	}

	protected function U($sUrl,$arrParams=array(),$nDelay=0,$sMsg=''){
		$sUrl=Q::U($sUrl,$arrParams);
		C::urlGo($sUrl,$nDelay,$sMsg);
	}

	public function __call($sMethod,$arrArgs){
		switch($sMethod){
			case 'isPost':
			case 'isGet':
				return strtolower($_SERVER['REQUEST_METHOD'])==strtolower(substr($sMethod,2));
			case 'q':
				if(!empty($arrArgs[0])){
					return Q::G($arrArgs[0],isset($arrArgs[1])?$arrArgs[1]:'R');
				}else{
					Q::E('Can not find method.');
				}
			default:
				Q::E('Can not find method.');
		}
	}

	private function J($sMessage,$nStatus=1,$nDisplay=1,$bAjax=FALSE){
		if($nStatus==1){
			$this->_bIsError=false;
		}else{
			$this->_bIsError=true;
		}
		
		// 判断是否为AJAX返回
		if($bAjax || C::isAjax()){
			$this->A('',$sMessage,$nStatus,$nDisplay);
		}

		// 提示标题
		if(!$this->G('__MessageTitle__')){
			$this->assign('__MessageTitle__',$nStatus?Q::L('操作成功','__QEEPHP__@Q'):Q::L('操作失败','__QEEPHP__@Q'));
		}

		// 关闭窗口
		if($this->G('__CloseWindow__')){
			$this->assign('__JumpUrl__','javascript:window.close();');
		}

		// 消息图片
		if(defined('__MESSAGE_IMG_PATH__')){
			$arrMessageImg=array(
				'loader'=>__MESSAGE_IMG_PATH__.'/loader.gif',
				'infobig'=>__MESSAGE_IMG_PATH__.'/info_big.gif',
				'errorbig'=>__MESSAGE_IMG_PATH__.'/error_big.gif'
			);
		}else{
			$arrMessageImg=array(
				'loader'=>'Public/Images/loader.gif',
				'infobig'=>'Public/Images/info_big.gif',
				'errorbig'=>'Public/Images/error_big.gif'
			);

			foreach($arrMessageImg as $sKey=>$sMessageImg){
				$arrMessageImg[$sKey]=is_file(TEMPLATE_PATH.'/'.$arrMessageImg[$sKey])?__TMPL__.'/'.$arrMessageImg[$sKey]:__THEME__.'/Default/'.$arrMessageImg[$sKey];
			}
		}

		$this->assign('__LoadingImg__',$arrMessageImg['loader']);
		$this->assign('__InfobigImg__',$arrMessageImg['infobig']);
		$this->assign('__ErrorbigImg__',$arrMessageImg['errorbig']);

		// 状态
		$this->assign('__Status__',$nStatus);
		if($nStatus){
			$this->assign('__Message__',$sMessage);// 提示信息
		}else{
			$this->assign('__ErrorMessage__',$sMessage);
		}

		$arrInit=array();

		if($nStatus){
			if(!$this->G('__WaitSecond__')){// 成功操作后默认停留1秒
				$this->assign('__WaitSecond__',1);
				$arrInit['__WaitSecond__']=1;
			}else{
				$arrInit['__WaitSecond__']=$this->G('__WaitSecond__');
			}

			if(!$this->G('__JumpUrl__')){// 默认操作成功自动返回操作前页面
				$this->assign('__JumpUrl__',isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'');
				$arrInit['__JumpUrl__']=isset($_SERVER["HTTP_REFERER"])? $_SERVER["HTTP_REFERER"]:'';
			}else{
				$arrInit['__JumpUrl__']=$this->G('__JumpUrl__');
			}

			$sJavaScript=$this->javascriptR($arrInit);
			$this->assign('__JavaScript__',$sJavaScript);

			$sTemplate=strpos($GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS'],'public+')===0 && $GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?
				str_replace('public+','Public+',$GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS']):
				$GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS'];

			$this->display($sTemplate);
		}else{
			if(!$this->G('__WaitSecond__')){// 发生错误时候默认停留3秒
				$this->assign('__WaitSecond__',3);
				$arrInit['__WaitSecond__']=3;
			}else{
				$arrInit['__WaitSecond__']=$this->G('__WaitSecond__');
			}

			if(!$this->G('__JumpUrl__')){// 默认发生错误的话自动返回上页
				if(preg_match('/(mozilla|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT'])){
					$this->assign('__JumpUrl__','javascript:history.back(-1);');
				}else{// 手机
					$this->assign('__JumpUrl__',__APP__);
				}
				$arrInit['__JumpUrl__']='';
			}else{
				$arrInit['__JumpUrl__']=$this->G('__JumpUrl__');
			}

			$sJavaScript=$this->javascriptR($arrInit);
			$this->assign('__JavaScript__',$sJavaScript);

			$sTemplate=strpos($GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR'],'public+')===0 && $GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?
				str_replace('public+','Public+',$GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR']):
				$GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR'];

			$this->display($sTemplate);
		}

		exit;
	}

	private function javascriptR($arrInit){
		extract($arrInit);
		return "<script type=\"text/javascript\">var nSeconds={$__WaitSecond__};var sDefaultUrl=\"{$__JumpUrl__}\";onload=function(){if((sDefaultUrl=='javascript:history.go(-1)' || sDefaultUrl=='') && window.history.length==0){document.getElementById('__JumpUrl__').innerHTML='';return;};window.setInterval(redirection,1000);};function redirection(){if(nSeconds<=0){window.clearInterval();return;};nSeconds --;document.getElementById('__Seconds__').innerHTML=nSeconds;if(nSeconds==0){document.getElementById('__Loader__').style.display='none';window.clearInterval();if(sDefaultUrl!=''){window.location.href=sDefaultUrl;}}}</script>";
	}

}

class PController extends Controller{

	public function __construct($oParentcontroller=null){
		parent::__construct();
		$this->_oParent=$oParentcontroller;
	}

}
