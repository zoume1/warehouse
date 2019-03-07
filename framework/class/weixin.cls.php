<?php

//todo 支持 php5.3
if(!defined('JSON_UNESCAPED_UNICODE')) define('JSON_UNESCAPED_UNICODE', 0);

class Weixin {
	/*
		记录微信日志 写到weixin_log中
		
		此函数记录的日志不会被rollback
	*/
	public static function weixin_log($str)
	{
		if (!$str)
		{
			return;
		}
		/*
		echo $str.PHP_EOL;
		return;
		*/

		if(0 && !empty($_GET['_d'])) {
			$info = debug_backtrace();	
			echo '[[<h3>'.substr($info[0]['file'], strlen(UCT_PATH)).' +'.$info[0]['line'].'</h3>]]';
			echo date('[Y-m-d H:i:s] ').$str.PHP_EOL;
			return;
		}

		//防止日志被 rollback
		if(1 && Dba::inTransaction()) {
			if(empty($GLOBALS['_TMP']['norollbacklog'])) {
				register_shutdown_function(function(){
					Dba::insertS('weixin_log', $GLOBALS['_TMP']['norollbacklog']);	
				});
			}
			$GLOBALS['_TMP']['norollbacklog'][] = array('create_time' => time(), 'log' => 'saved log ->'.$str);
			return;
		}

		Dba::insert('weixin_log', array('create_time' => time(), 'log' => $str));

		return;

		$file = '/var/log/weixin.log';
		$f    = fopen($file, 'a');
		$str  = Date('[Y-m-d H:i:s] ') . $str . "\n";
		fwrite($f, $str);
		fclose($f);
	}

	/*
		微信验证
		$token 待验证公众号的token值
	*/
	public static function weixin_validate($token)
	{

		if (isset($_GET['signature']) &&
			isset($_GET['timestamp']) &&
			isset($_GET['nonce'])
		)
		{
			$tmpArr = array($token, $_GET['timestamp'], $_GET['nonce']);
			sort($tmpArr, SORT_STRING);
			if ($_GET['signature'] !== sha1(implode($tmpArr)))
			{
				echo 'check weixin signature fail!';
				exit(1);
			}
			else
			{
				if (isset($_GET['echostr']))
				{
					echo $_GET['echostr'];
					exit(1);
				}
			}
		}
		else
		{
			echo 'weixin validate fail!';
			exit(1);
		}
	}

	/*
		获取微信参数保存到$GLOBALS['weixin_args']

		一个微信文字消息数据格式如下
		更多消息请查看微信官方文档 http://mp.weixin.qq.com/wiki/index.php

 <xml>
 <ToUserName><![CDATA[toUser]]></ToUserName>
 <FromUserName><![CDATA[fromUser]]></FromUserName>
 <CreateTime>1348831860</CreateTime>
 <MsgType><![CDATA[text]]></MsgType>
 <Content><![CDATA[this is a test]]></Content>
 <MsgId>1234567890123456</MsgId>
 </xml>
*/
	public static function weixin_parse_input()
	{
		if(!empty($GLOBALS['weixin_args'])) return;
		try
		{
			$post = file_get_contents('php://input');

			//自动判断是否为加密模式
			if (!empty($_GET['msg_signature']) &&
				!empty($_GET['timestamp']) &&
				!empty($_GET['nonce'])
			)
			{
				include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
				$token = WeixinMod::get_tencent_token();
				$wx    = WeixinMod::get_current_weixin_public();

				$app_id   = $wx['app_secret'] == '0' ? COMPONENT_APPID : $wx['app_id'];
				$pc       = new WXBizMsgCrypt($token, $wx['app_secret'] == '0' ?  COMPONENT_KEY: $wx['aes_key'], $app_id);
				$post_dec = '';
				if (0 != ($code = $pc->decryptMsg($_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $post, $post_dec)))
				{
					//echo PHP_EOL.'----ddd----'."${_GET['msg_signature']}, ${_GET['timestamp']}, ${_GET['nonce']}".PHP_EOL.$post.PHP_EOL;
					self::weixin_log('ERROR in weixin_parse_input! seems the weixin svr send incorrect encrypt data! ' . $code);
					exit(1);
				}

				$post = $post_dec;
			}

			self::weixin_log($post);
			libxml_disable_entity_loader();
			$GLOBALS['weixin_args'] = @ array_map(function ($v)
			{
				return is_object($v) ? (array)$v : $v;
			},
				(array)(simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA)));


		} catch (Exception $e)
		{
			self::weixin_log('ERROR in weixin_parse_input! seems the weixin svr send incorrect data!');
			exit(1);
		}
	}

	public static function weixin_reply($xml)
	{
		self::weixin_log('weixin_reply:'.$xml);
		//自动判断是否为加密模式
		if (!empty($_GET['msg_signature']) &&
			!empty($_GET['timestamp']) &&
			!empty($_GET['nonce'])
		)
		{
			include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
			$token = WeixinMod::get_tencent_token();
			$wx    = WeixinMod::get_current_weixin_public();
			//$pc = new WXBizMsgCrypt($token, $wx['aes_key'], $wx['app_id']);
			$app_id  = $wx['app_secret'] == '0' ? COMPONENT_APPID : $wx['app_id'];
			$pc      = new WXBizMsgCrypt($token, $wx['aes_key'], $app_id);
			$xml_enc = '';
			if (0 != ($code = $pc->encryptMsg($xml, $_GET['timestamp'], $_GET['nonce'], $xml_enc)))
			{
				self::weixin_log('ERROR in weixin_reply! encrypt data fail! ' . $code);
				exit(1);
			}
			$xml = $xml_enc;
		}

		header('Content-Type:text/xml; charset=utf-8');
		echo $xml;

		exit();
		self::weixin_log('exit!!!!');
	}

	//回复微信文字消息
	public static function weixin_reply_txt($txt)
	{
		uct_use_app('su');
		//如果是服务号或者 开启了代理网页oauth2授权登录, 则不自动加weixin_session_id
		if(((WeixinMod::get_current_weixin_public('public_type') & 1) == 1) && 
			!SuWxLoginMod::is_proxy_wxlogin_available()) {
			$txt = WeixinMod::try_replace_to_weixin_session_id_url($txt);
		}
		
		//小程序客服消息回复
		if((WeixinMod::get_current_weixin_public('public_type') & 8) == 8) {
				$data = array(
					'touser'  =>  $GLOBALS['weixin_args']['FromUserName'],
					'msgtype' => 'text',
					'text' 	  => array(
						'content' =>  $txt,
					)
				);
			
			$access_token = WeixinMod::get_weixin_access_token();
			self::weixin_send_custom_msg($data, $access_token);
			exit();
		}

		$xml = '<xml>
<ToUserName><![CDATA[' . $GLOBALS['weixin_args']['FromUserName'] . ']]></ToUserName>
<FromUserName><![CDATA[' . $GLOBALS['weixin_args']['ToUserName'] . ']]></FromUserName>
<CreateTime>' . $_SERVER['REQUEST_TIME'] . '</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[' . $txt . ']]></Content>
</xml>';

		self::weixin_reply($xml);
	}

	/*
		todo 如果是本地链接图文消息自动加上WEIXINSESSION参数以实现自动登陆

		回复图文消息
			articles 文章列表 array(
				array(
					'Title'=>'',        //标题
					'Description'=>'',  //描述

					//支持JPG、PNG格式，较好的效果为大图360*200，小图200*200,默认第一个item为大图
					'PicUrl'=>'',
					'Url'=>'',          //链接地址
				),
				...
			);
	*/
	public static function weixin_reply_news($articles)
	{
		//最多8条图文
		$articles = array_slice($articles, 0, 8);
		$xml = '<xml>
<ToUserName><![CDATA[' . $GLOBALS['weixin_args']['FromUserName'] . ']]></ToUserName>
<FromUserName><![CDATA[' . $GLOBALS['weixin_args']['ToUserName'] . ']]></FromUserName>
<CreateTime>' . $_SERVER['REQUEST_TIME'] . '</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>' . count($articles) . '</ArticleCount>
<Articles>';

		//如果是服务号或者 开启了代理网页oauth2授权登录, 则不自动加weixin_session_id
		uct_use_app('su');
		if(((WeixinMod::get_current_weixin_public('public_type') & 1) != 1) || 
			SuWxLoginMod::is_proxy_wxlogin_available()) {
			$try_auto_login = false;
		}		
		else {
			$try_auto_login = true;
		}

		//小程序客服消息回复
		if((WeixinMod::get_current_weixin_public('public_type') & 8) == 8) {
				$data = array(
					'touser'  =>  $GLOBALS['weixin_args']['FromUserName'],
					'msgtype' => 'link',
					'link' 	  => array(
						'title' =>  $articles[0]['Title'],
						'description' =>  $articles[0]['Description'],
						'thumb_url' =>  $articles[0]['PicUrl'],
						'url' =>  $articles[0]['Url'],
					)
				);
			
			$access_token = WeixinMod::get_weixin_access_token();
			self::weixin_send_custom_msg($data, $access_token);
			exit();
		}

		foreach ($articles as $a)
		{
			//测试发现PicUrl需要编码但Url不需要
			#$a['PicUrl'] = htmlspecialchars($a['PicUrl']);
			//todo 关闭
//			if (false && $a['Url'])
			if ($try_auto_login && $a['Url'])
			{
				$a['Url'] = WeixinMod::try_add_weixin_session_id_to_url($a['Url']);
			}

			$xml .= '<item>
<Title><![CDATA[' . $a['Title'] . ']]></Title>
<Description><![CDATA[' . $a['Description'] . ']]></Description>
<PicUrl><![CDATA[' . $a['PicUrl'] . ']]></PicUrl>
<Url><![CDATA[' . $a['Url'] . ']]></Url>
</item>
';
		}
		$xml .= '</Articles>
</xml> ';

		return self::weixin_reply($xml);
	}



	public static function weixin_reply_image($media_id)
	{
		$xml = '<xml>
<ToUserName><![CDATA[' . $GLOBALS['weixin_args']['FromUserName'] . ']]></ToUserName>
<FromUserName><![CDATA[' . $GLOBALS['weixin_args']['ToUserName'] . ']]></FromUserName>
<CreateTime>' . $_SERVER['REQUEST_TIME'] . '</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA['.$media_id.']]></MediaId>
</Image>
</xml>';

		self::weixin_reply($xml);
	}

	/*
		转发小程序客服消息
	*/
	public static function weixin_reply_transfer_kefu()
	{
		$xml = '<xml>
<ToUserName><![CDATA[' . $GLOBALS['weixin_args']['FromUserName'] . ']]></ToUserName>
<FromUserName><![CDATA[' . $GLOBALS['weixin_args']['ToUserName'] . ']]></FromUserName>
<CreateTime>' . $_SERVER['REQUEST_TIME'] . '</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>';

		self::weixin_reply($xml);
	}



	/*
		调用微信服务器接口 https get
	*/
	public static function weixin_https_get($url)
	{
		#Weixin::weixin_log('weixin get '.$url);
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		$ret = curl_exec($c);
		curl_close($c);

		return $ret;
	}

	/*
		调用微信服务器接口 https post
	*/
	public static function weixin_https_post($url, $data)
	{

		if (is_array($data))
		{
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		}
		Weixin::weixin_log('weixin post ' . $url . ' ===== ' . $data);
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($c);
		curl_close($c);

		return $ret;
	}

	/*
		上传接口
	*/
	public static function weixin_upload_file($url, $data)
	{
		if (isset($data['description']))
		{
			$data['description'] = json_encode($data['description'], JSON_UNESCAPED_UNICODE);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}


	/*
		下载文件接口
	*/

	public static function weixin_download_file($url, $data = '')
	{
		$ch = curl_init();
		// $timeout=3;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, "http://mp.weixin.qq.com/");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		// curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);

		if (!empty($data))
		{
			$data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$ret = curl_exec($ch);

		$rinfo = curl_getinfo($ch);
		//获取相应头长度 分割header 和body
		if ($rinfo["http_code"] == '200')
		{
			$headerSize                     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header                         = substr($ret, 0, $headerSize);
			$body                           = substr($ret, $headerSize);
			$ret                            = null;
			$ret['header']                  = $header;
			$ret["content_type"]            = $rinfo["content_type"];
			$ret["http_code"]               = $rinfo["http_code"];
			$ret["download_content_length"] = $rinfo["download_content_length"];
			$ret['body']                    = $body;
			if ($filename = self::get_filename($header))
			{
				$ret['name'] = $filename;
			}

		}
		curl_close($ch);

		return $ret;
	}


	/*
		获取相应头中文件名
	*/
	public static function  get_filename($header)
	{

		if (preg_match('/filename="[^"]+"/', $header, $filename))
		{
			$filename = strtr($filename[0], array('filename="' => '', '"' => ''));

			return $filename;
		}
		else
		{
			return false;
		}


	}

	/*
		获取 access token

		{"access_token":"ACCESS_TOKEN","expires_in":7200}
	*/
	public static function weixin_get_access_token($appid, $secret)
	{
		if (isset($GLOBALS['weixin_args']['access_token_'.$appid]))
		{
			return $GLOBALS['weixin_args']['access_token_'.$appid];
		}

		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;

		$ret = self::weixin_https_get($url);
		self::weixin_log('['.$appid.'] get access token : ' . $ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['access_token']))
		{
			return false;
		}

		return $GLOBALS['weixin_args']['access_token_'.$appid] = $ret['access_token'];
	}

	/*
		获取自定义菜单

		array (
  			'errcode' => 46003,
		  'errmsg' => 'menu no exist',
		)
	*/
	public static function weixin_get_menu($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		self::weixin_log('get menu : ' . $ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	/*
		获取自定义菜单
		可以取得通过api设置的数据和公众号后台设置的数据
		文档地址 http://mp.weixin.qq.com/wiki/17/4dc4b0514fdad7a5fbbd477aa9aab5ed.html
	*/
	public static function weixin_get_self_menu($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		self::weixin_log('get self menu : ' . $ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	public static function weixin_delete_menu($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		self::weixin_log('delete menu : ' . $ret);

		return ($ret && ($ret = json_decode($ret, true)) && isset($ret['errcode'])
			&& $ret['errcode'] == 0);
	}

	public static function weixin_url_encode($var)
	{
		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				$var[$k] = self::weixin_url_encode($v);
			}

			return $var;
		}
		else
		{
			return urlencode($var);
		}
	}

	/*
		创建自定义菜单
	*/
	public static function weixin_create_menu($menu, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
		#$data = json_encode($menu);
		$data = urldecode(json_encode(self::weixin_url_encode($menu)));
		$ret  = self::weixin_https_post($url, $data);
		self::weixin_log('create menu : ' . $ret);
		// return ($ret && ($ret = json_decode($ret, true)) && isset($ret['errcode'])
		// && $ret['errcode'] == 0);
		return $ret;

	}

	/*
		获取jsapi_ticket

		{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
			"expires_in":7200
		}
	*/
	public static function weixin_get_jsapi_ticket($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $access_token . '&type=jsapi';
		$ret = self::weixin_https_get($url);
		self::weixin_log('get jsapi_ticket: ' . $ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['ticket']))
		{
			return false;
		}

		return $ret['ticket'];
	}

	/*
		获取临时二维码

		{	"ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==",
			"expire_seconds":60,
			"url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"
		}
	*/
	public static function weixin_get_temp_qrcode($scene_id, $access_token)
	{
		$url  = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
		$data = '{"expire_seconds":2592000,"action_name":"QR_SCENE","action_info":{"scene":{"scene_id":' . $scene_id . '}}}';
		$ret  = self::weixin_https_post($url, $data);
		self::weixin_log('get temp_qrcode: ' . $ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['ticket']))
		{
			return false;
		}

		return $ret;
	}

	/*
		获取用户信息
		{
		"subscribe": 1,  //当未关注时，可能没有下面的字段
		"openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
		"nickname": "Band",
		"sex": 1,
		"language": "zh_CN",
		"city": "广州",
		"province": "广东",
		"country": "中国",
		"headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
		"subscribe_time": 1382694957,
		"unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
		"remark": "",
		"groupid": 0
}

	*/
	public static function weixin_get_user_info($open_id, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $open_id . '&lang=zh_CN';
		$ret = self::weixin_https_get($url);
		//self::weixin_log('get weixin user info: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['openid']))
		{
			return false;
		}

		return $ret;
	}

    public static function weixin_get_user_info_batchget($data,$access_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=' . $access_token ;
        $ret = self::weixin_https_post($url,$data);
        //self::weixin_log('get weixin user info: '.$ret);
        if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['user_info_list']))
        {
            return false;
        }

        return $ret;
	}
	
	/*

	*/
	public static function array_to_weixin_xml($arr, $tag = 'xml')
	{
		$xml = "<$tag>";
		foreach ($arr as $k => $v)
		{
			if (($k == 'CreateTime'))
			{
				$xml .= "<$k>$v</$k>";
			}
			else
			{
				if (is_array($v))
				{
					$xml .= Weixin::array_to_weixin_xml($v, $k);
				}
				else
				{
					$xml .= "<$k><![CDATA[$v]]></$k>";
				}
			}
		}
		$xml .= "</$tag>";

		return $xml;
	}

	/*
	 * 创建分组   $data = "{"group":{"name":"test"}}";  分组名字，UTF8编码 分组名字（30个字符以内）
	 */
	public static function weixin_create_group($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['group']))
		{
			return false;
		}

		return $ret;
	}


	/*
	 * 获取所有分组
	 */
	public static function weixin_get_all_group($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['groups']))
		{
			return false;
		}

		return $ret;
	}


	/*
	 * 通过用户的OpenID查询其所在的GroupID
	 * POST数据例子：{"openid":"od8XIjsmk6QdVTETa9jLtGWA6KBc"}
	 */
	public static function weixin_get_groudid_by_openid($open_id, $access_token)
	{
		$url  = 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=' . $access_token;
		$data = array('openid' => $open_id);
		$ret  = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['groupid']))
		{
			return false;
		}

		return $ret;
	}


	/*
	 * 修改分组名
	 * POST数据例子：{"group":{"id":108,"name":"test2_modify2"}}
	 */
	public static function weixin_update_group($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}


	/*
	 * 移动用户分组
	 * POST数据例子：{"openid":"oDF3iYx0ro3_7jD4HFRDfrjdCM58","to_groupid":108}
	 */
	public static function weixin_update_user_groupid($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	/*
	 * 批量移动用户分组
	 * POST数据例子：{"openid_list":["oDF3iYx0ro3_7jD4HFRDfrjdCM58","oDF3iY9FGSSRHom3B-0w5j4jlEyY"],"to_groupid":108}
	 */
	public static function weixin_batch_update_user_groupid($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	/*
	* 批量移动用户分组
	* POST数据例子：{"group":{"id":108}}
	*/
	public static function weixin_delete_group($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	/*
	* 批量移动用户分组
	* POST数据例子：{
					"openid":"oDF3iY9ffA-hqb2vVvbr7qxf6A0Q",
					"remark":"pangzi"  //   remark 	新的备注名，长度必须小于30字符
					}
	*/
	public static function weixin_update_user_remark($data, $access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	/*
	 * 获取用户列表 http://mp.weixin.qq.com/wiki/0/d0e07720fc711c02a3eab6ec33054804.html
	 *
	 * 当公众号关注者数量超过10000时，可通过填写next_openid的值。
	 */
	public static function weixin_get_user_list($NEXT_OPENID='',$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token.'&next_openid='.$NEXT_OPENID;
		$ret = self::weixin_https_get($url);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['data']))
		{
			return false;
		}

		return $ret;
	}

	/*
	 * 设置行业可在MP中完成，每月可修改行业1次
	 * $data = {
          "industry_id1":"1",
          "industry_id2":"4"
       }
	 */
	public static function weixin_set_industry($data ,$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
//		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
//		{
//			return false;
//		}

		return $ret;
	}

	/*
	 *通过模板编号获取微信模板
	 * $data = {
           "template_id_short":"TM00015"
       }
	 */
	public static function weixin_get_template_id($data ,$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=' . $access_token;
		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
//		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
//		{
//			return false;
//		}

		return $ret;
	}


	/*
	 * 发送模板消息
	 */
	public static function weixin_send_template_msg($data,$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token;

		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
//		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
//		{
//			return false;
//		}

		return $ret;
	}

	/*
	 * 获取模板列表
	 */

	public static function weixin_get_all_private_template($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			$GLOBALS['_TMP']['WEIXIN_ERROR'] = empty($ret['errcode']) ? 'false' : $ret['errcode'];
			setLastError(ERROR_IN_WEIXIN_API);
		}
		return $ret;
	}

	/*
	 * 发送小程序模板消息
	 */
	public static function xiaochengxu_send_template_msg($data,$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access_token;

		$ret = self::weixin_https_post($url, $data);
		#self::weixin_log('xiaochengxu_send_template_msg: '.$ret);
//		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
//		{
//			return false;
//		}

		return $ret;
	}

	/*
	 * 获取小程序模板列表
	 */

	public static function xiaochengxu_get_all_private_template($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=' . $access_token;
		$ret = self::weixin_https_post($url,array('offset' => 0, 'count' => 20));
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			$GLOBALS['_TMP']['WEIXIN_ERROR'] = empty($ret['errcode']) ? 'false' : $ret['errcode'];
			setLastError(ERROR_IN_WEIXIN_API);
		}
		return $ret;
	}
	
	/*
	 * 获取自动回复规则
	 */
	public static function weixin_get_current_autoreply_info($access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token=' . $access_token;
		$ret = self::weixin_https_get($url);
		//self::weixin_log('weixin_create_group: '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		{
			return false;
		}

		return $ret;
	}

	public static function weixin_send_custom_msg($data,$access_token)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;

		$ret = self::weixin_https_post($url, $data);
		//self::weixin_log('weixin_create_group: '.$ret);
		//		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
		//		{
		//			return false;
		//		}

		return $ret;
	}
}


