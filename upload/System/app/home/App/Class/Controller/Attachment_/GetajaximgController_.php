<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Ajax取得图片($$)*/

!defined('Q_PATH') && exit;

class Getajaximg_C_Controller extends InitController{

	public function index(){
		$nAttachmentid=intval(Q::G('id','G'));
		$nUserid=intval(Q::G('uid','G'));

		if($nUserid<1){
			return array();
		}

		// 展示一定数量的照片
		$nShowimgnum=intval($GLOBALS['_option_']['attachment_showimgnum']);
		$arrAttachments=Model::F_('attachment','user_id=? AND attachment_isimg=1',$nUserid)
			->order('attachment_id DESC')
			->getAll();

		$nIndex=0;
		$sContent='';
		if(is_array($arrAttachments)){
			foreach($arrAttachments as $nKey=>$oAttachment){
				if($nAttachmentid==$oAttachment['attachment_id']){
					$nIndex=$nKey;
				}
			}
		}

		// 取得展示数量索引
		$nAttachmentimgstartnum=$nIndex-$nShowimgnum;
		if($nAttachmentimgstartnum<0){
			$nAttachmentimgstartnum=0;
		}

		$nAttachmentimgendnum=$nIndex+$nShowimgnum;

		$arrShowimgid=array();
		if(!empty($arrAttachments)){
			foreach($arrAttachments as $nKey=>$oAttachment){
				if($nKey>=$nAttachmentimgstartnum && $nKey<=$nAttachmentimgendnum){
					$arrShowimgid[]=$oAttachment['attachment_id'];
					$sLinkurl=__ROOT__.'/user/attachment/'.$oAttachment['attachment_savepath'].'/'.$oAttachment['attachment_savename'];
					$sContent.='<li>
							<div>
								<img src="'.Attachment_Extend::getAttachmentPreview($oAttachment).'" url="'.Q::U('home://file@?id='.$oAttachment['attachment_id']).'" alt="'.$oAttachment['attachment_name'].'" bigImg="'.$sLinkurl.'"">
								</a>
							</div>
						</li>';
				}
			}
		}

		// 取得新的索引
		foreach($arrShowimgid as $nKey=>$nId){
			if($nAttachmentid==$nId){
				$nIndex=$nKey;
			}
		}

		exit(json_encode(array('index'=>$nIndex+1,'content'=>$sContent)));
	}

}
