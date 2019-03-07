<?php

class WeixinMod {
	public static function func_get_weixin($item) {
		$item['has_verified'] = ($item['public_type'] >> 4) > 0;
		$item['public_type'] = $item['public_type'] & 0xf;
		if(!empty($item['weixin_brief'])) $item['weixin_brief'] = htmlspecialchars($item['weixin_brief']);

		return $item;
	}

	/*
		获取当前公众号token  用于填写到腾讯公众号后台的token	
	*/
	public static function get_tencent_token() {
		
		$wx = self::get_current_weixin_public();
		//授权模式下用统一的token
		$token=($wx['app_secret'] == '0' ? COMPONENT_TOKEN : md5($wx['uct_token'].$wx['uid']));
		
		//app_secret 为0 则是第三方公众号平台授权账号，直接传回uctoo_token 
		// return md5($wx['uct_token'] . $wx['uid']);
		return $token;
	}

	/*
		获取当前公众号URL地址 用于填写到腾讯公众号后台的URL
	*/
	public static function get_tencent_callback_url() {
		$wx = self::get_current_weixin_public();
		return (isHttps()?'https://':'http://').getDomainName().'/?_u=weixin.tencent_callback&uct_token='.$wx['uct_token'];
	}

	public static function get_weixin_public_by_uid($uid) {
		if(isset($GLOBALS['weixin_public']) && ($uid == $GLOBALS['weixin_public']['uid'])) {
			return $GLOBALS['weixin_public'];
		}
		return Dba::readRowAssoc('select * from weixin_public where uid = '.$uid, 'WeixinMod::func_get_weixin');
	}

	/*
		获取服务商所有公众号列表,不分页
		$_d 默认只取有权限的公众号(针对子账号)
	*/
	public static function get_all_weixin_public_by_sp_uid($sp_uid = 0, $_d = true) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_INVALID_REQUEST_PARAM);
			return false;
		}

		$sql = 'select * from weixin_public where sp_uid = '.$sp_uid;
		//子账号
		if($_d && ($subsp_uid = AccountMod::has_subsp_login())) {
			uct_use_app('subsp');
			if($subsp = SubspMod::get_subsp_by_uid($subsp_uid)) {
				$sql = 'select * from weixin_public where sp_uid = '.$subsp['sp_uid'];
				if(!empty($subsp['uct_tokens'])) {
					$sql .= '&& uct_token in '.Dba::makeIn($subsp['uct_tokens']);
				}
			}
		}

		return Dba::readAllAssoc($sql, 'WeixinMod::func_get_weixin');
	}

	/*
		获取所有公众号列表,不分页
	*/
	public static function get_weixin_public_list($option) {
		$sql = 'select * from weixin_public';
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'sp_uid ='.$option['sp_uid'];
		}
		if(!empty($option['key'])) {
			$where_arr[] = '(public_name like "%'.addslashes($option['key']).'%") || (origin_id like "%'.addslashes($option['key']).'%")';
		}
		if(!empty($option['public_type'])) {
			$where_arr[] = 'public_type ='.$option['public_type'];
		}

		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' && ', $where_arr);
		}
		$sql .= ' order by uid desc';

		if(!empty($option['pagination'])) {
			return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'WeixinMod::func_get_weixin');
		}

		return Dba::readCountAndLimit($sql, 0, -1, 'WeixinMod::func_get_weixin');
	}

	/*
		为消息里的链接增加session_id		
	*/
	public static function try_replace_to_weixin_session_id_url($txt) {
		return preg_replace_callback('#(https?://[^<\'"\s]{3,455})#', function($match){
			return WeixinMod::try_add_weixin_session_id_to_url($match[0]);
		},$txt);
	}

	/*
		增加一个session id
		如果是本地链接图文消息自动加上WEIXINSESSION参数
	*/
	public static function try_add_weixin_session_id_to_url($url) {
		if(($mix = parse_url($url)) &&
			!empty($mix['host']) && 
			//($mix['host'] == getDomainName()) 
			(false !== strpos($mix['host'], 'uctphp.')) // 
			) {
			$url .= (strpos($url, '?') ? '&' : '?').'WEIXINSESSIONID='.WeixinMod::get_a_weixin_session_id();
		}

		return $url;
	}

	/*
		生成一个微信sessionid， 里面包含了当前公众号和粉丝信息

		可以在一个链接里带上WEIXINSESSIONID参数
		从这个链接点击进入可以通过 self::start_weixin_session达到自动登陆效果

		值与PHPSESSIONID相同, 默认到期时间也相同	
	*/
	public static function get_a_weixin_session_id($expired = -1) {
		$ukey = self::get_current_weixin_fan('open_id');
		if(!$ukey) $ukey = session_id();//md5(uniqid());

		if(empty($GLOBALS['arraydb_weixin_session'][$ukey])) {
			$data = array('uct_token' => self::get_current_weixin_public('uct_token'),
						'open_id' => self::get_current_weixin_fan('open_id'),
				);
						
			if($expired == -1) {
				$expired =  session_get_cookie_params();
				$expired = $expired['lifetime'];
			}
			if($expired <= 0) {
				$expired = 86400 * 30 -1;
			}
			$GLOBALS['arraydb_weixin_session'][$ukey] = array('expire' => $expired, 'value' => json_encode($data));
		}
		return $ukey;
	}


	/*
		通过浏览器访问时根据WEIXINSESSIONID自动登陆
	*/
	public static function start_weixin_session() {
		if(empty($_GET['WEIXINSESSIONID']) || !preg_match('/^[\w\-_]{6,64}$/', $_GET['WEIXINSESSIONID']) ||
			empty($GLOBALS['arraydb_weixin_session'][$_GET['WEIXINSESSIONID']])) {
			return false;
		}

		if($data = json_decode($GLOBALS['arraydb_weixin_session'][$_GET['WEIXINSESSIONID']], true)) {
			self::set_current_weixin_public($data['uct_token']);		
			self::set_current_weixin_fan($data['open_id']);		
			
			$_SESSION['uct_token'] = $data['uct_token'];
			$_SESSION['open_id'] = $data['open_id'];
			$_SESSION['sp_uid'] = self::get_current_weixin_public('sp_uid');
			$_SESSION['su_login'] = $_SESSION['su_uid'] = self::get_current_weixin_fan('su_uid');
            
			self::on_init_weixin_session();
		}
		//302 一下，去掉url里的WEIXINSESSIONID信息
		unset($_GET['WEIXINSESSIONID']);
		unset($_GET['uct_token']);
		redirectTo('/?'.http_build_query($_GET));
	}

	/*
		设置当前公众号信息，可以直接设置一个数组，
		或者设置uct_token
	*/
	public static function set_current_weixin_public($wx) {
		if (is_array($wx)) {
			$GLOBALS['weixin_public'] = $wx;	
		}
		else {
			$_GET['uct_token'] = $wx;
		}
	}

	/*
		根据回调地址获取当前公众号微信token,失败退出
		#token of '桐路网小助手'
		#$token = '444b9871d32edfee3f9441bb7c9baaef';
	*/
	public static function get_current_weixin_public($field = '', $bexit = true) {
		
		if(!isset($GLOBALS['weixin_public'])) {
			
			//取到app_id 时为第三方公众平台的回调信息，通过app_id 取公众号信息
			if(!empty($_REQUEST['_app_id']) && preg_match('/^[\w]{3,64}$/', $_REQUEST['_app_id'])) {
				$_GET['uct_token'] = Dba::readOne('select uct_token from weixin_public where app_id = "'.$_REQUEST['_app_id'].'"');
				if(!$_GET['uct_token'] && uct_is_weixin_page()) {
					//todo  可能是第一次自动授权过来, 也可能是其他域名下的app_id, 要有个which_domain_is_this_app_id()方法
					uct_use_app('domain');
					if(DomainMod::is_master_top_domain() && !AccountMod::has_sp_login()) {
						$d = 'uctphp.cn';	
						//$ret = ComponentMod::debug_send_msg($d);
						$url = (isHttps() ? 'https://' : 'http://').$d.'?'.http_build_query($_GET);
						redirectTo($url);
						exit();
					}
					
				}
			}
			
			if(empty($_GET['uct_token']) && !empty($_SESSION['uct_token'])) {
				$_GET['uct_token'] = $_SESSION['uct_token'];
			}

			if(empty($_GET['uct_token']) && uct_is_backend_page()) {
				if($subsp_uid = AccountMod::has_subsp_login()) {
					$_GET['uct_token'] = Dba::readOne('select uct_token from sub_sp where uid = '.$subsp_uid);
				}
				else if($sp_uid = AccountMod::has_sp_login()) {
					$_GET['uct_token'] = Dba::readOne('select uct_token from service_provider where uid = '.$sp_uid);
				}
			}
			
			if(empty($_GET['uct_token']) && !uct_is_backend_page()) {
				if($sp_uid = AccountMod::require_sp_uid(false)) {
					$_GET['uct_token'] = Dba::readOne('select uct_token from service_provider where uid = '.$sp_uid);
				}
			}

			//无效的uct_token试着更新一下
			if((empty($_GET['uct_token']) 
				|| !preg_match('/^[\w]{3,64}$/', $_GET['uct_token'])
				|| !($GLOBALS['weixin_public'] = Dba::readRowAssoc(
					'select * from weixin_public where uct_token = "'.($_GET['uct_token']).'"', 'WeixinMod::func_get_weixin'))
				) && uct_is_backend_page()) {

				if($subsp_uid = AccountMod::has_subsp_login()) {
					uct_use_app('subsp');
					if($subsp = SubspMod::get_subsp_by_uid($subsp_uid)) {
						$sql = 'select * from weixin_public where sp_uid = '.$subsp['sp_uid'];
						if(!empty($subsp['uct_tokens'])) {
							$sql .= '&& uct_token in '.Dba::makeIn($subsp['uct_tokens']);
						}
						$GLOBALS['weixin_public'] = Dba::readRowAssoc($sql);
                		$update = array('uct_token' => !empty($GLOBALS['weixin_public']['uct_token']) ? 
																$GLOBALS['weixin_public']['uct_token'] : '');
		                Dba::update('sub_sp', $update, 'uid = '.$subsp_uid);
					}
				}
				else if($sp_uid = AccountMod::has_sp_login()) {
					$GLOBALS['weixin_public'] = Dba::readRowAssoc('select * from weixin_public where sp_uid = '.$sp_uid);
                	$update = array('uct_token' => !empty($GLOBALS['weixin_public']['uct_token']) ? 
															$GLOBALS['weixin_public']['uct_token'] : '');
					Dba::update('service_provider', $update, 'uid = '.$sp_uid);
				}
			}
				
			if(empty($GLOBALS['weixin_public']['uct_token'])) {
				if($bexit) {
					echo 'invalid uct token! '.(isset($_GET['uct_token']) ? $_GET['uct_token'] : ''); exit(1);
				}
				else {
					return false;
				}
			}
		}

		return $field ? (isset($GLOBALS['weixin_public'][$field]) ? $GLOBALS['weixin_public'][$field] : false) 
						: $GLOBALS['weixin_public']; 
	}

	/*
		获取微信服务器xml参数, 在Weixin::weixin_parse_input后调用
	*/
	public static function get_weixin_xml_args($field = '') {
		if(empty($GLOBALS['weixin_args'])) {
			return false;
		}
		return $field ? (isset($GLOBALS['weixin_args'][$field]) ? $GLOBALS['weixin_args'][$field] : false) 
						: $GLOBALS['weixin_args']; 
	}

	/*
		新增一个粉丝	
		此时可以做些初始化工作

		$fan = array('public_uid' => 1, 
					 'open_id' => 'oz-Rft9o-43mxnwO7NfteG-DVHas')
		
		返回 fan_uid
	*/
	public static function add_a_weixin_fan($fan) {
		if($ui = WeixinMod::get_weixin_user_info($fan['public_uid'], $fan['open_id'])) {
			$update = array();	
			if(!empty($ui['nickname'])) {
				#$update['name'] = checkString($ui['nickname'], PATTERN_USER_NAME);
				//todo mb4
				$update['name'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $ui['nickname']);
			}
			if(!empty($ui['sex'])) $update['gender'] = checkInt($ui['sex']);
			if(!empty($ui['headimgurl'])) $update['avatar'] = checkString($ui['headimgurl'], PATTERN_URL);
			if(!empty($ui['unionid'])) $update['union_id'] = checkString($ui['unionid'], PATTERN_NORMAL_STRING);
		}
		else {
			$retry = true;
		}

		$f_table = 'weixin_fans';
		if((self::get_current_weixin_public('public_type')&8) == 8) {
			$f_table = 'weixin_fans_xiaochengxu';
		}
		$fan_id = Dba::readOne('select uid from '.$f_table.' where public_uid = '.$fan['public_uid'].						' && open_id = "'.$fan['open_id'].'"');
		Dba::beginTransaction(); {
			if(!$fan_id) {
				Dba::insert($f_table, $fan);
				$fan_id = Dba::insertID();
			}

			//添加一个对应的普通用户 (也可能是绑定一个现有的普通用户?)
			//todo 如果是认证服务号,可以自动获取粉丝资料
			$su = array('name' => '微信粉丝'.$fan_id, 'sp_uid' => AccountMod::get_current_service_provider('uid'));
			if(!empty($update)) {
				$su = array_merge($su, $update);
			}
			$su_uid = AccountMod::add_or_edit_service_user($su);
			$GLOBALS['weixin_fan']['su_uid'] = $su_uid;
			$sql = 'update '.$f_table.' set su_uid = '.$su_uid;
			if(!empty($ui['subscribe'])) {
				$sql .= ', has_subscribed = 1';
			}
			$sql .= ' where uid = '.$fan_id;
			Dba::write($sql);
		} Dba::commit();

		if(!empty($retry)) {
			//试着再刷新一下
			Queue::do_job_at(1, 'su_updateinfoJob', array($su_uid, 3));	
		}

		return $fan_id;
	}

	/*
		设置当前粉丝信息，可以直接设置一个数组，
		或者设置粉丝open_id
	*/
	public static function set_current_weixin_fan($wx) {
		if (is_array($wx)) {
			$GLOBALS['weixin_fan'] = $wx;	
		}
		else {
			$GLOBALS['weixin_args']['FromUserName'] = $wx;
		}
	}

	/*
		根据消息获取当前公众号粉丝信息,失败返回false
		不存在时会添加一个		
		$FromUserName = 'oz-Rft9o-43mxnwO7NfteG-DVHas';
	*/
	public static function get_current_weixin_fan($field = '') {
		if(!isset($GLOBALS['weixin_fan'])) {
			$FromUserName = self::get_weixin_xml_args('FromUserName');	
			if(!$FromUserName) {
				setLastError(ERROR_INVALID_WEIXIN_ARGS);
				return false;
				//exit('can not get weixin fan before weixin_parse_input!');
			}

			$public_uid = self::get_current_weixin_public('uid');
			$f_table = 'weixin_fans';
			//小程序客服消息会到这里
			if((self::get_current_weixin_public('public_type')&8) == 8) {
				$f_table = 'weixin_fans_xiaochengxu';
			}

			$sql = 'select * from '.$f_table.' where public_uid = '.$public_uid.' && open_id = "'.addslashes($FromUserName).'"';
			if(!($GLOBALS['weixin_fan'] = Dba::readRowAssoc($sql))) {
				$GLOBALS['weixin_fan'] = array('public_uid' => $public_uid, 
											   'open_id' => $GLOBALS['weixin_args']['FromUserName'],
											   'has_subscribed' => 0);
				$GLOBALS['weixin_fan']['uid'] = self::add_a_weixin_fan($GLOBALS['weixin_fan']);		
				$GLOBALS['weixin_fan']['is_new'] = true; //标记一下是新增粉丝
			}
			else if(isset($GLOBALS['weixin_fan']['has_subscribed']) && 
				($GLOBALS['weixin_fan']['has_subscribed'] != 1) && uct_is_weixin_page()) {
				//收到消息了，认为已关注, 更新一下数据库
				Dba::write('update '.$f_table.' set has_subscribed = 1 where uid = '.$GLOBALS['weixin_fan']['uid']);

				//更新更多用户资料
				if($ui = WeixinMod::get_weixin_user_info($GLOBALS['weixin_fan']['public_uid'], $GLOBALS['weixin_fan']['open_id'])) {
					$update = array();
					if(!empty($ui['nickname'])) {
						#$update['name'] = checkString($ui['nickname'], PATTERN_USER_NAME);
						//todo mb4
						$update['name'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $ui['nickname']);
					}
					if(!empty($ui['sex'])) $update['gender'] = checkInt($ui['sex']);
					if(!empty($ui['headimgurl'])) $update['avatar'] = checkString($ui['headimgurl'], PATTERN_URL);
					if($update) {
						Dba::update('service_user', $update, 'uid = '.$GLOBALS['weixin_fan']['su_uid']);
					}

					$update = array();
					if(!empty($ui['province'])) $update['province'] = checkString($ui['province'], PATTERN_USER_NAME);
					if(!empty($ui['city'])) $update['city'] = checkString($ui['city'], PATTERN_USER_NAME);
					if($update) {
						if(!($ret = Dba::readOne('select * from service_user_profile where uid='.$GLOBALS['weixin_fan']['su_uid'])))
						{
							$update['uid'] = $GLOBALS['weixin_fan']['su_uid'];
							Dba::insert('service_user_profile',$update);
						}
						else
						{
							Dba::update('service_user_profile', $update, 'uid = '.$GLOBALS['weixin_fan']['su_uid']);

						}
					}
				}
			}
			//$fan= array('FromUserName' => $FromUserName, 'fan_uid' => 1, 'msg_processor' => '');
			// 更新最后一次发信息时间
			if(uct_is_weixin_page())
			{
				Dba::write('update '.$f_table.' set last_time = '.$_SERVER['REQUEST_TIME'].' where uid = '.$GLOBALS['weixin_fan']['uid']);
			}

		}

		return $field ? (isset($GLOBALS['weixin_fan'][$field]) ? $GLOBALS['weixin_fan'][$field] : false) 
						: $GLOBALS['weixin_fan']; 
	}

	//通过微信接口更新用户资料
	public static function update_server_user_info_from_weixin($public_uid,$open_id,$su_uid)
	{
		if($ui = WeixinMod::get_weixin_user_info($GLOBALS['weixin_fan']['public_uid'], $GLOBALS['weixin_fan']['open_id'])) {
			$update = array();
			if(!empty($ui['nickname'])) {
				#$update['name'] = checkString($ui['nickname'], PATTERN_USER_NAME);
				//todo mb4
				$update['name'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $ui['nickname']);
			}
			if(!empty($ui['sex'])) $update['gender'] = checkInt($ui['sex']);
			if(!empty($ui['headimgurl'])) $update['avatar'] = checkString($ui['headimgurl'], PATTERN_URL);
			if($update) {
				Dba::update('service_user', $update, 'uid = '.$GLOBALS['weixin_fan']['su_uid']);
			}

			$update = array();
			if(!empty($ui['province'])) $update['province'] = checkString($ui['province'], PATTERN_USER_NAME);
			if(!empty($ui['city'])) $update['city'] = checkString($ui['city'], PATTERN_USER_NAME);
			if($update) {
				if(!($ret = Dba::readOne('select * from service_user_profile where uid='.$GLOBALS['weixin_fan']['su_uid'])))
				{
					$update['uid'] = $GLOBALS['weixin_fan']['su_uid'];
					Dba::insert('service_user_profile',$update);
				}
				else
				{
					Dba::update('service_user_profile', $update, 'uid = '.$GLOBALS['weixin_fan']['su_uid']);

				}
			}
		}
	}

	/*
		, 加载公众号对应插件等
	*/
	public static function on_init_weixin_session() {
		AccountMod::set_current_service_provider(self::get_current_weixin_public('sp_uid'));	
		AccountMod::set_current_service_user(self::get_current_weixin_fan('su_uid'));	
		
		$plugs = WeixinPlugMod::get_weixin_public_plugins_available();
		foreach($plugs as $p) {
			if($p['dir'] && $p['processor']) {
				uct_use_app(strtolower($p['dir']));
				$sort = 10;
				if($p['dir'] == 'default') { //默认回复在最后处理
					$sort = 99;
				}
				Event::addStaticClassHandler($p['processor'], $sort);	
			}
		}
	}

	/*
		解析微信传入参数, 确定粉丝信息
	*/
	public static function prepare_weixin_session() {
		Weixin::weixin_parse_input();
		
		self::on_init_weixin_session();
	}

	/*
		按关键字处理消息
	*/
	public static function process_weixin_msg_by_keyword($content = '') {
		//根据关键字自动判断处理模块
		if($p = WeixinPlugMod::get_weixin_public_plugins_by_content(0, $content ? $content : self::get_weixin_xml_args('Content'))) {
				Event::handle('BeforeWeixinNormalMsg', array($p['processor']));
				if(is_callable(array($p['processor'], 'onWeixinNormalMsg'))) {
					//插件使用次数+1
					WeixinPlugMod::increase_current_plugin_used_cnt($p['dir']);	

					$p['processor']::onWeixinNormalMsg();
				}
				else {
					Weixin::weixin_log('error! weixin plugin function onWeixinNormalMsg not callable!  '.$p['processor']);
				}
		}
	}

	public static function get_session_wx_processor() {
		$key = 'mp_'.self::get_current_weixin_public('uid').'_'.self::get_current_weixin_fan('uid');
		if(empty($GLOBALS['arraydb_weixin_fan'][$key])) {
			return false;
		}
	
		$mp = json_decode($GLOBALS['arraydb_weixin_fan'][$key], true);
		return $mp;
	}

	/*
		指定微信插件处理消息
	*/
	public static function set_session_wx_processor($p) {
		if(is_string($p)) {
			$p = array('processor' => $p);
		}
		$key = 'mp_'.self::get_current_weixin_public('uid').'_'.self::get_current_weixin_fan('uid');
		return ($GLOBALS['arraydb_weixin_fan'][$key] = json_encode($p));
	}

	public static function clear_session_wx_processor() {
		$key = 'mp_'.self::get_current_weixin_public('uid').'_'.self::get_current_weixin_fan('uid');
		unset($GLOBALS['arraydb_weixin_fan'][$key]); 
	}


	/*
		微信业务逻辑
	*/
	public static function process_weixin_msg() {
//		Event::addHandler('DefaultWeixinMsg',array('Wxmsg_WxPlugMod','onWeixinNormalMsg'));
		//如果已经指定模块处理, 此时插件使用次数不再+1
		if($mp = self::get_session_wx_processor()) {
			if(strcasecmp(self::get_weixin_xml_args('MsgType'), 'event')) {
				if(is_callable(array($mp['processor'], 'onWeixinNormalMsg'))) {
					Event::handle('BeforeWeixinNormalMsg', array($mp['processor']));
					$mp['processor']::onWeixinNormalMsg();
				}
			}
			else {
				if(is_callable(array($mp['processor'], 'onWeixinEventMsg'))) {
					$mp['processor']::onWeixinEventMsg();
				}
			}
		}

		if (!strcasecmp(self::get_weixin_xml_args('MsgType'), 'event')) {
			//如果是事件消息(关注、取消关注)，所有模块都要处理
			Event::handle('WeixinEventMsg');

			//点击事件,把eventkey作为触发关键字
			if(!strcasecmp(self::get_weixin_xml_args('Event'), 'click')) {
				$GLOBALS['weixin_args']['Content'] = $GLOBALS['weixin_args']['EventKey'];
			}
		}

		self::process_weixin_msg_by_keyword();

		//4.给小程序商户发送客服提示
		//self::send_sp_msg();

		//默认处理方式
		WeixinPlugMod::increase_current_plugin_used_cnt('default');	
		Event::handle('DefaultWeixinMsg');
	}

	/*
		添加一个伪公众号数据
	*/
	public static function add_fake_weixin_public($sp_uid = 0) {
		$public = array(
						'origin_id' => 'fk_'.md5(uniqid()),
						'public_type' => 8,
                        'public_name' =>'待补充'
					);
		return self::add_or_edit_weixin_public($public, $sp_uid);
	}

	public static function add_or_edit_weixin_public($public, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_INVALID_REQUEST_PARAM);
			return false;
		}
		
		if(!empty($public['uid'])) {
			//原始id只能修改一次,但是若之前为伪公众号,则允许修改
			if(isset($public['origin_id'])) {
				$old_origin = Dba::readOne('select origin_id from weixin_public where uid = '.$public['uid']);
				if($old_origin && strncmp($old_origin, 'fk_', 3)) {
					unset($public['origin_id']);
				}
			}

			Dba::update('weixin_public', $public, 'uid = '.$public['uid'].' and sp_uid = '.$sp_uid);
		}
		else {
			if(empty($public['access_mod']))
			{
				$public['access_mod']=0;
			}
			
			if(empty($public['origin_id'])) {
				setLastError(ERROR_INVALID_REQUEST_PARAM);
				return false;
			}

			$cnt = Dba::readOne('select count(*) from weixin_public where sp_uid = '.$sp_uid);
			$max_cnt = Dba::readOne('select max_public_cnt from service_provider where uid = '.$sp_uid);
			if($cnt && $cnt >= $max_cnt) {
				setLastError(ERROR_OUT_OF_LIMIT);
				return false;
			}

			if(empty($public['uct_token'])) {
				$public['uct_token'] = md5(uniqid().$public['origin_id']);
			}
			$public['sp_uid'] = $sp_uid;

			Dba::beginTransaction(); {
			Dba::insert('weixin_public', $public);
			if(!($public['uid'] = Dba::insertID())) {
				setLastError(ERROR_DBG_STEP_1);
				return false;
			}
		
			$sql = 'update service_provider set uct_token = "'.$public['uct_token'].'" where uid = '.$sp_uid;
			Dba::write($sql);
			} Dba::commit();
			$_SESSION['uct_token'] = $public['uct_token'];
			
		}
		return $public['uid'];
	}

	/*
		删除微信公众号
	*/
	public static function delete_weixin_public($public_uid, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_INVALID_REQUEST_PARAM);
			return false;
		}
	
		if(!($public = Dba::readRowAssoc('select * from weixin_public where uid = '.$public_uid)) ||
			($public['sp_uid'] != $sp_uid)) {
			setLastError(ERROR_DBG_STEP_1);
			return false;
		}

		$sql = 'delete from weixin_public where uid = '.$public['uid'];
		Dba::write($sql);

		if(!($uct_token = Dba::readOne('select uct_token from weixin_public where sp_uid = '.$sp_uid.
			' && uct_token != "'.$public['uct_token'].'"'))) {
			$uct_token = '';
		}
	
		$sql = 'update service_provider set uct_token = "'.addslashes($uct_token).'" where uid = '.$sp_uid;
		Dba::write($sql);

		if($sp_uid == AccountMod::get_current_service_provider('uid')) {
			$_SESSION['uct_token'] = $uct_token;
		}
		return true;
	}
	
	
	/*
		刷新公众号access token
	*/
	public static function refresh_weixin_access_token($public_uid = 0) {
		!$public_uid && $public_uid = WeixinMod::get_current_weixin_public('uid');

		$key = 'accesstoken_'.$public_uid;
		unset($GLOBALS['arraydb_weixin_public'][$key]);
	}	

	/*
		获取微信access_token
	*/
	public static function get_weixin_access_token($public_uid = 0) {
		if(!$public_uid) {
			uct_use_app('sp');
			if(!SpMod::has_weixin_public_set() || !($public_uid = WeixinMod::get_current_weixin_public('uid'))) {
				setLastError(ERROR_OBJ_NOT_EXIST);
				return false;
			}
		}
		$key = 'accesstoken_'.$public_uid;
		 
		$t = $GLOBALS['arraydb_weixin_public'][$key];
	
		if(!$t) {
			if($wx = self::get_weixin_public_by_uid($public_uid)) {
				$appid = $wx['app_id'];
				$secret= $wx['app_secret'];
				if($secret=='0')
				{	
					$t=ComponentMod::get_access_token($public_uid, $appid, false);
				}
				else
				{
					if($t = Weixin::weixin_get_access_token($appid, $secret)) {
						$GLOBALS['arraydb_weixin_public'][$key] = array('expire' => 6400, 'value' => $t);
					}
					else {
						setLastError(ERROR_DBG_STEP_1);
					}
				}
			}
		}
		return $t;
	}

	/*
		刷新公众号jsapi ticket
	*/
	public static function refresh_weixin_jsapi_ticket($public_uid = 0) {
		if(!$public_uid && !($public_uid = WeixinMod::get_current_weixin_public('uid'))) {
			$key = 'jsapiticket_'.$public_uid;
			unset($GLOBALS['arraydb_weixin_public'][$key]);
		}
	}	

	/*
		获取微信jsapi ticket
	*/
	public static function get_weixin_jsapi_ticket($public_uid = 0) {
		if(!$public_uid && !($public_uid = WeixinMod::get_current_weixin_public('uid'))) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return false;
		}

		$key = 'jsapiticket_'.$public_uid;
		$t = $GLOBALS['arraydb_weixin_public'][$key];
		if(!$t) {
			if($access_token = self::get_weixin_access_token($public_uid)) {
				if($t =  Weixin::weixin_get_jsapi_ticket($access_token)) {
					$GLOBALS['arraydb_weixin_public'][$key] = array('expire' => 6400, 'value' => $t);
				}
				else {
					setLastError(ERROR_DBG_STEP_1);
				}	
			}
		}
    
		return $t;
	}

	/*
		获取JS SDK中要用到的 
		wx.config({
 		   appId: '', // 必填，公众号的唯一标识
 		   timestamp: , // 必填，生成签名的时间戳
 		   nonceStr: '', // 必填，生成签名的随机串
 		   signature: '',// 必填，签名，见附录1
		});
		数组

		失败返回空数组
	*/
	public static function get_jsapi_params($public_uid = 0) {
		if(!$public_uid) {
			uct_use_app('sp');
			if(!SpMod::has_weixin_public_set() || !($public_uid = WeixinMod::get_current_weixin_public('uid'))) {
				setLastError(ERROR_OBJ_NOT_EXIST);
				return array();
			}
		}
		if(!($wx =  self::get_weixin_public_by_uid($public_uid))) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return array();
		}
		if(!($t = self::get_weixin_jsapi_ticket($wx['uid']))) {
			return array();
		}

		//多出来的uct_token
		unset($_GET['uct_token']);
		if(isset($_GET['_easy']) || isset($_GET['_rewrite'])) {
			unset($_GET['_a']);
			unset($_GET['_u']);
		}
		if(!isset($_SERVER['wx_nonce'])) $_SERVER['wx_nonce'] = substr(md5(uniqid()), 8, 16);
		$tmpArr = array('jsapi_ticket' => $t, 'noncestr' => $_SERVER['wx_nonce'], 'timestamp' => $_SERVER['REQUEST_TIME'],
						#'url' => getUrlName().'/?'.(http_build_query($_GET)));
						'url' => getCurrentUrl());
		#ksort($tmpArr, SORT_STRING);
		#$s1 = sha1(urldecode(http_build_query($tmpArr)));
		#Weixin::weixin_log('jssdk old s is --> '.urldecode(http_build_query($tmpArr)));
		$s = "jsapi_ticket=${tmpArr['jsapi_ticket']}&noncestr=${tmpArr['noncestr']}&timestamp=${tmpArr['timestamp']}&url=${tmpArr['url']}";
		#Weixin::weixin_log((urldecode(http_build_query($tmpArr)) == $s).'jssdk new s is --> '.$s);
		$s = sha1($s);

		//Weixin::weixin_log('js sdk params '.var_export($tmpArr, true).'=====signature==='. $s);
		return array('appId'    => $wx['app_id'], 
					'timestamp' => $tmpArr['timestamp'],
					'nonceStr'  => $tmpArr['noncestr'],
					'signature' => $s,
					'url'       => $tmpArr['url'],
					'debug'     => !empty($_REQUEST['_d']) ? true : false,
				);
	}

	/*
		获取临时二维码
		文档地址 http://mp.weixin.qq.com/wiki/18/28fc21e7ed87bec960651f0ce873ef8a.html
	*/
	public static function get_temp_qrcode($scene_id, $public_uid = 0) {
		if(!($access_token = self::get_weixin_access_token($public_uid))) {
			return false;
		}	
		if(!($ret = Weixin::weixin_get_temp_qrcode($scene_id, $access_token))) {
			setLastError(ERROR_IN_WEIXIN_API);
			self::refresh_weixin_access_token($public_uid);
			return false;
		}
		
		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ret['ticket']);
	}

	/*
		上传png图片作为微信临时素材
		文档地址 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738726&token=&lang=zh_CN
		
		@param  $png_data png图片数据 可以是gd画的也可以是file_get_contents('file.png') 得到的

		@return 成功返回media_id, 失败false
	*/
	public static function upload_inmemory_png_as_tmp_media($png_data, $public_uid = 0) {
		if(!($access_token = self::get_weixin_access_token($public_uid))) {
			return false;
		}	
		
		$param = array('type' => 'image', 'media' => 'media; filename="temp.png"'."\r\n".'Content-Type: image/png'."\r\n");
		$param[$param['media']] = $png_data;

		$url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type=image';
		$ret = Weixin::weixin_upload_file( $url, $param);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['media_id'])) {
			setLastError(ERROR_DBG_STEP_2);
			return false;
		}

		return $ret['media_id'];
	}

	/*	
		发送客服消息

		图片
		$msg = array(
			'touser' =>  openid
			'msgtype' => 'image'
			'image' => array(
						'media_id' =>  媒体uid
					)
		)

		文字	
		$msg = array(
			'touser' =>  openid
			'msgtype' => 'text'
			'text' => array(
						'content' =>  
					)
		)
		http://mp.weixin.qq.com/wiki/11/c88c270ae8935291626538f9c64bd123.html
	*/
	public static function send_kf_msg($msg, $public_uid = 0) {
		if(!$access_token = self::get_weixin_access_token($public_uid)) {
			return false;
		}
		
		if(!empty($msg['text']['content'])) {
			if($public_uid != 0) {
				WeixinMod::set_current_weixin_public(WeixinMod::get_weixin_public_by_uid($public_uid));
			}
			WeixinMod::set_current_weixin_fan($msg['touser']);
			
			//如果是服务号或者 开启了代理网页oauth2授权登录, 则不自动加weixin_session_id
			uct_use_app('su');
			if(WeixinMod::get_current_weixin_public('public_type') != 2 && 
				!SuWxLoginMod::is_proxy_wxlogin_available(WeixinMod::get_current_weixin_public('sp_uid'))) {
				$msg['text']['content'] = WeixinMod::try_replace_to_weixin_session_id_url($msg['text']['content']);
			}
		}
			
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $msg);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			Weixin::weixin_log('send kf msg failed!: ' . $ret2);
			return false;
		}

		return $ret;
	}

	/*
		发送模板消息
		微信文档 http://mp.weixin.qq.com/wiki/5/6dde9eaa909f83354e0094dc3ad99e05.html

		$msg = array(
			'touser' => openid
			'template_id' => 模板id
			'url' => 链接地址 可选 
			'miniprogram' => array( //跳转到关联小程序，可选
				'appid':'xiaochengxuappid12345',
				'pagepath':'index?foo=bar'
			),    

			'data' => array(
				'first' => array(
					'value' => '系统通知',
					'color' => '#C0C0C0',
				),
				'keyword1' => array(
					'value' => '用户下单',
					'color' => '#C0C0C0',
				),
				'keyword2' => array(
					'value' => '恭喜有人下单啦',
					'color' => '#C0C0C0',
				),
				'remark' => array(
					'value' => '',
					'color' => '#173177',
				),
				...
			)
		)
	*/
	public static function send_template_msg($msg, $public_uid = 0) {
		if(!$access_token = self::get_weixin_access_token($public_uid)) {
			return false;
		}
		
		$ret = Weixin::weixin_send_template_msg($msg, $access_token);
		#Weixin::weixin_log('template msg -> '.$ret);
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['msgid'])) {
			setLastError(ERROR_IN_WEIXIN_API);
			self::refresh_weixin_access_token($public_uid);
			return false;
		}

		return $ret['msgid'];
	}

	/*	
		获取微信用户信息
		http://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html	
	*/
	public static function get_weixin_user_info($public_uid, $open_id) {
		if(!$access_token = self::get_weixin_access_token($public_uid)) {
			return false;
		}

		return Weixin::weixin_get_user_info($open_id, $access_token);
	}

	/*
		判断用户是否关注了公众号
	*/
	public static function has_su_subscribed($su_uid = 0) {
		if(!$su_uid && !($su_uid = AccountMod::get_current_service_user('uid'))) {
			return false;
		}
		if(!$fan = Dba::readRowAssoc('select * from weixin_fans where su_uid = '.$su_uid.' limit 1')) {
			return false;
		}

		if(!empty($fan['has_subscribed'])) {
			return $fan['has_subscribed'] == 1 ? $fan['public_uid'] : false;
		}
		$ui = self::get_weixin_user_info($fan['public_uid'], $fan['open_id']);
		$subcribed = empty($ui['subscribe']) ? 2 : 1;
		Dba::write('update weixin_fans set has_subscribed = '.$subcribed.' where uid = '.$fan['uid']);

		return $subcribed == 1 ? $fan['public_uid'] : false;
	}

	/*
	 * 取公众号的用户分组
	 */
	public static function weixin_get_all_group($public_uid=0)
	{
		if(!($public_uid)) $public_uid = WeixinMod::get_current_weixin_public('uid');
		if ( isset( $GLOBALS[ 'arraydb_weixin_public' ][ 'public_groups_' . $public_uid ] ) ) {
			return json_decode($GLOBALS[ 'arraydb_weixin_public' ][ 'public_groups_' . $public_uid ],true);
		}
		$ret = Weixin::weixin_get_all_group(WeixinMod::get_weixin_access_token($public_uid));
		empty($ret) ||
		$GLOBALS[ 'arraydb_weixin_public' ][ 'public_groups_' . $public_uid ] = array(
			'value' => $ret,
			'expire' => time()+60*15 //15min
		);
		return $ret;
	}


	/*
		获取公众号biz参数
	*/
	public static function get_weixin_public_biz($public_uid) {
		$key = 'biz_'.$public_uid;

		return $GLOBALS['arraydb_weixin_public'][$key];
	}

	/*
		设置公众号biz参数
	*/
	public static function set_weixin_public_biz($biz, $public_uid) {
		$key = 'biz_'.$public_uid;

		return $GLOBALS['arraydb_weixin_public'][$key] = $biz;
	}

	/*
	    给小程序-商户发送客服提示
	 */
	public static function send_sp_msg($sp_uid=0){

		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_INVALID_REQUEST_PARAM);
			return false;
		}
		$public_uid = self::get_current_weixin_public('uid');
		$public = WeixinMod::get_weixin_public_by_uid($public_uid);
		if(($public['public_type']&8) != 8){
			return false;
		}

		if(empty($GLOBALS['weixin_args'])){
			return false;
		}

		//首次进入客服界面通知
		if($GLOBALS['weixin_args']['MsgType'] == 'event'){

			$su_uid = Dba::readOne('select su_uid from weixin_fans_xiaochengxu where open_id = "'.addslashes($GLOBALS['weixin_args']['FromUserName']).'"');
			$user = Dba::readRowAssoc('select uid, name, avatar from service_user where uid = '.$su_uid);


			$msg = array(
				'title' => '小程序 客服消息提醒',
				'content' => '用户'.(empty($user['name'])?$user['avatar']:$user['name']).'进入客服会话，请<a href="https://mpkf.weixin.qq.com/cgi-bin/kfloginpage">登录查看</a>',
				'sp_uid' => $sp_uid,
			);
			uct_use_app('sp');
			//SpMsgMod::add_sp_msg($msg);

			return false;
		}


	}

}

