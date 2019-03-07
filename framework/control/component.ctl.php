<?php

class ComponentCtl {
	

	//处理事件和消息的被动推送
	public function callback()
	{

		echo ComponentMod::component_callback();
		exit;
		
	}
	
	
	//处理授权后回调
	public function uricallbcak()
	{

		if ((!$expires_in = requestInt('expires_in')) || (!$authorization_code = requestString('auth_code')))
		{
			#var_export($_REQUEST);
			echo 'some params is miss';
			exit;
		}
		//accountMod::set_current_service_provider($sp_uid);
		//通过 authorization_code 获取 用户的公众号appid 和access_token
		$ret_query_auth   = ComponentMod::get_query_auth($authorization_code);
		$authorizer_appid = $ret_query_auth['authorization_info']['authorizer_appid'];
		if (!$sp_uid = requestInt('sp_uid'))
		{
			
			header('Content-Type: text/html; charset=utf-8');
			echo "测试授权成功,授权公众号/小程序app_id=" . $authorizer_appid;
			exit;
		}

		uct_use_app('domain');
		DomainMod::goto_its_top_domain($sp_uid);

		$ret_authorizer_info = ComponentMod::get_authorizer_info($authorizer_appid);
		Weixin::weixin_log('ret_authorizer_info  ===== ' . json_encode($ret_authorizer_info));
		$public_uid = ComponentMod::add_or_edit_weixin_public($ret_query_auth, $ret_authorizer_info, $sp_uid);

		$public = WeixinMod::get_weixin_public_by_uid($public_uid);
		$msg    = array(
			'title'   => '公众号/小程序授权成功 提醒',
			'content' => '尊敬的用户，公众号/小程序：' . $public['public_name'] . ' 已经授权成功。开始的你的大师之旅吧！',
			'sp_uid'  => $sp_uid,
		);
		uct_use_app('sp');
		SpMsgMod::add_sp_msg($msg);
		//同步菜单
		self::sync_menu($sp_uid, $public, $public_uid);
		//获取自动回复规则
		self::sync_autoreply($sp_uid, $public, $public_uid);

		//iframe跳回首页
		$ap_uid = requestInt('_ap_uid');
		$url = 'http://'.which_agent_provider($ap_uid).'/?_a=sp';

		echo '<script>
if(top!=self) top.location.href="'.$url.'";
else window.location.href="'.$url.'";
</script>';
		exit;
		redirectTo($url);
	}

	public function sync_autoreply($sp_uid, $public, $public_uid)
	{
		$data = weixin::weixin_get_current_autoreply_info(WeixinMod::get_weixin_access_token($public_uid));
		uct_use_app('job');
		$args = array('basic_arg' => array('sp_uid'        => $sp_uid,
		                                   'public_uid'    => $public_uid,
		                                   'job_uid'       => '',
		                                   'job_parent_id' => (isset($this->job_uid) ? $this->job_uid : '')),
		              'fun_args'  => array($data));
		$ret  = JobMod::add_job('SyncaAutoreplyJob', $args);
		$msg = array(
			'title'   => '自动回复规则 已经同步',
			'content' => '尊敬的用户，' . $public['public_name'] . '自动回复规则已经同步。',
			'sp_uid'  => $sp_uid,
		);
		SpMsgMod::add_sp_msg($msg);
	}

	//同步菜单
	public function sync_menu($sp_uid, $public, $public_uid)
	{


		//同步菜单
		if (!$public['has_verified'] && $public['public_type'] == 1)
		{
			$msg = array(
				'title'   => '自定义菜单 无法同步',
				'content' => '尊敬的用户，订阅号：' . $public['public_name'] . '未认证，自定义菜单未能同步，请到<a href="?_a=menu&_u=sp">请进行设置。',
				'sp_uid'  => $sp_uid,
			);
			SpMsgMod::add_sp_msg($msg);
		}
		else
		{

			uct_use_app('menu');
			$menu = MenuMod::get_weixin_public_menu_from_tencent($public_uid);
			if(!empty($menu['selfmenu_info'])) {
			$ret = MenuMod::set_weixin_public_menu($menu['selfmenu_info'], $public_uid);
			} else {
				$ret = false;
			}
			$msg = array(
				'title'   => '自定义菜单 ' . ($ret ? '同步成功' : '同步失败'),
				'content' => '尊敬的用户，公众号：' . $public['public_name'] . ' 自定义菜单 已经' . ($ret ? '同步成功' : '同步失败') . ',可到<a href="?_a=menu&_u=sp">进行设置。',
				'sp_uid'  => $sp_uid,
			);
			SpMsgMod::add_sp_msg($msg);

		}
	}


	/*
		微信向第三方平台推送公众号消息和事件
	*/
	public function message()
	{

		#$GLOBALS['arraydb_sys']['loggg_get'] = date('Y-m-d H:i:s  ---').var_export($_GET, true);
		#$GLOBALS['arraydb_sys']['loggg_input'] = file_get_contents('php://input');


		//1. 是否为微信验证调用
		Weixin::weixin_validate(WeixinMod::get_tencent_token());

		//2. 解析微信传入参数, 确定公众号、粉丝信息
		WeixinMod::prepare_weixin_session();
		
		//2.5 微信第三方平台全网发布调试
		self::full_web_debug();
		//3. 微信业务逻辑
		WeixinMod::process_weixin_msg();

	}
	
	/*
		第三方平台全网发布调试
	*/
	public function full_web_debug()
	{
		/*
			固定app_id 接收信息,3个处理事件
			even事件
			msg事件
			客服回复 
		*/
		if ((WeixinMod::get_weixin_xml_args('ToUserName') != 'gh_3c884a361561') && 
			(WeixinMod::get_weixin_xml_args('ToUserName') != 'gh_8dad206e9538'))
		{
			return;
		}
		if (!strcasecmp(WeixinMod::get_weixin_xml_args('MsgType'), 'event'))
		{
			Weixin::weixin_reply_txt(WeixinMod::get_weixin_xml_args('Event') . 'from_callback');
		}
		
		if (!strcasecmp(WeixinMod::get_weixin_xml_args('MsgType'), 'text'))
		{
			if (!strcasecmp(WeixinMod::get_weixin_xml_args('Content'), 'TESTCOMPONENT_MSG_TYPE_TEXT'))
			{
				Weixin::weixin_reply_txt('TESTCOMPONENT_MSG_TYPE_TEXT_callback');
			}
			if (($content = WeixinMod::get_weixin_xml_args('Content')) && (stripos($content, 'QUERY_AUTH_CODE') !== false))
			{
				$query_auth_code                                                    = explode(':', $content);
				$query_auth_code                                                    = $query_auth_code[1];
				$GLOBALS['arraydb_weixin_public']['QUERY_AUTH_CODE_FULL_WEB_DEBUG'] = $query_auth_code;
				$this->full_web_debug_send_custom_msg($query_auth_code, WeixinMod::get_weixin_xml_args('FromUserName'));
				Weixin::weixin_reply_txt('');
				#exit('');
			}
		}
	}
	
	protected function full_web_debug_send_custom_msg($query_auth_code, $touser)
	{
		//通过 authorization_code 获取 用户的公众号appid 和access_token
		$ret_query_auth   = ComponentMod::get_query_auth($query_auth_code);
		$authorizer_appid = $ret_query_auth['authorization_info']['authorizer_appid'];
		$ACCESS_TOKEN     = $ret_query_auth['authorization_info']['authorizer_access_token'];
		$data             = array('touser'  => $touser,
		                          'msgtype' => 'text',
		                          'text'    => array('content' => $query_auth_code . '_from_api'),
		);
		$url              = ('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $ACCESS_TOKEN);
		$ret              = Weixin::weixin_https_post($url, $data);
	}
}

