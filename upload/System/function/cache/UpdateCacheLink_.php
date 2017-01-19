<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   友情链接缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheLink{

	public static function cache(){
		$sTightlinkContent=$sTightlinkText=$sTightlinkLogo='';

		$arrLinks=Model::F_('link','link_status=?',1)
			->order('link_sort DESC')
			->getAll();
		if(!empty($arrLinks)){
			foreach($arrLinks as $oLink){
				$sLinklogo=Core_Extend::getEvalValue($oLink['link_logo']);
				$sLinkurl=Core_Extend::getEvalValue($oLink['link_url']);
				
				if($oLink['link_description']){
					if($oLink['link_logo']){
						$sTightlinkContent.='<li><div class="home-logo"><img src="'.$sLinklogo.'" border="0" alt="'.$oLink['link_name'].'" /></div>
							<div class="home-content"><h5><a href="'.$sLinkurl.'" target="_blank">'.$oLink['link_name'].'</a></h5><p>'.$oLink['link_description'].'</p></div></li>';
					}else{
						$sTightlinkContent.='<li><div class="home-content"><h5><a href="'.$sLinkurl.'" target="_blank">'.$oLink['link_name'].'</a></h5><p>'.$oLink['link_description'].'</p></div></li>';
					}
				}else{
					if($oLink['link_logo']){
						$sTightlinkLogo.='<a href="'.$sLinkurl.'" target="_blank"><img src="'.$sLinklogo.'" border="0" alt="'.$oLink['link_name'].'" /></a>';
					}else{
						$sTightlinkText.='<li><a href="'.$sLinkurl.'" target="_blank" title="'.$oLink['link_name'].'">'.$oLink['link_name'].'</a></li>';
					}
				}
			}
		}

		$arrData['link_content']=$sTightlinkContent;
		$arrData['link_text']=$sTightlinkText;
		$arrData['link_logo']=$sTightlinkLogo;

		Core_Extend::saveSyscache('link',$arrData);
	}

}
