<?php
/*
	商户资源配额
*/

class SpLimitMod {
	/*
		当前商户是否到期
	*/
	public static function is_current_sp_available() {
		$e = self::get_current_sp_limit('expire_time');

		if($e && ($e < $_SERVER['REQUEST_TIME'])) {
			setLastError(ERROR_SERVICE_NOT_AVAILABLE);
			return false;
		}
		return true;
	}

	/*
		获取当前商户配额
	*/
	public static function get_current_sp_limit($field = '', $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return 0;
		}
		if(!isset($GLOBALS['service_provider']['limit'][$sp_uid])) {
			$sql = 'select * from service_provider_limit where sp_uid = '.$sp_uid;
			if(!($GLOBALS['service_provider']['limit'][$sp_uid] = Dba::readRowAssoc($sql))) {
				$insert = array(
					'sp_uid' => $sp_uid,
					'sms_total' => 10,  //送10条短信
					'sms_remain' => 10,
					'excel_total' => 10,  //送10次导出excel
					'excel_remain' => 10,
				);
				Dba::insert('service_provider_limit', $insert);
				$GLOBALS['service_provider']['limit'][$sp_uid] = Dba::readRowAssoc($sql);
			}
		}

		return $field ? (isset($GLOBALS['service_provider']['limit'][$sp_uid][$field]) ? 
								$GLOBALS['service_provider']['limit'][$sp_uid][$field] : 0) 
					: (isset($GLOBALS['service_provider']['limit'][$sp_uid]) ? 
								$GLOBALS['service_provider']['limit'][$sp_uid] : 0); 
	}

	public static function update_current_sp_limit($l, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}
		$ret = Dba::update('service_provider_limit', $l, 'sp_uid = '.$sp_uid);
		$GLOBALS['service_provider']['limit'][$sp_uid] = array_merge($GLOBALS['service_provider']['limit'][$sp_uid], $l);

		return $ret;
	}


	/*
		减少短信数目
	*/
	public static function decrease_current_sms_cnt($cnt = 1, $sp_uid = 0) {
		$l = array('sms_remain' => self::get_current_sp_limit('sms_remain', $sp_uid) - $cnt);
		return self::update_current_sp_limit($l, $sp_uid);
	}

	/*
		增加短信数目
	*/
	public static function increase_current_sms_cnt($cnt = 1, $sp_uid = 0) {
		$l = array('sms_remain' => self::get_current_sp_limit('sms_remain', $sp_uid) + $cnt,
				   'sms_total' => self::get_current_sp_limit('sms_total', $sp_uid) + $cnt,);
		return self::update_current_sp_limit($l, $sp_uid);
	}

	/*
		减少excel数目
	*/
	public static function decrease_current_excel_cnt($cnt = 1, $sp_uid = 0) {
		$l = array('sms_remain' => self::get_current_sp_limit('excel_remain', $sp_uid) - $cnt);
		return self::update_current_sp_limit($l, $sp_uid);
	}

	/*
		增加excel数目
	*/
	public static function increase_current_excel_cnt($cnt = 1, $sp_uid = 0) {
		$l = array('sms_remain' => self::get_current_sp_limit('excel_remain', $sp_uid) + $cnt,
				   'sms_total' => self::get_current_sp_limit('excel_total', $sp_uid) + $cnt,);
		return self::update_current_sp_limit($l, $sp_uid);
	}

	/*
		增加续期时间
		$seconds 秒数 3月, 半年, 1年
	*/
	public static function increase_expire_time($seconds, $sp_uid = 0) {
		$cur = self::get_current_sp_limit('expire_time', $sp_uid);
		if($cur == 0) { //永久
			return true;
		}

		if($cur < $_SERVER['REQUEST_TIME']) {
			$cur = $_SERVER['REQUEST_TIME'];
		}
		$l = array('expire_time' => $cur + $seconds);
		return self::update_current_sp_limit($l, $sp_uid);
	}

	/*
		增加公众号数目
	*/
	public static function increase_current_public_cnt($cnt = 1, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}
		$sql = 'update service_provider set max_public_cnt = max_public_cnt + '.$cnt.' where uid = '.$sp_uid;
		return Dba::write($sql);
	}

	/*
		获取最大公众号数目
	*/
	public static function get_current_max_public_cnt($sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}
		$sql = 'select max_public_cnt from service_provider where uid = '.$sp_uid;
		return Dba::readOne($sql);
	}

	/*
		增加子账号号数目
	*/
	public static function increase_current_subsp_cnt($cnt = 1, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}

		$key = 'ssmc_'.$sp_uid;
		return $GLOBALS['arraydb_sys'][$key] = self::get_current_max_subsp_cnt($sp_uid) + $cnt;
	}

	/*
		获取最大子账号号数目
	*/
	public static function get_current_max_subsp_cnt($sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}
		$key = 'ssmc_'.$sp_uid;
		return isset($GLOBALS['arraydb_sys'][$key]) ? $GLOBALS['arraydb_sys'][$key] : 0;
	}

}

