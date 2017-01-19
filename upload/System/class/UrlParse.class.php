<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   URL解析($$)*/

!defined('Q_PATH') && exit;

/**
	本程序来自于云边开源轻博,该版本文件比较旧了，大量网站无法解析，我做了大量的修正工作
	EMAIL:nxfte@qq.com QQ:234027573
*/
class UrlParse{
	
	private $url='';
	private $desc='';

	public function __construct(){}
	
	/**
	 * 这里只解析音乐
	 */
	public function setmusic($url,$desc=''){
		if(!function_exists('curl_init')){
			return array('error'=>Q::L('服务器不支持curl扩展,无法使用本功能','__COMMON_LANG__@Common'));
		}

		$sitelist=array(
			'yinyuetai.com'=>'_webVideo'
		);
		
		$domain=$this->getDomain($url);//引用页地址
			
		//在约束数组中查找
		if(array_key_exists($domain,$sitelist)){//网页解析
			$data=$this->$sitelist[$domain]($url);
		}

		if(is_array($data)){
			if($desc!=''){
				$data['desc']=$desc;
			}

			if($data['type']=='' || $data['id']=='' || $data['img']=='' || $data['title']==''){
				return array('error'=>Q::L('解析的内容不全，请更换网站','__COMMON_LANG__@Common'));
			}

			return $data; 
		}else{
			return array('error'=>Q::L('音乐频道目前只能解析音乐台网站','__COMMON_LANG__@Common'));
		}
	}
	
	public function setvideo($url,$desc='',$type='music'){
		if(!function_exists('curl_init')){
			return array('error'=>Q::L('服务器不支持curl扩展,无法使用本功能','__COMMON_LANG__@Common'));
		}
		
		$sitelist = array(
			'xiami.com'=>'_webMusicGetXiami',
			'youku.com'=>'_webVideo',
			'tudou.com'=>'_webVideo',
			'ku6.com'=>'_webVideo',
			'56.com'=>'_webVideo',
			'sina.com.cn'=>'_webVideo',
			'my.tv.sohu.com'=>'_webVideo',
			'sohu.com'=>'_webVideo',
			'yinyuetai.com'=>'_webVideo'
		);
	
		$domain=$this->getDomain($url);//引用页地址
		
		//在约束数组中查找
		if(array_key_exists($domain,$sitelist)){//网页解析
			$data=$this->$sitelist[$domain]($url);
		}else{
			$data=array();
		}
	
		if(is_array($data)){
			if($desc!=''){
				$data['desc']=$desc;
			}

			if(!$data['img']){
				$data['img']=Core_Extend::getNoneimg();
			}

			if(!$data['title']){
				$data['title']='Nav';
			}

			if($data['type']=='' || $data['id']==''){
				return array('error'=>Q::L('解析的内容不全，请更换网站','__COMMON_LANG__@Common'));
			}

			return $data; 
		}else{
			return array('error'=>Q::L('无法获取地址,请更换一个链接','__COMMON_LANG__@Common'));
		}
	}

	/**
	 * 解析豆瓣电影
	 */
	public function setmovie($url,$desc=''){
		if(!function_exists('curl_init')){
			return array('error'=>Q::L('服务器不支持curl扩展,无法使用本功能','__COMMON_LANG__@Common'));
		}
		
		$domain=$this->getDomain($url);//引用页地址
		if($domain!='douban.com'){
			return array('error'=>Q::L('电影解析只支持豆瓣地址','__COMMON_LANG__@Common'));
		}

		$data=$this->_webMovieDouban($url);
		if(is_array($data)){
			if($desc!=''){
				$data['desc']=$desc;
			}

			if($data['type']=='' || $data['url']=='' || $data['img']=='' || $data['title']==''){
				return array('error'=>Q::L('解析的内容不全，请稍后尝试','__COMMON_LANG__@Common'));
			}

			return $data; 
		}else{
			return array('error'=>Q::L('电影解析只支持豆瓣地址','__COMMON_LANG__@Common'));
		}

		return $rs;
	}
	
	/**
	 * 获取视频内容主方法
	 */
	public function _webVideo($url){
		if(($info=VideoUrlParser::parse($url))){
			return $info;
		}

		return false;
	}
	
	/**
	 * 获取商品解析
	 */
	function setShopdesc($url,$desc=''){
		$sitelist=array('taobao.com'=>'_webShopTaobao',
						'tmall.com'=>'_webShopTaobao',
						'360buy.com'=>'_webShop360buy',
						'vancl.com'=>'_webShopVancl',
						'dangdang.com'=>'_webShopDangdang',
						'amazon.cn'=>'_webShopAmazon'
		); //注册引用解析

		$domain=$this->getDomain($url);//引用页地址
		if(array_key_exists($domain,$sitelist)){//网页解析
			$data=$this->$sitelist[$domain]($url); 
		}

		if(is_array($data)){
			return $data;
		}else{
			return array('error'=>Q::L('我们无法解析这个地址,请尝试更换方法','__COMMON_LANG__@Common'));
		}
	}
	
	/**
	 * 设置自定义的referer
	 */
	public function getRefereData($url,$refere){
		return $this->formPost($url,'',$refere);
	}
	
	private function _webMovieDouban($url){
		include(dirname(__FILE__).'/HtmlDomNode.class.php');

		$html = file_get_html($url);

		$data['type']='douban';
		$data['url']=$url;
		$data['img']=$html->find('img[rel=v:image]',0)->src;
		$data['title']=$html->find('span[property=v:itemreviewed]',0)->innertext;
		$data['directe']=$html->find('a[rel=v:directedBy]',0)->innertext;
		$data['average']=$html->find('strong[property=v:average]',0)->innertext;
		$data['runtime']=$html->find('span[property=v:runtime]',0)->innertext;
		$data['initialReleaseDate']=$html->find('span[property=v:initialReleaseDate]',0)->innertext;
		$data['summary']=$html->find('span[property=v:summary]',0)->innertext;
		$data['summary']=str_replace('<span class="pl">&nbsp; &nbsp;<a href="http://www.douban.com/about?topic=copyright"> &copy; '+Q::L('豆瓣','__COMMON_LANG__@Common')+'</a></span>','',$data['summary']);

		if(!empty($html->find('#mainpic a.trail_link',0)->href)){
			$movieurl=parse_url($movieurl);
			$movieurl=explode('/',$movieurl['path']);
			$data['movie']=$movieurl;
		}
		
		foreach($html->find('span[property=v:genre]') as $element){
			$data['genre'][]=$element->innertext;
		}
		
		foreach($html->find('a[rel=v:starring]') as $element){
			$data['starring'][]=$element->innertext;
		}

		return $data;
	}

	/**
	 * 获取淘宝商品
	 * 正常页面没问题
	 */
	private function _webShopTaobao($url){
		include(dirname(__FILE__).'/HtmlDomNode.class.php');

		$html = file_get_html($url);

		$data['title']=C::gbkToUtf8($html->find('.tb-detail-hd h3 a',0)->innertext,'GB2312','UTF-8');
		if($data['title']==''){
			return false;
		}

		$data['price']=$html->find('#J_StrPrice',0)->innertext;
		if($data['price']==''){
			$html->find('#J_SpanLimitProm',0)->innertext;
		}

		$data['count']=$html->find('.tb-sold-out em',0)->innertext;
		$data['img']=$html->find('#J_ImgBooth',0)->src;
		$data['type']='taobao';
		$data['url']=$url;

		return $data;
	}
	
	/**
	 * 京东商城
	 * todo
	 */
	private function _webShop360buy($url){
		return array();
	}
	
	/**
	 * 凡客
	 * http://item.vancl.com/0159148.html?ref=s_category_1516_1
	 */
	private function _webShopVancl($url){
		include(dirname(__FILE__).'/HtmlDomNode.class.php');

		$html=file_get_html($url);

		$data['title']=strip_tags($html->find('#productname',0)->innertext);
		$data['price']=$html->find('.cuxiaoPrice span strong',0)->innertext;
		$data['count']=str_replace(array('(',')'),array('',''),$html->find('.RsetTabMenu strong',0)->innertext);
		$data['img']=$html->find('#midimg',0)->src;
		$data['type']='vancl';

		return $data;
	}
	
	/**
	 * 当当
	 * todo
	 */
	private function _webShopDangdang($url){
		return array();
	}
	
	/**
	 * Amazon
	 * todo
	 */
	private function _webShopAmazon($url){
		return array();
	}

	/**
	 * 获取域名
	 */
	private function getDomain($url){
		$pattern = "/[\w-]+\.(com|net|org|gov|cc|biz|info|cn|co)(\.(cn|hk))*/";

		preg_match($pattern, $url, $matches);
		if(count($matches)>0){
			return $matches[0];
		}else{
			$rs=parse_url($url);
			$main_url=$rs["host"];
				
			if(!strcmp((sprintf("%u",ip2long($main_url))),$main_url)){
				return $main_url;
			}else{
				$arr=explode(".",$main_url);
				$count=count($arr);
				$endArr=array("com","net","org","3322");//com.cn  net.cn 等情况
			
				if(in_array($arr[$count-2],$endArr)){
					$domain=$arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
				}else{
					$domain=$arr[$count-2].".".$arr[$count-1];
				}

				return $domain;
			}
		}
	}
	
	private function formPost($url,$post_data,$referer=''){
		if(is_array($post_data)){
			$o='';
			foreach($post_data as $k=>$v){
				$o.="$k=".urlencode($v)."&";
			}
		}

		$post_data=substr($o,0,-1);

		$ch=curl_init();
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		if($referer){
			curl_setopt($ch,CURLOPT_REFERER,$referer);
		}

		if($post_data){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
		}

		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_URL,$url);
		
		$result=curl_exec($ch);

		return $result;
	}

}

class VideoUrlParser{
	const USER_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)
		Chrome/8.0.552.224 Safari/534.10";
	const CHECK_URL_VALID = "/(youku\.com|yinyuetai\.com|tudou\.com|ku6\.com|56\.com|letv\.com|video\.sina\.com\.cn|(my\.)?tv\.sohu\.com|v\.qq\.com)/";

	/**
	 * parse 
	 * 
	 * @param string $url 
	 * @param mixed $createObject 
	 * @static
	 * @access public
	 * @return void
	 */
	static public function parse($url='',$createObject=true){
		$lowerurl=strtolower($url);
		preg_match(self::CHECK_URL_VALID,$lowerurl,$matches);

		if(!$matches){
			return false;
		}

		switch($matches[1]){
			case 'youku.com':
				$data=self::_parseYouku($url);
				break;
			case 'tudou.com':
				$data=self::_parseTudou($url);
				break;
			case 'ku6.com':
				$data=self::_parseKu6($url);
				break;
			case '56.com':
				$data=self::_parse56($url);
				break;
			case 'video.sina.com.cn':
				$data=self::_parseSina($url);
				break;
			case 'my.tv.sohu.com':
			case 'tv.sohu.com':
			case 'sohu.com':
				$data=self::_parseSohu($url);
				break;
			case 'yinyuetai.com':
				$data=self::_parseYinyuetai($url);
				break;
			default:
				$data=false;
				break;
		}

		if($data && $createObject){
			$data['object']="<embed src=\"{$data['id']}\" quality=\"high\" width=\"480\" height=\"400\" align=\"middle\" allowNetworking=\"all\" allowScriptAccess=\"always\" type=\"application/x-shockwave-flash\"></embed>";
		}

		return $data;
	}
	
	static private function _parseYinyuetai($url){
		$html=self::_cget($url);

		preg_match("/<meta\sproperty=\"og:title\"\s+content=\"(.*?)\"\/>/i",$html,$title);
		preg_match("/<meta\sproperty=\"og:videosrc\"\s+content=\"(.*?)\"\/>/i",$html,$id);
		preg_match("/<meta\sproperty=\"og:url\"\s+content=\"(.*?)\"\/>/i",$html,$url);
		preg_match("/<meta\sproperty=\"og:image\"\s+content=\"(.*?)\"\/>/i",$html,$image);

		$data['img']=$image[1];
		$data['title']=$title[1];
		$data['url']=$url[1];
		$data['id']=$id[1];
		$data['type']='yinyuetai';

		return $data;
	}

	/**
	 * 优酷网 
	 * http://v.youku.com/v_show/id_XMjI4MDM4NDc2.html
	 * http://player.youku.com/player.php/sid/XMjU0NjI2Njg4/v.swf
	 */ 
	static private function _parseYouku($url){
		preg_match("#id\_(\w+)#",$url,$matches);
		$html=self::_fget($url);
	
		if(substr($url,-4)=='.swf'){
			preg_match("#\/sid\/(\w+)\/v.swf#",$url,$matches);
		}elseif(empty($matches)){
			preg_match("#v_playlist\/#",$url,$mat);
			if(!$mat){
				return false;
			}

			$html=self::_fget($url);
			preg_match("#videoId2\s*=\s*\'(\w+)\'#",$html,$matches);
			if(!$matches){
				return false;
			}
		}

		$link="http://v.youku.com/player/getPlayList/VideoIDS/{$matches[1]}/timezone/+08/version/5/source/out?password=&ran=2513&n=3";

		$retval=self::_cget($link);
		if($retval){
			$json=json_decode($retval,true);
			$data['img']=$json['data'][0]['logo'];
			$data['title']=$json['data'][0]['title'];
			$data['url']=$url;
			$data['id']="http://player.youku.com/player.php/sid/{$matches[1]}/v.swf";
			$data['type']='youku';
			return $data;
		}else{
			return false;
		}
	}

	/**
	 * 土豆网
	 * http://www.tudou.com/programs/view/pRNCO8q6daY/?fr=rec2
	 * http://www.tudou.com/albumplay/YS-uKkKkgNI.html
	 * http://www.tudou.com/listplay/tnOR7XHRyOY/fat7GRYO_fQ.html
	 * http://www.tudou.com/v/pRNCO8q6daY/v.swf
	 */
	static private function _parseTudou($url){
		preg_match("#view/([-\w]+)/#",$url,$matches);

		if(empty($matches) && substr($url,-4)!='.swf'){
			$html=self::_fget($url);

			// http://www.tudou.com/l/tnOR7XHRyOY/&resourceId=0_04_05_99&iid=177432463/v.swf
			if(strpos($url,"/listplay/")!==false){
				preg_match('/,pic\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				$data['img']=$arrMatch[1];
				
				preg_match('/kw\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				//$data['title']=C::gbkToUtf8($arrMatch[1],"GB2312","UTF-8");
				$data['title']=$arrMatch[1];

				preg_match('/iid\s*:\s*([^,\s]+)/s',$html,$arrMatch);
				$iid=$arrMatch[1];

				preg_match('/lcode\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				$lcode=$arrMatch[1];

				$data['id']="http://www.tudou.com/l/{$lcode}/&iid={$iid}/v.swf";
			}elseif(strpos($url,"/albumplay/")!==false){
				// http://www.tudou.com/a/YS-uKkKkgNI/&resourceId=0_04_05_99&iid=131596457/v.swf
				preg_match('/pic\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				$data['img']=$arrMatch[1];
				
				preg_match('/kw\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				//$data['title']=C::gbkToUtf8($arrMatch[1],"GB2312","UTF-8");
				$data['title']=$arrMatch[1];

				preg_match('/iid\s*:\s*([^,\s]+)/s',$html,$arrMatch);
				$iid=$arrMatch[1];

				preg_match('/areaCode\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
				$acode=$arrMatch[1];

				$data['id']="http://www.tudou.com/a/{$acode}/&iid={$iid}/v.swf";
			}else{
				// 还有其他网址类型欢迎提供
				return false;
			}

			$data['url']=$url;
			$data['type']='tudou';

			return $data;
		}

		if(substr($url,-4)=='.swf'){
			$host="www.tudou.com";
			$path=str_replace('http://www.tudou.com','',$url);
		}elseif(!empty($matches[1])){
			$host="www.tudou.com";
			$path="/v/{$matches[1]}/v.swf";
		}else{
			return false;
		}

		$ret=self::_fsget($path,$host);
		if(preg_match("#\nLocation: (.*)\n#",$ret,$mat)){
			parse_str(parse_url(urldecode($mat[1]),PHP_URL_QUERY));
			$html=self::_fget($url);

			// 修正视频预览图解析错误
			$data['img']=$snap_pic;
			$data['title']=$title;
			$data['url']=$url;
			$data['id']=substr($url,-4)=='.swf'?$url:"http://www.tudou.com/v/{$matches[1]}/v.swf";
			$data['type']='tudou';
			return $data;
		}

		return false;
	}

	/**
	 * 酷6网 
	 * http://v.ku6.com/film/show_520/3X93vo4tIS7uotHg.html
	 * http://v.ku6.com/special/show_4926690/Klze2mhMeSK6g05X.html
	 * http://v.ku6.com/show/7US-kDXjyKyIInDevhpwHg...html
	 * http://player.ku6.com/refer/3X93vo4tIS7uotHg/v.swf
	 */
	static private function _parseKu6($url){
		if(preg_match("/show\_/",$url)){
			preg_match("#/([-\w]+)\.html#",$url,$matches);
			$url="http://v.ku6.com/fetchVideo4Player/{$matches[1]}.html";
			$html=self::_fget($url);

			if($html){
				$json=json_decode($html,true);
				if(!$json){
					return false;
				}
				
				$data['img']=$json['data']['picpath'];
				$data['title']=$json['data']['t'];
				$data['url']=$url;
				$data['id']="http://player.ku6.com/refer/{$matches[1]}/v.swf";
				$data['type']='ku6';

				return $data;
			}else{
				return false;
			}
		}elseif(preg_match("/show\//",$url,$matches)){
			// 2013.10.07修正此类网址无法解析的BUG
			$html=self::_fget($url);
			preg_match("/VideoInfo\s?:\s?([^\n]*)};/si",$html,$matches);

			if(empty($matches)){
				return false;
			}

			$str=$matches[1];

			// img
			preg_match("/cover\s?:\s?\"([^\"]+)\"/",$str,$matches);
			$data['img']=$matches[1];

			// title
			preg_match("/title\"?\s?:\s?\"([^\"]+)\"/",$str,$matches);
			$jsstr="{\"title\":\"{$matches[1]}\"}";
			$json=json_decode($jsstr,true);
			$data['title']=$json['title'];

			// url
			$data['url']=$url;

			// id
			preg_match("/id\s?:\s?\"([^\"]+)\"/",$str,$matches);
			$data['id']="http://player.ku6.com/refer/{$matches[1]}/v.swf";

			$data['type']='ku6';
			
			return $data;
		}elseif(substr($url,-4)=='.swf'){
			preg_match("#\/refer\/(\w+)\/v.swf#",$url,$matches);
			$data['img']=Core_Extend::getNoneimg();
			$data['title']=$matches[1];
			$data['url']=$url;
			$data['id']=$url;
			$data['type']='ku6';

			return $data;
		}

		return false;
	}

	/**
	 * 56网
	 * http://www.56.com/u73/v_NTkzMDcwNDY.html
	 * http://player.56.com/v_NTkzMDcwNDY.swf
	 */
	static private function _parse56($url){
		preg_match("#/v_(\w+)\.[html|swf]#",$url,$matches);
		if(empty($matches)){
			return false;
		}

		$link="http://vxml.56.com/json/{$matches[1]}/?src=out";
		$retval=self::_cget($link);

		if($retval){
			$json=json_decode($retval,true);
			$data['img']=$json['info']['img'];
			$data['title']=$json['info']['Subject'];
			$data['url']=$url;
			$data['id']="http://player.56.com/v_{$matches[1]}.swf";
			$data['type']='video56';
			return $data;
		}else{
			return false;
		}
	}

	/**
	 * 搜狐TV
	 * http://my.tv.sohu.com/u/vw/5101536
	 */
	static private function _parseSohu($url){
		$html=self::_fget($url);
		
		preg_match_all("/og:(?:title|image|videosrc)\"\scontent=\"([^\"]+)\"/s",$html,$matches);

		// 这里也修正了，老版本解析结果错乱
		$data['img']=$matches[1][2];
		$data['title']=C::gbkToUtf8($matches[1][1],"GB2312","UTF-8");
		$data['url']=$url;
		$data['id']=$matches[1][0];
		$data['type']='sohu';

		return $data;
	}
		
	/*
	 * 新浪播客
	 * http://video.sina.com.cn/v/b/48717043-1290055681.html
	 * http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=48717043_1290055681_PUzkSndrDzXK+l1lHz2stqkP7KQNt6nki2O0u1ehIwZYQ0/XM5GdatoG5ynSA9kEqDhAQJA4dPkm0x4/s.swf
	 */
	static private function _parseSina($url){
		// 2013.10.07修正无法解析的BUG
		preg_match("/(\d+)(?:\-|\_)(\d+)/",$url,$matches);
		
		$url="http://video.sina.com.cn/v/b/{$matches[1]}-{$matches[2]}.html";
		$html=self::_fget($url);

		preg_match('/pic\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
		$data['img']=$arrMatch[1];

		preg_match('/title\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
		$data['title']=isset($arrMatch[1])?$arrMatch[1]:'sdfsdf';
		
		preg_match('/swfOutsideUrl\s*:\s*["\'](.*?)["\']/s',$html,$arrMatch);
		$data['id']=$arrMatch[1];
	
		$data['url']=$url;
		$data['type']='sina';
		
		return $data;
	}

	/*
	 * 通过 file_get_contents 获取内容
	 */
	static private function _fget($url=''){
		if(!$url){
			return false;
		}

		$html=file_get_contents($url);

		// 判断是否gzip压缩
		if(($dehtml=self::_gzdecode($html))){
			return $dehtml;
		}else{
			return $html;
		}
	}

	/*
	 * 通过 fsockopen 获取内容
	 */
	static private function _fsget($path='/',$host='',$user_agent=''){
		if(!$path || !$host){
			return false;
		}

		$user_agent=$user_agent?$user_agent:self::USER_AGENT;

		$out=<<<HEADER
GET $path HTTP/1.1
Host: $host
User-Agent: $user_agent
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-cn,zh;q=0.5
Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n\r\n
HEADER;

		$fp=@fsockopen($host,80,$errno,$errstr,10);
		if(!$fp){
			return false;
		}

		if(!fputs($fp,$out)){
			return false;
		}

		/** 修正BUG，土豆网解析提示变量$html不存在 */
		$html='';
		while(!feof($fp)){
			$html.=fgets($fp, 1024);
		}

		fclose($fp);

		// 判断是否gzip压缩
		if($dehtml=self::_gzdecode($html)){
			return $dehtml;
		}else{
			return $html;
		}
	}

	/*
	 * 通过 curl 获取内容
	 */
	static private function _cget($url='',$user_agent=''){
		if(!$url){
			return;
		}

		$user_agent=$user_agent?$user_agent:self::USER_AGENT;

		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,0);

		if(strlen($user_agent)){
			curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);
		}

		ob_start();
		curl_exec($ch);
		$html=ob_get_contents();
		ob_end_clean();

		if(curl_errno($ch)){
			curl_close($ch);

			return false;
		}

		curl_close($ch);

		if(!is_string($html) || !strlen($html)){
			return false;
		}

		return $html;

		// 判断是否gzip压缩
		if(($dehtml=self::_gzdecode($html))){
			return $dehtml;
		}else{
			return $html;
		}
	}
	
	static private function _gzdecode($data){
		$len=strlen($data);

		if($len<18 || strcmp(substr($data,0,2),"\x1f\x8b")){
			return null;// Not GZIP format (See RFC 1952) 
		}

		$method=ord(substr($data,2,1));// Compression method 
		$flags=ord(substr($data,3,1)); // Flags 

		if($flags&31!=$flags){
			// Reserved bits are set -- NOT ALLOWED by RFC 1952 
			return null;
		}

		// NOTE: $mtime may be negative (PHP integer limitations) 
		$mtime=unpack("V",substr($data,4,4));
		$mtime=$mtime[1];
		$xfl=substr($data,8,1);
		$os=substr($data,8,1);
		$headerlen=10;
		$extralen=0;
		$extra="";

		if($flags&4){
			// 2-byte length prefixed EXTRA data in header 
			if($len-$headerlen-2<8){
				return false; // Invalid format 
			}

			$extralen=unpack("v",substr($data,8,2));
			$extralen=$extralen[1];
			if($len-$headerlen-2-$extralen<8){
				return false; // Invalid format 
			}

			$extra=substr($data,10,$extralen);
			$headerlen+=2+$extralen;
		}
	
		$filenamelen=0;
		$filename="";

		if($flags&8){
			// C-style string file NAME data in header 
			if($len-$headerlen-1<8){
				return false; // Invalid format 
			}

			$filenamelen=strpos(substr($data,8+$extralen),chr(0));
			if($filenamelen===false || $len-$headerlen-$filenamelen-1<8){
				return false; // Invalid format 
			}

			$filename=substr($data,$headerlen,$filenamelen);
			$headerlen+=$filenamelen+1;
		}
	 
		$commentlen=0;
		$comment="";

		if($flags&16){
			// C-style string COMMENT data in header 
			if($len-$headerlen-1<8){
				return false; // Invalid format 
			}

			$commentlen=strpos(substr($data,8+$extralen+$filenamelen),chr(0));

			if($commentlen===false || $len-$headerlen-$commentlen-1<8){
				return false; // Invalid header format 
			}

			$comment=substr($data,$headerlen,$commentlen);
			$headerlen+=$commentlen+1;
		}
	 
		$headercrc="";

		if($flags&1){
			// 2-bytes (lowest order) of CRC32 on header present 
			if($len-$headerlen-2<8){
				return false;// Invalid format 
			}

			$calccrc=crc32(substr($data,0,$headerlen))&0xffff;
			$headercrc=unpack("v",substr($data,$headerlen,2));
			$headercrc=$headercrc[1];

			if($headercrc!=$calccrc){
				return false; // Bad header CRC 
			}
			$headerlen+=2;
		}
	
		// GZIP FOOTER - These be negative due to PHP's limitations 
		$datacrc=unpack("V",substr($data,-8,4));
		$datacrc=$datacrc[1];
		$isize=unpack("V",substr($data,-4));
		$isize=$isize[1];
	
		// Perform the decompression: 
		$bodylen=$len-$headerlen-8;
		if($bodylen<1){
			// This should never happen - IMPLEMENTATION BUG! 
			return null;
		}

		$body=substr($data,$headerlen,$bodylen);
		$data="";

		if($bodylen>0){
			switch($method){
				case 8:
					// Currently the only supported compression method: 
					$data=gzinflate($body);
					break;
				default:
					// Unknown compression method 
					return false;
			}
		}else{
			//...
		}
	
		if($isize!=strlen($data) || crc32($data)!=$datacrc){
			// Bad format!  Length or CRC doesn't match! 
			return false;
		}

		return $data;
	}

}
