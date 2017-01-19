<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   导航条缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheNav{

	public static function cache(){
		$arrNavs=array();

		// 主导航菜单
		$arrNavs['main']=array();
		$arrNavs['main']=self::getNav_(0);

		// 头部菜单
		$arrNavs['header']=array();
		$arrNavs['header']=self::getNav_(1);

		// 底部菜单
		$arrNavs['footer']=array();
		$arrNavs['footer']=self::getNav_(2);

		Core_Extend::saveSyscache('nav',$arrNavs);
	}

	static private function getNav_($nLocation=0){
		$arrReturnNav=array();

		if($nLocation==2){
			$arrWhere=array('nav_status'=>1,'nav_location'=>2);
			$arrWhere2=array('nav_status'=>1,'nav_location'=>2);
		}elseif($nLocation==1){
			$arrWhere=array('nav_status'=>1,'nav_location'=>1);
			$arrWhere2=array('nav_status'=>1,'nav_location'=>1);
		}else{
			$arrWhere=array('nav_parentid'=>0,'nav_status'=>1,'nav_location'=>0);
			$arrWhere2=array('nav_status'=>1,'nav_location'=>0);
		}
		
		$arrNavs=Model::F_('nav',$arrWhere)
			->order('nav_sort ASC')
			->getAll();
		if(is_array($arrNavs)){
			foreach($arrNavs as $key=>$oNav){
				$arrReturnNav[$key]=array(
					'title'=>$oNav['nav_name'],
					'description'=>self::getDescription_($oNav),
					'link'=>self::getNavUrl_($oNav),
					'style'=>self::getColorAndStyle_($oNav),
					'target'=>self::getTarget_($oNav),
					'app'=>self::getApp_($oNav),
				);

				// 查询子菜单
				$arrReturnNav[$key]['sub']=array();
				$arrWhereTwo['nav_parentid']=$oNav['nav_id'];
				$arrNavSubs=Model::F_('nav',$arrWhereTwo)
					->order('nav_sort ASC')
					->getAll();
				foreach($arrNavSubs as $oNavSub){
					$arrReturnNav[$key]['sub'][]=array(
						'title'=>$oNavSub['nav_name'],
						'description'=>self::getDescription_($oNavSub),
						'link'=>self::getNavUrl_($oNavSub),
						'style'=>self::getColorAndStyle_($oNavSub),
						'target'=>self::getTarget_($oNavSub),
						'app'=>self::getApp_($oNavSub),
					);
				}
			}
		}

		return $arrReturnNav;
	}

	static private function getNavUrl_($oNav){
		if($oNav['nav_identifier']=='sethomepage'){
			return "javascript:void(0)";
		}

		if($oNav['nav_identifier']=='setfavorite'){
			return Core_Extend::getSiteurl();
		}

		return (!$oNav['nav_type']?Q::U($oNav['nav_url']):Core_Extend::getEvalValue($oNav['nav_url']));
	}

	static private function getColorAndStyle_($oNav){
		$sStyle='';
		$arrStyle=array();
		if(!empty($oNav['nav_style'])){
			$arrStyle=unserialize($oNav['nav_style']);
		}else{
			$arrStyle=array(0=>0,1=>0,2=>0);
		}

		$sStyle.=self::getColor_($oNav['nav_color']);
		$sStyle.=self::getStyle_($arrStyle);
		if(!empty($sStyle)){
			$sStyle="style=\"{$sStyle}\";";
		}

		if($oNav['nav_identifier']=='sethomepage'){
			$sStyle.=" onclick=\"setHomepage('".Core_Extend::getSiteurl()."');\"";
		}

		if($oNav['nav_identifier']=='setfavorite'){
			$sStyle.=" onclick=\"addFavorite(this.href,'{$GLOBALS['_option_']['site_name']}');return false;\"";
		}

		return $sStyle;
	}

	static private function getColor_($nStyle){
		switch($nStyle){
			case 1:
				return 'color:red;';
				break;
			case 2:
				return 'color:orange;';
				break;
			case 3:
				return 'color:yellow;';
				break;
			case 4:
				return 'color:green;';
				break;
			case 5:
				return 'color:cyan;';
				break;
			case 6:
				return 'color:blue;';
				break;
			case 7:
				return 'color:purple;';
				break;
			case 8:
				return 'color:gray;';
				break;
			default:
				return '';
				break;
		}
	}

	static private function getStyle_($arrStyle){
		$sBackStyle='';

		if(!empty($arrStyle[0])){
			$sBackStyle.="text-decoration: underline;";
		}

		if(!empty($arrStyle[1])){
			$sBackStyle.="font-style: italic;";
		}

		if(!empty($arrStyle[2])){
			$sBackStyle.="font-weight: bold;";
		}

		return $sBackStyle;
	}

	static private function getTarget_($oNav){
		return ($oNav['nav_target']==1?'target="_blank"':'');
	}

	static private function getDescription_($oNav){
		return ($oNav['nav_title']?$oNav['nav_title']:$oNav['nav_name']);
	}

	static public function getApp_($oNav){
		$sApp='';
		if(strpos($oNav['nav_url'],'://')){
			$sApp=C::subString($oNav['nav_url'],0,strpos($oNav['nav_url'],'://'));
		}
		return $sApp;
	}

}
