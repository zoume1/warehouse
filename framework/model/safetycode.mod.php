<?php
/*
	图片验证码
	短信验证码

	安全密码
*/

class SafetyCodeMod {
	/*
		检查验证码
	*/
	public static function check_verify_code() {
		if(!empty($_REQUEST['verifycode'])) {
			if(empty($_SESSION['verify_code']) ||
				strcasecmp($_SESSION['verify_code'], $_REQUEST['verifycode']) ||
				($_SESSION['verify_code_time'] + 600 < $_SERVER['REQUEST_TIME'])
			) {
				setLastError(ERROR_INVALID_VERIFT_CODE);
				return false;
			}

			return true;
		}

		return (defined('DEBUG_CHECK_CODE') && DEBUG_CHECK_CODE);	
	}

	/*
		生成验证码, 随时刷新
	*/
	public static function get_a_verify_code() {
		$_SESSION['verify_code_time'] = $_SERVER['REQUEST_TIME'];
		return $_SESSION['verify_code'] = substr(md5(uniqid()), 14, 4);
	}

	/*
		检查短信验证码
	*/
	public static function check_mobile_code() {
		if(!empty($_REQUEST['mobilecode'])) {
			if(empty($_SESSION['mobile_code']) ||
				strcasecmp((string)$_SESSION['mobile_code'], $_REQUEST['mobilecode']) ||
				($_SESSION['mobile_code_time'] + 600 < $_SERVER['REQUEST_TIME'])
			) {
				setLastError(ERROR_INVALID_VERIFT_CODE);
				return false;
			}

			if(!empty($_REQUEST['account']) && (!isset($_SESSION['assoc_account']) || $_SESSION['assoc_account'] != $_REQUEST['account'])) {
				setLastError(ERROR_DENYED_FOR_SAFETY);
				return false;
			}
			return true;
		}

		return (defined('DEBUG_CHECK_CODE') && DEBUG_CHECK_CODE);	
	}

	/*
		生成手机短信验证码, 1分钟内只能刷新1次
		如果1分钟内已经生成过,返回false
		正常情况下返回6位数字
	*/
	public static function get_a_mobile_code() {
		if(!empty($_SESSION['mobile_code_time']) && 
			($_SESSION['mobile_code_time'] + 60 >= $_SERVER['REQUEST_TIME'])) {
			return false;
		}

		/*
			可能是account或phone	
		*/
		if(!empty($_REQUEST['account'])) {
			 $_SESSION['assoc_account'] = $_REQUEST['account'];
		}
		if(!empty($_REQUEST['phone'])) {
			 $_SESSION['assoc_account'] = $_REQUEST['phone'];
		}

		$_SESSION['mobile_code_time'] = $_SERVER['REQUEST_TIME'];
		return $_SESSION['mobile_code'] = mt_rand(1000, 9999);
	}

	/*
		获取用户安全密码
	*/
	public static function get_user_safety_passwd($su_uid = 0) {
		if(!$su_uid && !($su_uid = AccountMod::has_su_login())) {
			return false;
		}
		$key = 'safetycode_'.$su_uid;

		return 	$GLOBALS['arraydb_weixin_fan'][$key];
	}

	/*
		设置用户安全密码
	*/
	public static function set_user_safety_passwd($passwd, $su_uid = 0) {
		if(!$su_uid && !($su_uid = AccountMod::has_su_login())) {
			return false;
		}
		if(!self::check_safety_passwd()) {
			return false;
		}

		$key = 'safetycode_'.$su_uid;

		return 	$GLOBALS['arraydb_weixin_fan'][$key] = md5($passwd);
	}

	/*
		检查安全密码
	*/
	public static function check_safety_passwd() {
		if(!empty($_REQUEST['safetypasswd'])) {
			if(($pwd = self::get_user_safety_passwd()) &&
				$pwd != md5($_REQUEST['safetypasswd'])) {
				setLastError(ERROR_INVALID_SAFETY_PASSWD);
				return false;
			}

			return true;
		}

		return (defined('DEBUG_CHECK_CODE') && DEBUG_CHECK_CODE);	
	}

}
