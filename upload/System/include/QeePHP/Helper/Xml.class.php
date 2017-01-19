<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Xml 解析类($$)*/

!defined('Q_PATH') && exit;

class Xml{

	public $_oParser=NULL;
	public $_arrDocument;
	public $_arrParent;
	public $_arrStack;
	public $_sLastOpenedTag;
	public $_sData;

	public function __construct(){
		$this->_oParser=xml_parser_create();

		xml_parser_set_option($this->_oParser,XML_OPTION_CASE_FOLDING,false);
		xml_set_object($this->_oParser,$this);
		xml_set_element_handler($this->_oParser,'open','close');
		xml_set_character_data_handler($this->_oParser,'data');
	}

	public function __destruct(){
		xml_parser_free($this->_oParser);
	}

	static public function xmlUnserialize($sXml){
		$oXmlParser=new Xml();
		return $oXmlParser->parse($sXml);
	}

	static public function xmlSerialize(&$arrData,$bHtmlOn=true,$nLevel=0,$sPriorKey=NULL,$sCharset='UTF-8'){
		if($nLevel == 0){
			ob_start();
			echo '<?xml version="1.0" encoding="'.$sCharset.'"?><root>',"\n";
		}

		while((list($sKey, $sValue)=each($arrData))!==false){
			if(!strpos($sKey,' attr')){
				if(is_array($sValue)and array_key_exists(0,$sValue)){
					self::xmlSerialize($sValue,$bHtmlOn,$nLevel,$sKey,$sCharset);
				}else{
					$sTag=$sPriorKey?$sPriorKey:$sKey;
					echo str_repeat("\t",$nLevel),'<',$sTag;
					if(array_key_exists("$sKey attr",$arrData)){
						while((list($sAttrName,$sAttrValue)=each($arrData["$sKey attr"]))!=''){
							echo ' ',$sAttrName,'="',($bHtmlOn?'<![CDATA[':'').$sAttrValue.($bHtmlOn?']]>':''),'"';
						}
						reset($arrData["$sKey attr"]);
					}

				if(is_null($sValue)){
					echo " />\n";
				}elseif(!is_array($sValue)){
					echo '>',($bHtmlOn?'<![CDATA[':'').$sValue.($bHtmlOn?']]>':''),"</$sTag>\n";
				}else{
					echo ">\n",self::xmlSerialize($sValue,$bHtmlOn, $nLevel+1,null,$sCharset),str_repeat("\t", $nLevel),"</$sTag>\n";
				}
			 }
		  }
		}

		reset($arrData);

		if($nLevel==0){
			echo '</root>';
			$sStr=ob_get_contents();
			ob_end_clean();
			return $sStr;
		}
	}

	public function parse(&$sData){
		$this->_arrDocument=array();
		$this->_arrStack=array();
		$this->_arrParent=&$this->_arrDocument;

		return xml_parse($this->_oParser,$sData,true)?$this->_arrDocument:NULL;
	}

	public function open(&$oParser,$sTag,$arrAttributes){
		$this->_sData='';
		$this->_sLastOpenedTag=$sTag;
		if(is_array($this->_arrParent) and array_key_exists($sTag,$this->_arrParent)){
			if(is_array($this->_arrParent[$sTag]) and array_key_exists(0,$this->_arrParent[$sTag])){
				$nKey=countNumericItems($this->_arrParent[$sTag]);
			}else{
				if(array_key_exists("$sTag attr",$this->_arrParent)){
					$arrValue=array('0 attr'=>&$this->_arrParent["$sTag attr"],&$this->_arrParent[$sTag]);
					unset($this->_arrParent["$sTag attr"]);
				}else{
					$arrValue=array(&$this->_arrParent[$sTag]);
				}

				$this->_arrParent[$sTag]=&$arrValue;
				$nKey=1;
			}
			$this->_arrParent=&$this->_arrParent[$sTag];
		}else{
			$nKey=$sTag;
		}

		if($arrAttributes){
			$this->_arrParent["$nKey attr"]=$arrAttributes;
		}

		$this->_arrParent =&$this->_arrParent[$nKey];
		$this->_arrStack[]=&$this->_arrParent;
	}

	public function data(&$oParser,$sData){
		if($this->_sLastOpenedTag!=NULL){
			$this->_sData.=$sData;
		}
	}
	public function close(&$oParser,$sTag){
		if($this->_sLastOpenedTag==$sTag){
			$this->_arrParent=$this->_sData;
			$this->_sLastOpenedTag=NULL;
		}

		array_pop($this->_arrStack);
		if($this->_arrStack){
			$this->_arrParent=&$this->_arrStack[count($this->_arrStack)-1];
		}
	}
}

function countNumericItems(&$array){
	return is_array($array)?count(array_filter(array_keys($array),'is_numeric')):0;
}
