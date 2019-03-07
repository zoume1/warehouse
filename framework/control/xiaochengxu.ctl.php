<?php

class XiaochengxuCtl {
	/*
		小程序 用户登陆流程
	*/
	public function login() {
		if(!$code = requestString('code', PATTERN_NORMAL_STRING)) {
			//调试模式下没有code等信息, 随便添加一个用户
			if(requestBool('_d') || (defined('DEBUG_WXPAY') && DEBUG_WXPAY)) {
			    if($name = requestString('name', PATTERN_USER_NAME)) {
			        $avatar = requestString('avatar', PATTERN_URL);
			        $sp_uid = WeixinMod::get_current_weixin_public('sp_uid');
			        if(!$su_uid = Dba::readOne('select uid from service_user where sp_uid = '.
			                $sp_uid.' && name = "'.addslashes($name).'" limit 1')) {
			            Dba::insert('service_user', array('name' => $name, 'avatar' => $avatar,
			                        'sp_uid' => $sp_uid, 'create_time' => $_SERVER['REQUEST_TIME']));
			            $su_uid = Dba::insertID();
			        }
			
			        $_SESSION['su_login'] = $_SESSION['su_uid'] = $su_uid;
			        $ret = array('su_uid' => $su_uid, 'open_id' => '', 'PHPSESSID' => session_id());
			        outRight($ret);
			    }
			}

			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		//没有这2个参数时不登陆，但是可以取到用户openid, 可用于微信支付
		if(!$iv = requestString('iv')) {
			#outError(ERROR_INVALID_REQUEST_PARAM);
		}
		if(!$encryptedData = requestString('encryptedData')) {
			#outError(ERROR_INVALID_REQUEST_PARAM);
		}
		
		outRight(XiaochengxuMod::login_by_code($code, $iv, $encryptedData));
	}

	/*
		生成小程序二维码
		https://mp.weixin.qq.com/debug/wxadoc/dev/api/qrcode.html

		// 扫码后进入小程序首页， 获取二维码中的 scene参数
		//
		Page({
		  onLoad: function(options) {
		    var scene = options.scene
		  }
		})
	*/
	public function qrcode() {
		$public_uid = requestInt('public_uid');
		if(!$access_token = WeixinMod::get_weixin_access_token($public_uid)) {
			outError();
		}
		
		//最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~ 
		//（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
		if($data['path'] = requestString('path')) {
			//永久有效， 数量限制为10万
			$type = requestInt('type');
			if(!empty($type)){
				$url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$access_token;
			}else{
				$url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
			}

		} else {
			unset($data['path']);
			$data['scene'] = requestStringLen('scene', 32);
			//必须是已经发布的小程序页面，例如 "pages/index/index" 如果不填写这个字段，默认跳主页面
			$data['page'] = requestString('page'); 
//			unset($data['page']);
			//永久有效， 不限数量
			$url = 'http://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token;
		}

		$data['width'] = requestInt('width', 430);
		$data['auto_color'] = requestBool('auto_color', true);
		$data['line_color'] = requestKvJson('line_color');
		if(!$data['line_color']) {
			$data['line_color'] = array('r' => '0','g' => '0','b' => '0',);
		}
		

		$ret2 = $ret = Weixin::weixin_https_post($url, $data);
		header('Cache-Control: public');
		header('Last-Modified: ' . $_SERVER['REQUEST_TIME']);
		header('Content-Type: image/jpeg');
		if($ret2[0] == '{' && strlen($ret2) < 512) {
			Weixin::weixin_log('['.$public_uid.'] get xcx qrcode fail ! '.$ret2);
		}
		echo $ret;
		exit();

		if(!$ret || !($ret = @json_decode($ret, true))) {
			Weixin::weixin_log(WeixinMod::get_current_weixin_public('public_name').' getwxacodeunlimit fail ooobbb '.print_r($ret2, true));
			WeixinMod::refresh_weixin_access_token();	
			//outRight('???'.$ret2);
			outError(ERROR_DBG_STEP_1);
		}	

		outRight($ret);
	}

	/*
		解密
	*/
	public function decrypt() {
		if(!$iv = requestString('iv')) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		if(!$encryptedData = requestString('encryptedData')) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		if(empty($_SESSION['session_key'])) {
			outError(ERROR_USER_HAS_NOT_LOGIN);
		}

		$data = '';
		include_once UCT_PATH.'vendor/weixin_xiaochengxu_encrypt/wxBizDataCrypt.php';
		$pc = new WXBizDataCrypt(WeixinMod::get_current_weixin_public('app_id'), $_SESSION['session_key']);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);
		if($errCode != 0) {
			Weixin::weixin_log(WeixinMod::get_current_weixin_public('app_id').' decrypt fail'.($_SESSION['session_key']));
			outError(ERROR_DBG_STEP_2);	
		}

		if(is_string($data)) $data = @json_decode($data, true);
		//解密的内容一般是微信群id,  自动保存一下
		if(!empty($data['openGId']) && ($su_uid = AccountMod::has_su_login())) {
			$sp_uid = AccountMod::require_sp_uid();
			uct_use_app('su');
			#$g = array('wx_group_id' => $data['openGid'], 'sp_uid' => $sp_uid);
			#WechatGroupMod::add_or_edit_group($g); 
			$g_uid = WechatGroupMod::wx2gid($data['openGId'], $sp_uid);
			WechatGroupMod::add_user_to_group($su_uid, $g_uid, $sp_uid);
			$data['g_uid'] = $g_uid;
		}
		
		outRight($data);
	}

	/*
	 * 小程序客服消息
	 * https://mp.weixin.qq.com/debug/wxadoc/dev/api/custommsg/conversation.html
	 * https://mpkf.weixin.qq.com/
	 */
	public function message(){

		$sp_uid = AccountMod::require_sp_uid();
		//站内通知
		$msg = array(
			'title' => '微信小程序 客服消息提醒',
			'content' => '收到微信小程序客服消息提醒<a href="https://mpkf.weixin.qq.com/">点击查看详情</a> ',
			'sp_uid' => $sp_uid,
		);
		uct_use_app('sp');
		SpMsgMod::add_sp_msg($msg);
//		outRight();
	}
	
	/*
		获取小程序页面配置	
	*/
	public function get_page() {
		$sp_uid = AccountMod::require_sp_uid();
		$public_uid = WeixinMod::get_current_weixin_public('uid');
		
		if($uid = requestInt('uid')) {
			$page = XiaochengxuPagesMod::get_xiaochengxu_pages_by_uid($uid);
			//todo 要不要检查一下权限
			if(0 && (!$page || ($page['sp_uid'] != $sp_uid))) {
				outError(ERROR_OBJ_NOT_EXIST);
			}
		} else if($title = requestString('title', PATTERN_NORMAL_STRING)) {
			$page = XiaochengxuPagesMod::get_xiaochengxu_pages_by_title($title, $sp_uid);
		} else {
			#echo $sp_uid.PHP_EOL;
			#echo $public_uid;
			$page = XiaochengxuPagesMod::get_xiaochengxu_pages_by_default($sp_uid, $public_uid);
			if($page && isset($_REQUEST['modify_time']) && 
				$_REQUEST['modify_time'] == $page['modify_time']) {
				//未更改，使用缓存
				$page['content'] = '';
				setLastError(ERROR_NOT_CHANGE);	
			}
		}
		outRight($page);
	}

	/*
		todo 技术支持信息 
	*/
	public function get_agent_info() {
		$sp_uid = AccountMod::require_sp_uid();
		$ret = array(
		);
		if($sp_uid == 31) {
			$ret = array(
				'title' => '@ 深圳快马加鞭 2018',
				'logo' => '',
			);	
		}
		else if(in_array($sp_uid, array(1505))) {
			$ret = array(
				'title' => '@ 金士顿提供技术支持',
				'logo' => '',
			);	
		}
		
		outRight($ret);
	}

	/*
		取小程序自定义菜单 等
	*/
	public function get_ext_json() {
		$public_uid = WeixinMod::get_current_weixin_public('uid');
		outRight(XiaochengxuMod::get_xiaochengxu_ext_json($public_uid));
	}

	/*
		设置小程序底部菜单 等

		json 为app.json中的格式, 想覆盖哪个就传哪个
	*/
	public function set_ext_json() {
		$public_uid = WeixinMod::get_current_weixin_public('uid');
		$json = requestKvJson('json');
		outRight(XiaochengxuMod::set_xiaochengxu_ext_json($json, $public_uid));
	}

}

