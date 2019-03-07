<?php

class ComponentMod {
	/*
	处理component_callback de 事件和消息推送
	component_verify_ticket协议 component_verify_ticket
	和
	取消授权通知 unauthorized
	*/
	public static function component_callback() {
		if ((!$ret = component::component_callback()) || empty($ret['InfoType'])) {
			Weixin::weixin_log('error! component_callback input params error!');
			return 'false';
		}

		switch ($ret['InfoType']) {
			case 'component_verify_ticket': {
				$GLOBALS[ 'arraydb_sys' ][ 'component_verify_ticket' ] = $ret[ 'ComponentVerifyTicket' ];
				return 'success';
			}
			case 'unauthorized': {
			//有公众号取消授权，发提示信息给对应账号
				if (($app_id = $ret[ 'AuthorizerAppid']) && preg_match('/^[\w]{3,64}$/', $ret['AuthorizerAppid'])) {
					$_REQUEST['_app_id'] = $app_id;
					$ret = WeixinMod::get_current_weixin_public();
					AccountMod::set_current_service_provider( $ret['sp_uid'] );
					$msg = array(
						'title' => '公众号取消授权 提醒',
						'content' => '尊敬的用户，公众号：' . $ret['public_name'] . ' 已经取消授权。您在平台上设置的功能已经全部失效，如需继续使用，<a href="?_a=sp&_u=index.addpublic&uid='.$ret['uid'].'">请重新再次授权。</a>',
						'sp_uid' => $ret['sp_uid'],
					);
					uct_use_app( 'sp' );
					SpMsgMod::add_sp_msg( $msg );
					$sql='update weixin_public set access_mod=-1 where app_id="'.$app_id.'"';
					Dba::write($sql);
				}
				return 'success';
			}
	
			//
			case 'uctphp_rpc_call': {
				$callable = unserialize($ret['uctoo_callable']);
				$param = unserialize($ret['uctoo_param']);
				Weixin::weixin_log('uctphp_rpc_call dbg '.var_export($callable, true).'['.var_export($param, true).']');

				return self::uctoo_rpc_call_reply_enc(serialize(call_user_func_array($callable, $param)));
			}
			
			default: return 'not processed!';
		}
	}
	
	//取得预授权码
	public static function get_pre_auth_code() {
		$component_access_token = component::get_component_token();
		// if(empty($component_access_token)) 
		// {
		// echo 'error:component_access_token is miss! Please Try again later';exit;
		// }
		$pre_auth_code = component::get_create_preauthcode( $component_access_token );
		return !empty( $pre_auth_code ) ? $pre_auth_code : '';
	}
	
	
	
	
	/*
	用户登陆公众号号后点击授权后回调地址,取得authorization_code,
	并通过authorization_code获取公众号信息,
	app_id,access_token,expires_in,refresh_token,
	*/
	public static function get_query_auth( $authorization_code ) {
		$component_access_token = component::get_component_token();
		if ( empty( $component_access_token ) ) {
			echo 'error:component_access_token is miss in get_query_auth! Please Try again later';
			exit(1);
		}
		
		$ret = component::get_query_auth( $component_access_token, $authorization_code );
		if ( empty( $ret ) ) {
			echo 'error:get_query_auth is fail!';
			exit(1);
		}
		return $ret;
	}
	
	
	/*
	sp_uid 
	app_id 
	access_token_public_uid 
	public_uid
	（刷新）授权公众号的令牌
	*/
	public static function refresh_token( $public_uid, $authorizer_appid, $bexit = true) {
		$component_access_token = component::get_component_token();
		if ( empty( $component_access_token ) ) {
			if($bexit) {
				echo 'error:component_access_token is miss! Please Try again later';
				exit(1);
			}
			else {		
				return false;
			}
		}
		$ret = component::authorizer_refresh_token( $public_uid, $authorizer_appid, $component_access_token );
		if ( empty( $ret ) ) {
			if($bexit) {
				echo 'error:refresh_token is fail!';
				exit(1);
			}
			else {
				return false;
			}
		}
		$GLOBALS[ 'arraydb_weixin_public' ][ 'access_token_' . $public_uid ] = array(
			 'value' => $ret[ 'authorizer_access_token' ],
			'expire' => $ret[ 'expires_in' ] 
		);
		$GLOBALS[ 'arraydb_weixin_public' ][ 'authorizer_refresh_token_' . $public_uid ] = $ret[ 'authorizer_refresh_token' ];
		return $ret[ 'authorizer_access_token' ];
	}
	
	/*
	利用,取得authorization_code,
	并通过authorization_code获取公众号信息,
	
	*/
	public static function get_authorizer_info( $authorizer_appid ) {
		$component_access_token = component::get_component_token();
		if ( empty( $component_access_token ) ) {
			echo 'error:component_access_token is miss in get_authorizer_info! Please Try again later';
			exit(1);
		}
		
		$ret = component::get_authorizer_info( $authorizer_appid, $component_access_token );
		if ( empty( $ret ) ) {
			echo 'error:get_authorizer_info is fail!';
			exit(1);
		}
		return $ret;
	}
	
	public static function add_or_edit_weixin_public( $ret_query_auth, $ret_authorizer_info, $sp_uid ) {

		$public_type = ( $ret_authorizer_info[ 'authorizer_info' ][ 'service_type_info' ][ 'id' ] == 2 )? 2 : 1 ; //type 为2时时服务号
		$public_type = ( $ret_authorizer_info[ 'authorizer_info' ][ 'verify_type_info' ][ 'id' ] == -1 )? $public_type : ( ($public_type == 1)? 17 : 18 ); //type-info 为0未认证
		#Weixin::weixin_log('add wx public '.var_export($ret_authorizer_info, true));
		//小程序
		if(!empty($ret_authorizer_info['authorizer_info']['MiniProgramInfo'])) {
			$public_type = 8;
		} else {
			header('Content-Type: text/html; charset=utf-8');
			echo '这是一个公众号，不是一个小程序！ ['.addslashes( $ret_authorizer_info[ 'authorizer_info' ][ 'nick_name' ]).
					'] ,请在手机重新选择！ 如果您确实要授权一个公众号，请联系客服！';
			
			exit(1);
		}
		
		$public = array(
			'app_id' => $ret_authorizer_info[ 'authorization_info' ][ 'authorizer_appid' ],
			'app_secret' => 0,
			//'uct_token'=>COMPONENT_TOKEN,
			'aes_key' => COMPONENT_KEY,
			'origin_id' => $ret_authorizer_info[ 'authorizer_info' ][ 'user_name' ],
			'public_name' => addslashes( $ret_authorizer_info[ 'authorizer_info' ][ 'nick_name' ] ),
			'public_type' => $public_type,
			'head_img' => empty( $ret_authorizer_info[ 'authorizer_info' ][ 'head_img' ] ) ? '' : $ret_authorizer_info[ 'authorizer_info' ][ 'head_img' ],
			'qrcode_url' => empty( $ret_authorizer_info[ 'authorizer_info' ][ 'qrcode_url' ] ) ? '' : $ret_authorizer_info[ 'authorizer_info' ][ 'qrcode_url' ],
			'msg_mode' => '3',
			'access_mod' => '1' 
		);

		$sql = 'select uid,sp_uid from weixin_public where app_id="' . $public[ 'app_id' ] . '"';
		$rets = Dba::readRowAssoc( $sql );
		//取uid 有则为更新信息，无则添加
		$public[ 'uid' ] = !empty( $rets[ 'uid' ] ) ? $rets[ 'uid' ] : '';
		//公众号已存在时 判断是否为同一用户操作
		if ( !empty( $rets[ 'sp_uid' ] ) && $rets[ 'sp_uid' ] != $sp_uid ) {
			header('Content-Type: text/html; charset=utf-8');
			$sp = AccountMod::get_service_provider_by_uid($rets['sp_uid']);
			echo '该公众号/小程序已经被其他账号使用 ['.$public['public_name'].
				'] 已绑定账号 '.$sp['account'].'['.$sp['name'].']';
			exit(1);
		}
		//新增时 判断配额
		if(empty($public[ 'uid' ]))
		{
			$cnt = Dba::readOne('select count(*) from weixin_public where sp_uid = '.$sp_uid);
			$max_cnt = Dba::readOne('select max_public_cnt from service_provider where uid = '.$sp_uid);
			if($cnt && $cnt >= $max_cnt) {
				//超配额时 寻找该商户的伪公众号
				$public[ 'uid' ] = Dba::readOne('select uid from weixin_public where origin_id like "fk_%" and sp_uid='.$sp_uid);
			}
		}

		$public_uid = WeixinMod::add_or_edit_weixin_public( $public, $sp_uid );
		if ( empty( $public_uid ) ) {
			header('Content-Type: text/html; charset=utf-8');
			switch ( getErrorString() ) {
				case 'ERROR_INVALID_REQUEST_PARAM':
					echo '缺少参数';
					break;
				case 'ERROR_OUT_OF_LIMIT':
					echo '配额已满。';
					break;
				case 'ERROR_DBG_STEP_1':
					echo '该公众号/小程序已经被其他账号使用';
					break;
				default:
					echo '未知错误' . getErrorString();
			}
			exit(1);
		}
		
		if ( !empty( $ret_query_auth[ 'authorization_info' ][ 'authorizer_access_token' ] ) ) {
			$GLOBALS[ 'arraydb_weixin_public' ][ 'access_token_' . $public_uid ] = array(
				 'value' => $ret_query_auth[ 'authorization_info' ][ 'authorizer_access_token' ],
				'expire' => $ret_query_auth[ 'authorization_info' ][ 'expires_in' ] 
			);
			$GLOBALS[ 'arraydb_weixin_public' ][ 'authorizer_refresh_token_' . $public_uid ] = $ret_query_auth[ 'authorization_info' ][ 'authorizer_refresh_token' ];
		}
		return $public_uid;
	}
	/*
	获取公众号access_token
	*/
	public static function get_access_token( $public_uid, $authorizer_appid , $bexit = true) {
		if ( isset( $GLOBALS[ 'arraydb_weixin_public' ][ 'access_token_' . $public_uid ] ) ) {
			return $GLOBALS[ 'arraydb_weixin_public' ][ 'access_token_' . $public_uid ];
		}
		$authorizer_access_token = self::refresh_token( $public_uid, $authorizer_appid, $bexit);
		if ( !empty( $authorizer_access_token ) ) {
			return $authorizer_access_token;
		} else {
			if($bexit) {
				echo ( '刷新授权公众号的令牌失败' ); exit(1);
			}
			else {
				return false;
			}
		}
		
	}
	
	/*
		自动授权方式测试发送消息 $app_id 公众号的app_id, 

		返回依然是加密的xml内容	
		用于主域名代理调用

		如果想作为调试 可以自己对返回值再解密一下
	*/
	public static function debug_send_msg($url_domain = '', $app_id = '', $xml = '') {
		if(!$app_id && !($app_id = requestString('_app_id', PATTERN_TOKEN))) {
			setLastError(ERROR_DBG_STEP_1);
			return false;
		}
		if(!$xml) {
			if(empty($GLOBALS['weixin_args'])) {
				setLastError(ERROR_DBG_STEP_2);
				return false;
			}
			$xml = Weixin::array_to_weixin_xml($GLOBALS['weixin_args']);
		}
		$url = (isHttps() ? 'https://' : 'http://').($url_domain ? $url_domain : getDomainName()).'?_u=component.message&_app_id='.$app_id;
		

		$encodingAesKey = COMPONENT_KEY;
		$token = COMPONENT_TOKEN;
		$appId = COMPONENT_APPID;
		$timeStamp = time();
		$nonce = '12334563';

		include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
		$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
		$encryptMsg = '';
		$errCode = $pc->encryptMsg($xml, $timeStamp, $nonce, $encryptMsg);
		if ($errCode != 0) {
			setLastError(ERROR_DBG_STEP_3);
			return false;
		}


		$url .= '&timestamp='.$timeStamp.'&nonce='.$nonce;
		libxml_disable_entity_loader();
		$args = @ array_map(function($v){ return is_object($v) ? (array)$v : $v;},
							(array)(simplexml_load_string($encryptMsg, 'SimpleXMLElement', LIBXML_NOCDATA)));
		$tmpArr = array($token, $timeStamp, $nonce, $args['Encrypt']);
		sort($tmpArr, SORT_STRING);
		$msg_signature = sha1(implode($tmpArr));
		$url .= '&encrypt_type=aes&msg_signature='.$msg_signature;
		$tmpArr = array($token, $timeStamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$signature = sha1(implode($tmpArr));
		$url .= '&signature='.$signature;

		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $encryptMsg);
		$ret = curl_exec($c);
		curl_close($c);

		return $ret;
	}

	/*
		调用另一台机器上的方法
		前2个参数跟 call_user_func_array 是一样的, 多了一个url_domain
	*/
	public static function uctphp_rpc_call($callable, $param = array(), $url_domain = '') {
		$url = (isHttps() ? 'https://' : 'http://').($url_domain ? $url_domain : getDomainName()).'?_u=component.callback';

		$xml = '<xml><InfoType>uctphp_rpc_call</InfoType><uctoo_callable><![CDATA['.
					serialize($callable).']]></uctoo_callable><uctoo_param><![CDATA['.serialize($param).']]></uctoo_param></xml>';
		include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
		$pc = new Prpcrypt(COMPONENT_KEY);//$this->encodingAesKey
		$enc = $pc->encrypt($xml, COMPONENT_APPID);
		if($enc[0] != 0) {
			return false;
		}
		$enc = $enc[1];
		$post = '<xml><Encrypt>'.$enc.'</Encrypt><AppId>'.COMPONENT_APPID.'</AppId></xml>';
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		$ret = curl_exec($c);
		curl_close($c);

		return unserialize(self::uctoo_rpc_call_reply_dec($ret));


	}

	protected static function uctoo_rpc_call_reply_enc($str) {
		$pc = new Prpcrypt(COMPONENT_KEY);
		$enc = $pc->encrypt($str, COMPONENT_APPID);
		return $enc[0] == 0 ? $enc[1] : false;
	}
	protected static function uctoo_rpc_call_reply_dec($str) {
		$pc = new Prpcrypt(COMPONENT_KEY);
		$enc = $pc->decrypt($str, COMPONENT_APPID);
		return $enc[0] == 0 ? $enc[1] : false;
	}

}

