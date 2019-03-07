<?php
/*
	微信小程序
*/
//todo 支持 php5.3
if(!defined('JSON_UNESCAPED_UNICODE')) define('JSON_UNESCAPED_UNICODE', 0);

class XiaochengxuMod {
	/*
		根据小程序 _uct_token 初始化  设置一下当前小程序和商户信息
	*/
	public static function start_xiaochengxu_session() {
		if(empty($_GET['_uct_token']) 
				|| !preg_match('/^[\w]{3,64}/', $_GET['_uct_token'])
				|| !($GLOBALS['weixin_public'] = Dba::readRowAssoc(
					'select * from weixin_public where uct_token = "'.($_GET['_uct_token']).'"', 'WeixinMod::func_get_weixin'))) {
			return false;
		}

		AccountMod::set_current_service_provider(WeixinMod::get_current_weixin_public('sp_uid'));	
		return true;
	}


	/*
		根据小程序的 code 登陆

		如果不传 $iv 和 $encryptedData , 那么就只返回用户openid，不进行登陆
	*/
	public static function login_by_code($code, $iv, $encryptedData) {
		if(!($app_id = WeixinMod::get_current_weixin_public('app_id')) ||
		   !($app_secret = WeixinMod::get_current_weixin_public('app_secret'))) {
			#setLastError(ERROR_SERVICE_NOT_AVAILABLE);
			#return false;
		}

		//这个是小程序自动授权
		if(!$app_secret || $app_secret == '0' || WeixinMod::get_current_weixin_public('access_mod') == 1) {
			$access_token = Component::get_component_token();
			$url = 'https://api.weixin.qq.com/sns/component/jscode2session?appid='.$app_id.
					'&js_code='.$code.'&grant_type=authorization_code&component_appid='.
					COMPONENT_APPID.'&component_access_token='.$access_token;
		} else {
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$app_id.'&secret='.
			$app_secret.'&js_code='.$code.'&grant_type=authorization_code';
		}

		//echo $url;die;
		if(!($ret2 = $ret = Weixin::weixin_https_get($url)) || !($ret = @json_decode($ret, true)) || 
			empty($ret['openid']) || empty($ret['session_key'])) {
			setLastError(ERROR_DBG_STEP_1);
			Weixin::weixin_log('get url failed '.$url.'. return '.$ret2);
			return false;
		}

		if(!$iv || !$encryptedData) {
			return $ret;
		}

		//Weixin::weixin_log($url);
		//Weixin::weixin_log(var_export($ret, true));

		$data = '';
		include_once UCT_PATH.'vendor/weixin_xiaochengxu_encrypt/wxBizDataCrypt.php';
		$pc = new WXBizDataCrypt($app_id, $ret['session_key']);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);
		if($errCode != 0) {
			setLastError(ERROR_DBG_STEP_2);	
			return false;
		}
		
/*
{"openId":"oozIJ0ZyLR2QNSg4-BC_dZWZj284","nickName":"刘路浩公众号极速开发","gender":1,"language":"zh_CN","city":"Shenzhen","province":"Guangdong","country":"CN","avatarUrl":"http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKOASyV1lpdkdpLZeu08JvhJphITh2bcnS5y1Wl5kGAMuyjkAoic7PqXOrzp4Q4RNOvfD9HcyWlkOQ/0","unionId":"oumfTs5JskKS2ZcOVkre6Cv7KZu0","watermark":{"timestamp":1482754734,"appid":"wx2b2151820c13f54e"}}
*/
		//var_export($data);
		if(!$data || !($data = @json_decode($data, true)) || empty($data['openId'])) {
			setLastError(ERROR_DEBUG_STEP_3);	
			return false;
		}
		//小程序登陆流程
		/*
			先看看有没有先前 公众号的 unionid 
			注意 当先用小程序注册，后关注公众号时，会存在问题 
		*/
		$su_uid = 0;
		$open_id = $data['openId'];
		$public_uid = WeixinMod::get_current_weixin_public('uid');
		if(!empty($data['unionId'])) {
			$su_uid = Dba::readOne('select su_uid from weixin_unionid where union_id = "'.addslashes($data['unionId']).'"');
			//有unionId 的时候， 还要保存一下对应此小程序的 openid
			if($su_uid && !Dba::readOne('select su_uid from weixin_fans_xiaochengxu where public_uid = '.
				$public_uid.' && open_id = "'.addslashes($open_id).'"')) {
				$insert = array('su_uid' => $su_uid, 'public_uid' => $public_uid, 
					'open_id' => $open_id, 'create_time' => $_SERVER['REQUEST_TIME']);
				Dba::insert('weixin_fans_xiaochengxu', $insert);
			}
		}
		if(!$su_uid) {
			$su_uid = Dba::readOne('select su_uid from weixin_fans_xiaochengxu where public_uid = '.
				$public_uid.' && open_id = "'.addslashes($open_id).'"');
		}

		//创建新的用户
		if(!$su_uid) {
			$su = array();	
			if(!empty($data['nickName'])) {
				//mb4
				$su['name'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $data['nickName']);
			}
			if(!empty($data['avatarUrl'])) {
				$su['avatar'] = $data['avatarUrl'];
			}
			if(!empty($data['gender'])) {
				$su['gender'] = $data['gender'];
			}
			if(empty($su['name'])) {
				$su['name'] = '小程序用户';
			}
			$su['sp_uid'] = WeixinMod::get_current_weixin_public('sp_uid');
			Dba::beginTransaction(); {
				$su_uid = $su['uid'] = AccountMod::add_or_edit_service_user($su);
				$insert = array('su_uid' => $su_uid, 'public_uid' => $public_uid, 
								'open_id' => $open_id, 'create_time' => $_SERVER['REQUEST_TIME']);
				Dba::insert('weixin_fans_xiaochengxu', $insert);
				if(!empty($data['unionId'])) {
					$replace = array('su_uid' => $su_uid, 'union_id' => $data['unionId']);
					Dba::replace('weixin_unionid', $replace);
				}
			} Dba::commit();
		}
		
		$_SESSION['session_key'] = $ret['session_key'];
		$_SESSION['su_login'] = $_SESSION['su_uid'] = $su_uid;
		$ret = array('su_uid' => $su_uid, 'open_id' => $open_id, 'PHPSESSID' => session_id());
		return $ret;
	}
	/*
		获取小程序代码
	*/
	public static function get_code_tpl($id = null) {
		static $code = array('29' => array(
				'template_id' => '29',
				'ext_json' => array('ext' => array()),
				'user_version' => '1.0',
				'user_desc' => '电商版',
				'item_list' => array(
					array(
					'address' => 'page/index/index',
					'tag' => '商城',
					'first_class' => '生活服务',
					'second_class' => '线下超市/便利店',
					'title'=> '首页'
					),
				),
			));

		return $id === null ? $code : (isset($code[$id]) ? $code[$id] : false);
	}

	/*
		获取小程序extjson
	*/
	public static function get_xiaochengxu_ext_json($public_uid) {
		$key = 'xiaochengxu_ext_json_'.$public_uid;
		return isset($GLOBALS['arraydb_weixin_public'][$key]) ? 
				json_decode($GLOBALS['arraydb_weixin_public'][$key], true): array();
	}

	/*
		设置小程序ext_json
		ext数组格式参考文档
		https://developers.weixin.qq.com/miniprogram/dev/devtools/ext.html
	*/
	public static function set_xiaochengxu_ext_json($json, $public_uid) {
		$key = 'xiaochengxu_ext_json_'.$public_uid;
		if(is_array($json)) $json = json_encode($json);
		$GLOBALS['arraydb_weixin_public'][$key] = $json;
		
		return $json;
	}

	/*
		上传
	*/
	public static function upload_xiaochengxu($template_id, $public) {
		if(!$tpl = self::get_code_tpl($template_id)) {
			setLastError(ERROR_OBJECT_NOT_EXIST);
			return false;
		}

		$url = getDomainName();
		$url = 'weixin.uctphp.com';
		$ext = array(
			'server_url'=> 'https://'.$url.'/?_uct_token='.$public['uct_token'].'&',
			'prefix_url'=> 'https://'.$url.'/',
			#'sp_name'=> AccountMod::get_current_service_provider('name'),
			'sp_name'=> $public['public_name'],
			'main_color'=> '#0e90d2', //todo 主色调
		);
		$tpl['ext_json'] = XiaochengxuMod::get_xiaochengxu_ext_json($public['uid']);
		if(!isset($tpl['ext_json']['ext'])) $tpl['ext_json']['ext'] = array();
		$tpl['ext_json']['ext'] = array_merge($tpl['ext_json']['ext'],  $ext);
		$tpl['ext_json']['extAppid'] = $public['app_id'];

		$tpl['ext_json'] = json_encode($tpl['ext_json'], JSON_UNESCAPED_UNICODE);
		unset($tpl['item_list']);

		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}
		$url = 'https://api.weixin.qq.com/wxa/commit?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $tpl);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('upload xiaochengxu failed!: '.$public['public_name'] . $ret2);
			return false;
		}

		$save = array('template_id' => $template_id, 'upload_time' => time());
		XiaochengxuMod::set_audit_id($save, $public['uid'], true);
		return $ret;
	}

	/*
		获取体验二维码
	*/
	public static function get_qrcode($public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}
		$url = 'https://api.weixin.qq.com/wxa/get_qrcode?access_token='.$access_token;

		//304支持
		if (0 && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			header('Cache-Control: public');
			header('Last-Modified:' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
			exit();
		}
		
		header('Cache-Control: public');
		header('Last-Modified: ' . $_SERVER['REQUEST_TIME']);
		header('Content-Type: image/jpeg');
		//这就是1张图 Content-Type: image/jpeg 
		echo file_get_contents($url);
		exit();

		$ret2 = $ret = Weixin::weixin_https_get($url);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('get xiaochengxu qrcode failed!: ' . $ret2);
			return false;
		}

		return $ret;
	}

	/*
		获取小程序每日访问统计数据, 只能查一天
		$option = array(
			begin_date: '20180304'
			end_date: '20180304' //最大值为昨天
		)

		返回
array (
  'list' => 
  array (
    array (
      'ref_date' => '20180303',
session_cnt	打开次数
visit_pv	访问次数
visit_uv	访问人数
visit_uv_new	新用户数
stay_time_uv	人均停留时长 (浮点型，单位：秒)
stay_time_session	次均停留时长 (浮点型，单位：秒)
visit_depth	平均访问深度 (浮点型)
    ),
  ),
)
	*/
	public static function get_summary_day($public, $day) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}
		$url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token='.$access_token;
		$option = array('begin_date' => $day, 'end_date' => $day);

		$ret2 = $ret = Weixin::weixin_https_post($url, $option);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']) ||
			empty($ret['list'][0])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('get xiaochengxu summary failed!: ' . $ret2);
			return false;
		}

		return $ret['list'][0];
	}

	/*
		获取小程序帐号的可选类目
	*/
	public static function get_category($public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}

		$url = 'https://api.weixin.qq.com/wxa/get_category?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_get($url);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('get category xiaochengxu failed!: ' . $ret2);
			return false;
		}

		$cats = array();
		if($ret['category_list'])
		foreach($ret['category_list'] as $c) {
			if(!isset($cats[$c['first_class']])) 
				$cats[$c['first_class']] = array('name' => $c['first_class']);
			if(!isset($cats[$c['first_class']]['nodes'][$c['second_class']])) 
				$cats[$c['first_class']]['nodes'][$c['second_class']] = array('name' => $c['second_class']);
			if(isset($c['third_class'])) {
			if(!isset($cats[$c['first_class']]['nodes'][$c['second_class']]['nodes'][$c['third_class']])) 
				$cats[$c['first_class']]['nodes'][$c['second_class']]['nodes'][$c['third_class']] = array('name' => $c['third_class']);
			}
		}

		return  $cats;
	}

	/*
		获取小程序审核版本id (每次提交审核会返回一个审核id，需要用这个id查询审核的状态)
		array('audit_id' => , 'create_time' => )
	*/
	public static function get_audit_id($public_uid) {
		$key = 'auditid_'.$public_uid;
		return isset($GLOBALS['arraydb_weixin_public'][$key]) ? 
				json_decode($GLOBALS['arraydb_weixin_public'][$key], true): false;
	}

	/*
		保存小程序审核版本id
		@param $audit_id = array(
					'audit_id' => 编号
					'create_time' => 选填
					//'template_id' => 选填
				)

		$param $clear 清空旧的记录
	*/
	public static function set_audit_id($audit_id, $public_uid, $clear = false) {
		$key = 'auditid_'.$public_uid;
		if(!is_array($audit_id)) {
			$audit_id = array('audit_id' => $audit_id);
		}
		if(empty($audit_id['create_time'])) $audit_id['create_time'] = $_SERVER['REQUEST_TIME'];

		if(!$clear && ($old = XiaochengxuMod::get_audit_id($public_uid))) {
			$audit_id = array_merge($old, $audit_id);
		}

		$GLOBALS['arraydb_weixin_public'][$key] = json_encode($audit_id);
		return $audit_id;
	}

	/*
		提交小程序

		全部从template_id里读了
		$data = array(
			'title' '小程序页面标题', 
			'tag' => '小程序标签',
			'first_class' => '生活服务',
			'second_class' => '线下超市/便利店',
			'third_class' => '',
		)

		提交的参数为
		$param = array(
		'item_list' => array(array(
			'title' '小程序页面标题', 
			'tag' => '小程序标签',

			'address' => 'page/index/index',
			'first_class' => '生活服务',
			'second_class' => '线下超市/便利店',
		),
		))
	*/
	public static function audit_xiaochengxu($data, $public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}
		if(!($audit = XiaochengxuMod::get_audit_id($public['uid'])) || empty($audit['template_id']) ||
			!($tpl = XiaochengxuMod::get_code_tpl($audit['template_id']))) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return false;
		}
		
		$post_data = array();
		foreach($tpl['item_list'] as $it) {
			$post_data['item_list'][] = array_merge($it, $data);
		}

		$url = 'https://api.weixin.qq.com/wxa/submit_audit?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $post_data);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']) ||
			empty($ret['auditid'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('audit xiaochengxu failed!: ' . $ret2);
			return false;
		}

		
		$save = array('audit_id' => $ret['auditid']);
		XiaochengxuMod::set_audit_id($save, $public['uid']);
		return $ret;
	}

	/*
		获取小程序审核状态
		返回 array(
			'status' => 2
		)

		status	审核状态，其中0为审核成功，1为审核失败，2为审核中
		reason	当status=1，审核被拒绝时，返回的拒绝原因
	*/
	public static function auditstatus_xiaochengxu($public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}

		if(!($audit_id = XiaochengxuMod::get_audit_id($public['uid'])) ||
			(empty($audit_id['audit_id'])) || !($audit_id = $audit_id['audit_id'])) {
			#setLastError(ERROR_OBJ_NOT_EXIST);
			#return false;
			$new = 1;
			$url = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token='.$access_token;
			$ret2 = $ret  = Weixin::weixin_https_get($url);
		} else {
			$data = array('auditid' => $audit_id);
			$url = 'https://api.weixin.qq.com/wxa/get_auditstatus?access_token='.$access_token;
			$ret2 = $ret  = Weixin::weixin_https_post($url, $data);
		}
		//$url = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token='.$access_token;
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('auditstatus xiaochengxu failed!: ' . $ret2);
			return false;
		}

		if(0&&!empty($new)) {
			//var_export($ret);
			$save = array('audit_id' => $ret['auditid']);
			XiaochengxuMod::set_audit_id($save, $public['uid']);
		}

		return $ret;
	}

	/*
		发布小程序
	*/
	public static function release_xiaochengxu($public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}

		$data = '{}';
		$url = 'https://api.weixin.qq.com/wxa/release?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $data);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('release xiaochengxu failed!: ' . $ret2);
			return false;
		}

		//清除一下
		$save = array('audit_id' => 0, 'release_time' => $_SERVER['REQUEST_TIME']);
		XiaochengxuMod::set_audit_id($save, $public['uid']);
		return $ret;
	}

	/*
		绑定体验者小程序
	*/
	public static function bindtester_xiaochengxu($wechatid, $public) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}

		$data = array('wechatid' => $wechatid);
		$url = 'https://api.weixin.qq.com/wxa/bind_tester?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $data);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('bind tester xiaochengxu failed!: ' . $ret2);
			return false;
		}

		return $ret;
	}

	/*
		自动设置服务器接口域名
		自动取 WEIXIN_REDIRECT_URI 里的域名

		release代码前，要先设置一下
	*/
	public static function auto_domain_xiaochengxu($public, $domain = array()) {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}

		if(!$domain) {
			$auto = parse_url(AccountMod::require_wx_redirect_uri(), PHP_URL_HOST);	
			$domain = array(
							'requestdomain' => array('https://'.$auto),
							'uploaddomain' => array('https://'.$auto),
							'downloaddomain' => array('https://'.$auto),
							'wsrequestdomain' => array('wss://'.$auto),
			);	
		}

		//直接add了 不用先get再set
		$data = array('action' => 'add');
		$data = array_merge($domain, $data);
		$url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token='.$access_token;
		$ret2 = $ret  = Weixin::weixin_https_post($url, $data);
		if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode'])) {
			WeixinMod::refresh_weixin_access_token($public['uid']);
			Weixin::weixin_log('add domain xiaochengxu failed!: ' . $ret2);
			//return false;
		}
		
		return $ret;
	}

	/*
	 * 获取小程序帐号下已存在的模板列表
	 * 注意这里只取了前20条
	 */
    public static function weixin_get_all_private_template($public)
    {
		if(!$access_token = WeixinMod::get_weixin_access_token($public['uid'])) {
			return false;
		}
        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=' . $access_token;
        $ret = self::weixin_https_post($url, array('offset' => 0, 'count' => 20));
        if (!$ret || !($ret = json_decode($ret, true)) || !empty($ret['errcode']))
        {
            $GLOBALS['_TMP']['WEIXIN_ERROR'] = empty($ret['errcode']) ? 'false' : $ret['errcode'];
            setLastError(ERROR_IN_WEIXIN_API);
        }
        return $ret;
	}
	
	/*
		发送小程序模板消息(与公众号模板不同)
		小程序文档 https://mp.weixin.qq.com/debug/wxadoc/dev/api/notice.html#模版消息管理

		$msg = array(
			'touser' => openid
			'template_id' => 模板id
			'form_id' => 必填 表单提交场景下，为submit事件带上的 formId；支付场景下为本次支付的 prepay_id
			'page' => 小程序跳转页面地址 可选支持参数 'index?foo=bar'
			'color' => 默认黑色
			'emphasis_keyword': 'keyword1.DATA'  模板需要放大的关键词

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
		if(!$access_token = WeixinMod::get_weixin_access_token($public_uid)) {
			return false;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;
		$ret = Weixin::weixin_https_post($url, $msg);
		#Weixin::weixin_log('template msg -> '.$ret);
		Weixin::weixin_log('xcx tpl return '.var_export($ret, true));
		if (!$ret || !($ret = json_decode($ret, true)) || empty($ret['msgid'])) {
			//setLastError(ERROR_IN_WEIXIN_API);
			WeixinMod::refresh_weixin_access_token($public_uid);
			return false;
		}

		return $ret['msgid'];
	}

	/*
		保存form id 用于发模板消息
		$it = array(
			su_uid
			form_id
		)
	*/
	public static function save_form_id($it) {
		if(empty($it['create_time'])) $it['create_time'] = $_SERVER['REQUEST_TIME'];
		Dba::insert('form_id_xiaochengxu',$it);
		return Dba::insertID();
	} 

	/*
		取一个form_id 自动删除
	*/
	public static function get_a_form_id($su_uid, $public_uid = 0) {
		if(!$su_uid) return false;
		$time = time() - 86400 * 7;
		$sql = 'select uid, form_id from form_id_xiaochengxu where su_uid = '.$su_uid.
				' && create_time >= '.$time;
		if($public_uid) $sql .= ' && public_uid = '.$public_uid;
		$sql .= ' order by create_time';	
		$ret = Dba::readRowAssoc($sql);
		if(!$ret) {
			return false;
		}
		//用完即删 
		Dba::write('delete from form_id_xiaochengxu where uid = '.$ret['uid']);

		//删除过期的
		$sql = 'delete from form_id_xiaochengxu where su_uid = '.$su_uid.
				' && create_time < '.$time;
		Dba::write($sql);
		return $ret['form_id'];
	} 


}

