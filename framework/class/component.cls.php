<?php

class Component {
		
	/*
		获取第三方平台access_token
	*/	
	public static function get_component_token()
	{		
		if (isset($GLOBALS['arraydb_sys']['component_access_token'])) {
			return $GLOBALS['arraydb_sys']['component_access_token'];
		}
		
		/*
			主域名下直接去访问微信接口获取
			其他域名下从主域名获取
		*/
		uct_use_app('domain');
		if(DomainMod::is_master_top_domain()) {
			if(empty($GLOBALS['arraydb_sys']['component_verify_ticket'])) {
				Weixin::weixin_log('error! component_verify_ticket not set!!!');
				return false;
			}
		
			$data=array('component_appid'         =>COMPONENT_APPID,
						'component_appsecret'     =>COMPONENT_APPSECRET,
						'component_verify_ticket' => $GLOBALS['arraydb_sys']['component_verify_ticket']);
			$url=('https://api.weixin.qq.com/cgi-bin/component/api_component_token');
			$ret=Weixin::weixin_https_post($url,$data);
			if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['component_access_token'])) {
				return false;
			}

			//不能连写!
			$GLOBALS['arraydb_sys']['component_access_token'] = array('value'=>$ret['component_access_token'], 'expire' => 6400);
			return $ret['component_access_token'];
		}
		else {
			$ret = ComponentMod::uctphp_rpc_call('Component::get_component_token', array(), 'uctphp.com');		
			Weixin::weixin_log('dbg rpc_call Component::get_component_token return '.var_export($ret, true));
			if(!$ret) {
				return false;
			}

			//不能连写
			$GLOBALS['arraydb_sys']['component_access_token'] = array('value'=>$ret, 'expire' => 6400);
			return $ret;
		}
	}
	
	
	/*
		获取预授权码
	*/
	public static function get_create_preauthcode($component_access_token)
	{	
		$data=array('component_appid'=>COMPONENT_APPID);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['pre_auth_code'])) {
			//失败了刷新一下component_access_token
			unset($GLOBALS['arraydb_sys']['component_access_token']);	
			return false;
		}
		
		 return $ret['pre_auth_code'];
	}
	
	
	/*
		使用授权码换取公众号的授权信息
	*/
	public static function get_query_auth($component_access_token,$authorization_code)
	{	
		$data=array('component_appid'=>COMPONENT_APPID,
					'authorization_code'=>$authorization_code);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		Weixin::weixin_log('see see  '.$ret);
		
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['authorization_info'])) {
			//失败了刷新一下component_access_token
			unset($GLOBALS['arraydb_sys']['component_access_token']);	
			return false;
		}
		return $ret;
		
	}

	/*
		获取（刷新）授权公众号的令牌
	*/
	public static function authorizer_refresh_token($public_uid,$authorizer_appid,$component_access_token)
	{	
		$authorizer_refresh_token=$GLOBALS['arraydb_weixin_public']['authorizer_refresh_token_'.$public_uid];
		$data=array('component_appid'=>COMPONENT_APPID,
					'authorizer_appid'=>$authorizer_appid,
					'authorizer_refresh_token'=>$authorizer_refresh_token
					);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['authorizer_access_token'])) {
			//失败了刷新一下component_access_token
			unset($GLOBALS['arraydb_sys']['component_access_token']);	
			return false;
		}
		return $ret;
		
	}
	
	/*
		获取授权方的账户信息
	*/
	
	public static function get_authorizer_info($authorizer_appid,$component_access_token)
	{
		
		$data=array('component_appid'=>COMPONENT_APPID,
					'authorizer_appid'=>$authorizer_appid,
					);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		//var_dump($ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['authorizer_info'])) {
			return false;
		}
		return $ret;
	}
	
	/*
		获取授权方的选项设置信息
	*/

	public static function get_authorizer_option()
	{	
		/*
			"option_name": "option_name_value"
			option_name	option_value			选项值说明
			location_report(地理位置上报选项)	0	无上报
												1	进入会话时上报
												2	每5s上报
			voice_recognize（语音识别开关选项）	0	关闭语音识别
												1	开启语音识别
			customer_service（客服开关选项）	0	关闭多客服
												1	开启多客服
		*/
		$data=array('component_appid'=>COMPONENT_APPID,
					'authorizer_appid'=>$authorizer_appid,
					"option_name"=>$option_name
					);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['authorizer_appid'])) {
			return false;
		}
		
		$authorizer_appid=$ret['authorizer_appid'];
		$option_name=$ret['option_name'];
		$option_value=$ret['option_value'];
		
	}	
	
	/*
		设置授权方的选项信息
	*/
	
	public static function 	set_authorizer_option()
	{	
		/*
			"option_name": "option_name_value"
		*/
		$data=array('component_appid'=>COMPONENT_APPID,
					'authorizer_appid'=>$authorizer_appid,
					"option_name"=>$option_name,
					"option_value"=>$option_value
					);
		$url=('https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token='.$component_access_token);
		$ret=Weixin::weixin_https_post($url,$data);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['errcode'])||$ret['errcode']!=0) {
			return false;
		}
		else
		{
			return true;
		}
	}	

	/*
		接受消息和事件推送
		接受推送component_verify_ticket协议
		接受微信服务器推送取消授权通知
	*/
	public static function component_callback()
	{
		$post = file_get_contents('php://input');
		if(empty($post))
			return false;
		// 解密取得信息
		include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
		$pc = new Prpcrypt(COMPONENT_KEY);//$this->encodingAesKey
		$xmlparse = new XMLParse;
		$array = $xmlparse->extractcomponent($post);
		$ret = $array[0];
		if ($ret != 0) {
			return false;
		}
		$encrypt = $array[1];
		$appid = $array[2];
		$result = $pc->decrypt($encrypt, COMPONENT_APPID);
		if ($result[0] != 0) {
			return false;
		}
		$post =$result[1];
		libxml_disable_entity_loader();
		$post =array_map(function($v){ return is_object($v) ? (array)$v : $v;},
								(array)(simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA)));
		return $post;
	}








	


}


