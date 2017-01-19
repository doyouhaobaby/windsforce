<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   附件显示($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	public function index(){
		$nAttachmentid=intval(Q::G('id','G'));
		if(empty($nAttachmentid)){
			$this->E(Q::L('你没有指定要查看的附件','Controller'));
		}

		$arrAttachment=Model::F_('attachment','@A','attachment_id=?',$nAttachmentid)
			->joinLeft(Q::C('DB_PREFIX').'attachmentcategory AS B','B.attachmentcategory_name','A.attachmentcategory_id=B.attachmentcategory_id')
			->join(Q::C('DB_PREFIX').'user AS C','C.user_sign,C.user_name,C.user_nikename','A.user_id=C.user_id')
			->getOne();
		if(empty($arrAttachment['attachment_id'])){
			$this->E(Q::L('你要查看的文件不存在','Controller'));
		}

		// 判断邮件等外部地址过来的查找评论地址
		$nIsolationCommentid=intval(Q::G('isolation_commentid','G'));
		if($nIsolationCommentid){
			$result=AttachmentcommentModel::getCommenturlByid($nIsolationCommentid);
			if($result===false){
				$this->E(Q::L('该条评论已被删除、屏蔽或者尚未通过审核','Controller'));
			}

			C::urlGo($result);
			exit();
		}

		// 读取评论列表
		$arrWhere=array();
		$arrWhere['A.attachmentcomment_status']=1;
		$arrWhere['A.attachment_id']=$nAttachmentid;

		$nTotalRecord=Model::F_('attachmentcomment','@A')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num']);
		$arrAttachmentcommentLists=Model::F_('attachmentcomment','@A')->where($arrWhere)
			->setColumns('A.attachmentcomment_id,A.create_dateline,A.user_id,A.attachmentcomment_name,A.attachmentcomment_content,A.attachment_id')
			->order('A.attachmentcomment_id ASC')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrAttachment['attachment_name'].' - '.Q::L('多媒体','Controller')));

		$this->assign('arrAttachment',$arrAttachment);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->assign('nTotalAttachmentcomment',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrAttachmentcommentLists',$arrAttachmentcommentLists);
		$this->display('attachment+show');
	}

	public function show_attachment($arrAttachment){
		$sAttachmentType=Attachment_Extend::getAttachmenttype($arrAttachment);
		if(in_array($sAttachmentType,array('img','swf','wmp','mp3','qvod','flv','url'))){
			if(is_callable(array($this,'show_'.$sAttachmentType))){
				call_user_func(array($this,'show_'.$sAttachmentType),$arrAttachment);
			}else{
				Q::E('callback not exist');
			}
		}else{
			$this->show_download($arrAttachment);
		}
	}

	public function show_img($arrAttachment){
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showimg');
	}

	public function show_download($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/download.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showdownload');
	}

	public function show_url($arrAttachment){
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showurl');
	}

	public function show_swf($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/swf.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showswf');
	}
	public function show_flv($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/swf.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showflv');
	}

	public function show_wmp($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/wmp.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showwmp');
	}

	public function show_qvod($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/qvod.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showqvod');
	}

	public function show_mp3($arrAttachment){
		$this->assign('sAttachmentIcon',__PUBLIC__.'/images/common/media/mp3.gif');
		$this->assign('arrAttachment',$arrAttachment);
		$this->display('attachment+showmp3');
	}

	public function mp3list(){
		header("Content-Type: text/xml; charset=utf-8");
		
		$nAttachmentcategoryid=intval(Q::G('cid','G'));
		$nUserid=intval(Q::G('uid','G'));
		
		$arrUser=Model::F_('user','user_id=? AND user_status=1',$nUserid)->setColumns('user_id,user_name')->getOne();
		if(empty($arrUser['user_id'])){
			return false;
		}

		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\">
					<title>{$arrUser['user_name']}".Q::L('专辑','Controller')."</title>
					<creator>Dew</creator>
					<link>".Core_Extend::getSiteurl()."</link>
					<info>{$arrUser['user_name']}".Q::L('专辑','Controller')."</info>
					<image></image>
					<trackList>";
		
		if($nUserid>0){
			$arrAttachments=Model::F_('attachment','@A','A.user_id=? AND A.attachmentcategory_id=? AND A.attachment_extension=?',$nUserid,$nAttachmentcategoryid,'mp3')
				->setColumns('A.attachmentcategory_id,')
				->joinLeft(Q::C('DB_PREFIX').'attachmentcategory AS B','B.attachmentcategory_cover,B.attachmentcategory_name','A.attachmentcategory_id=B.attachmentcategory_id')
				->order('A.attachment_id DESC')
				->getAll();

			if($arrAttachments){
				foreach($arrAttachments as $arrAttachment){
					$sAttachmentcategory=$arrAttachment['attachmentcategory_name']?$arrAttachment['attachmentcategory_name']:Q::L('未分类','Controller');
					echo "<track>
							<location>".Attachment_Extend::getAttachmenturl($arrAttachment)."</location>
							<creator>{$arrAttachment['attachment_username']}</creator>
							<album>{$sAttachmentcategory}</album>
							<title>{$arrAttachment['attachment_name']}</title>
							<annotation>{$arrAttachment['attachment_name']}</annotation>
							<duration>{$arrAttachment['attachment_size']}</duration>
							<image></image>
							<info></info>
							<link></link>
						</track>";
				}
			}
		}

		echo "</trackList>
			</playlist>";
	}

	public function get_attachmentcategory_playlist($arrAttachment){
		return Core_Extend::windsforceOuter('app=home&c=attachment&a=mp3list&cid='.
			$arrAttachment['attachmentcategory_id'].'&uid='.$arrAttachment['user_id']);
	}

}
