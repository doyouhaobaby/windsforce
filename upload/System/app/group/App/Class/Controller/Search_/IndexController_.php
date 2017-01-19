<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组搜索首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$sKey=urldecode(trim(Q::G('key')));

		if($sKey){
			if($GLOBALS['_option_']['show_search_result_message']){
				C::urlGo(Q::U('group://search/parse?key='.urlencode($sKey),array(),true),1,nl2br($GLOBALS['_option_']['show_search_result_message']));
			}else{
				C::urlGo(Q::U('group://search/parse?key='.urlencode($sKey),array(),true));
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('帖子搜索','Controller')));

		$this->assign('sKey',$sKey);
		$this->display('search+index');
	}

	public function parse(){
		$sKey=urldecode(trim(Q::G('key')));
		$sKey=htmlspecialchars($sKey);
		$sKey=str_replace('%','\%',$sKey);
		$sKey=str_replace('_','\_',$sKey);

		// 快捷搜索
		$sSearchSubmit=trim(Q::G('searchsubmit','G'));
		$bQuickSearch=false;
		if($sSearchSubmit=='yes'){
			$bQuickSearch=true;
			$_REQUEST['search_date_before']=0;
			$sKey='';
		}

		if(empty($sKey) && $bQuickSearch===false){
			$this->E(Q::L('您没有指定要搜索的关键字','Controller'));
		}

		if($sKey || $bQuickSearch===true){
			if($GLOBALS['_option_']['search_keywords_minlength']>0 && strlen($sKey)<$GLOBALS['_option_']['search_keywords_minlength'] && $bQuickSearch===false){
				$this->E(Q::L('搜索的关键字最少为 %d 字节','Controller',null,$GLOBALS['_option_']['search_keywords_minlength']));
			}

			// 尝试查询搜索结果缓存
			$arrSearchIndex=array('id'=>0,'create_dateline'=>'0');
			
			// 排除快捷搜索
			$sSearchindexPrefix=$GLOBALS['_commonConfig_']['DB_PREFIX'];
			$nCurrentTimeStamp=CURRENT_TIMESTAMP;
			$oDb=Db::RUN();
			$nSearchPostSpace=$GLOBALS['_option_']['search_post_space'];
			$sIp=C::getIp();

			if($bQuickSearch===false){
				$sSql="SELECT groupsearchindex_id,groupsearchindex_searchstring,create_dateline,('{$nSearchPostSpace}'<>'0' AND {$nCurrentTimeStamp}-create_dateline<{$nSearchPostSpace}) AS flood,groupsearchindex_keywords='{$sKey}' AS indexvalid FROM {$sSearchindexPrefix}groupsearchindex WHERE('{$nSearchPostSpace}'<>'0' AND groupsearchindex_ip='{$sIp}' AND {$nCurrentTimeStamp}-create_dateline<{$nSearchPostSpace}) ORDER BY flood";

				// 对缓存结果进行分析
				$arrIndexs=$oDb->getAllRows($sSql);

				$arrTheRightSearchIndex=null;
				if(is_array($arrIndexs)){
					foreach($arrIndexs as $arrIndex){
						if($arrIndex['indexvalid'] && $arrIndex['create_dateline']>$arrSearchIndex['create_dateline']){
							$arrSearchIndex=array('id'=>$arrIndex['groupsearchindex_id'],'create_dateline'=>$arrIndex['create_dateline']);
							$arrTheRightSearchIndex=$arrIndex;
							break;
						}elseif($arrIndex['flood']){
							$this->E(Q::L('对不起,您在 %d 秒内只能进行一次搜索','Controller',null,$nSearchPostSpace));
						}
					}
				}
			}

			// 帖子查询条件
			$arrWhere=array();
			$arrWhere['grouptopic_status']=1;
			$arrWhere['search_type']=trim(Q::G('search_type'));

			$sSearchFilter=trim(Q::G('search_filter'));
			$sSearchFilter=in_array($sSearchFilter,array('all','digest','top'))?$sSearchFilter:'all';
			
			switch($sSearchFilter){
				case 'digest':
					$arrWhere['grouptopic_addtodigest']=array('gt',0);
					break;
				case 'top':
					$arrWhere['grouptopic_sticktopic']=array('gt',0);
					break;
			}

			$sUser=Q::G('search_name');
			if(!empty($sUser)){
				if(Core_Extend::isPostInt($sUser)){
					$arrWhere['user_id']=$sUser;
				}else{
					$oUserModel=Model::F_('user','user_name=? AND user_status=1',$sUser)->setColumns('user_id')->query();
					if(empty($arrUserModel['user_id'])){
						$this->E(Q::L('你搜索的用户名 %s 不存在','Controller',null,$sUser));
					}else{
						$arrWhere['user_id']=$arrUserModel['user_id'];
					}
				}
			}

			$arrWhere['search_date']=intval(Q::G('search_date'));
			$arrWhere['search_date_before']=Q::G('search_date_before');

			// 搜索ID是否存在
			$bExistSearchIndex=false;
			if(!empty($arrSearchIndex['id'])){
				$arrSearchstring=@unserialize($arrTheRightSearchIndex['groupsearchindex_searchstring']);
				$bExistSearchIndex=true;
				foreach($arrWhere as $sKey=>$value){
					if(isset($arrSearchstring[$sKey]) && $value!=$arrSearchstring[$sKey]){
						$bExistSearchIndex=false;
						break;
					}
				}
			}

			if($bExistSearchIndex===true){
				$nSearchId=$arrSearchIndex['id'];
				$arrSearchstring=@unserialize($arrTheRightSearchIndex['groupsearchindex_searchstring']);
				$sTheOrderby=$arrSearchstring['theorderby'];
				$sSearchOrderbyAscdesc=$arrSearchstring['orderby'];
			}else{
				// 构造SQL条件查询数据库
				if(!empty($sKey)){
					if(preg_match("(AND|\+|&|\s)",$sKey) && !preg_match("(OR|\|)",$sKey)){
						$sAndOr=' AND ';
						$sSqlTxtSrch='1';
						$sKey=preg_replace("/(AND |&|)/is","+",$sKey);
					}else{
						$sAndOr=' OR ';
						$sSqlTxtSrch='0';
						$sKey=preg_replace("/(OR |\|)/is","+",$sKey);
					}

					$sKey=str_replace('*','%',addcslashes($sKey,'%_'));
					foreach(explode("+",$sKey) AS $sText){
						$sText=trim($sText);
						if($sText){
							$sSqlTxtSrch.=$sAndOr;
							$sSearchType=trim(Q::G('search_type'));
							$sSearchFilter=trim(Q::G('search_filter'));
							
							$sSqlTxtSrch.=($sSearchType=='fulltext')?"(grouptopic_content LIKE '%".str_replace('_','\_',$sText)."%' OR grouptopic_title LIKE '%".$sText."%')":"grouptopic_title LIKE '%".$sText."%'";
						}
					}
				}else{
					$sSqlTxtSrch='';
				}

				$sTheQuery="SELECT grouptopic_id FROM ".$sSearchindexPrefix."grouptopic WHERE grouptopic_status=1";

				if(isset($arrWhere['grouptopic_sticktopic'])){
					$sTheQuery.=" AND grouptopic_sticktopic>0";
				}
				
				if(isset($arrWhere['grouptopic_addtodigest'])){
					$sTheQuery.=" AND grouptopic_addtodigest>0";
				}

				if(isset($arrWhere['user_id'])){
					$sTheQuery.=" AND user_id=".$arrWhere['user_id'];
				}

				if($arrWhere['search_date']){
					if($arrWhere['search_date_before']==0){
						$arrWhere['create_dateline']=$nCurrentTimeStamp-$arrWhere['search_date'];
						$sTheQuery.=" AND create_dateline>=".($nCurrentTimeStamp-$arrWhere['search_date']);
					}else{
						$arrWhere['create_dateline']=$nCurrentTimeStamp-$arrWhere['search_date'];
						$sTheQuery.=" AND create_dateline<=".($nCurrentTimeStamp-$arrWhere['search_date']);
					}
				}

				$sSearchOrderby=trim(Q::G('search_orderby'));
				$sSearchOrderbyAscdesc=trim(Q::G('search_orderby_ascdesc'));
				$sSearchOrderbyAscdesc=$sSearchOrderbyAscdesc=='asc'?'asc':'desc';
				
				$sTheOrderby='';
				switch($sSearchOrderby){
					case 'update_dateline':
						$sTheOrderby='grouptopic_update';
						break;
					case 'commentnum':
						$sTheOrderby='grouptopic_comments';
						break;
					case 'viewnum':
						$sTheOrderby='grouptopic_views';
						break;
					case 'create_dateline':
					default:
						$sTheOrderby='create_dateline';
						break;
				}

				$arrWhere['theorderby']=$sTheOrderby;
				$arrWhere['orderby']=$sSearchOrderbyAscdesc;

				$sTheQuery.=(!empty($sSqlTxtSrch)?" AND($sSqlTxtSrch)":'')." ORDER BY {$sTheOrderby} {$sSearchOrderbyAscdesc}";

				$nTotals=$sIds=0;
				$arrGrouptopics=$oDb->getAllRows($sTheQuery);

				if(is_array($arrGrouptopics)){
					foreach($arrGrouptopics as $arrGrouptopic){
						$sIds.=','.$arrGrouptopic['grouptopic_id'];
						$nTotals++;
					}
				}

				// 缓存搜索结果
				$oGroupsearchIndex=new GroupsearchindexModel();
				$oGroupsearchIndex->groupsearchindex_keywords=$sKey;
				$oGroupsearchIndex->groupsearchindex_expiration=$nCurrentTimeStamp+$nSearchPostSpace;
				$oGroupsearchIndex->groupsearchindex_searchstring=serialize($arrWhere);
				$oGroupsearchIndex->groupsearchindex_totals=$nTotals;
				$oGroupsearchIndex->groupsearchindex_ids=$sIds;
				$oGroupsearchIndex->save('create');
				if($oGroupsearchIndex->isError()){
					$this->E($oGroupsearchIndex->getErrorMessage());
				}

				$nSearchId=$oGroupsearchIndex->groupsearchindex_id;
			}

			$sOrderMore='';
			if(!empty($sTheOrderby)){
				$sOrderMore.="&orderby={$sTheOrderby}";
			}

			if(!empty($sSearchOrderbyAscdesc)){
				$sOrderMore.="&ascdesc={$sSearchOrderbyAscdesc}";
			}

			$sUrl=Q::U('group://search/result?searchid='.$nSearchId.$sOrderMore);

			if($GLOBALS['_option_']['show_search_result_message']){
				C::urlGo($sUrl,1,nl2br($GLOBALS['_option_']['show_search_result_message']));
			}else{
				C::urlGo($sUrl);
			}
		}else{
			$this->U('group://search/index');
		}
	}

	public function result(){
		$nSearchid=intval(Q::G('searchid','G'));
		$sTheOrderby=trim(Q::G('orderby','G'));
		$sOrderby=trim(Q::G('ascdesc','G'));
		
		$nTotalRecord=null;
		if(empty($sOrderby)){
			$sOrderby='DESC';
		}

		// 帖子列表
		$arrWhere=array();
		$arrWhere['A.grouptopic_status']=1;

		if($nSearchid){
			$arrGroupsearchindexModel=Model::F_('groupsearchindex','groupsearchindex_id=?',$nSearchid)->query();
			if(empty($arrGroupsearchindexModel['groupsearchindex_id'])){
				$this->E(Q::L('你请求的搜索缓存ID不存在','Controller'));
			}

			$sKey=$arrGroupsearchindexModel['groupsearchindex_keywords'];
			$nTotalRecord=$arrGroupsearchindexModel['groupsearchindex_totals'];

			$arrWhere['grouptopic_id']=array('exp','IN('.$arrGroupsearchindexModel['groupsearchindex_ids'].')');
			$arrSearchstring=@unserialize($arrGroupsearchindexModel['groupsearchindex_searchstring']);
			if(!empty($arrSearchstring['theorderby'])){
				$sTheOrderby=$arrSearchstring['theorderby'];
			}

			if(!empty($arrSearchstring['orderby'])){
				$sOrderby=$arrSearchstring['orderby'];
			}

			if($nTotalRecord===null){
				$nTotalRecord=Model::F_('grouptopic','@A')->where($arrWhere)->all()->getCounts();
			}

			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['search_list_num']);
			if(!empty($sTheOrderby)){
				$sOrder="A.{$sTheOrderby} {$sOrderby},A.grouptopic_id DESC";
			}else{
				$sOrder="A.grouptopic_update DESC,A.grouptopic_id DESC";
			}
			
			$arrGrouptopics=Model::F_('grouptopic','@A')->where($arrWhere)
				->join(Q::C('DB_PREFIX').'group AS B','B.group_name,B.group_nikename','A.group_id=B.group_id')
				->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS C','C.grouptopiccategory_name','A.grouptopiccategory_id=C.grouptopiccategory_id')
				->order($sOrder)->limit($oPage->S(),$oPage->N())
				->getAll();
				
			Core_Extend::getSeo($this,array('title'=>($sKey?$sKey.' - ':'').Q::L('搜索结果','Controller')));
			
			$this->assign('arrGrouptopics',$arrGrouptopics);
			$this->assign('nTotalGrouptopicnum',$nTotalRecord);
			$this->assign('nEverynum',$GLOBALS['_option_']['search_list_num']);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
			$this->assign('sKey',$arrGroupsearchindexModel['groupsearchindex_keywords']);
			$this->display('search+result');
		}else{
			$this->E(Q::L('你没有指定搜索缓存ID','Controller'));
		}
	}

}
