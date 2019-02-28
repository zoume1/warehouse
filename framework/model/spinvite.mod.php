<?php
/*
	商户邀请码

	访问权限设置
*/

class SpInviteMod {
	public static function get_vip_types() {
		return array(0 => '试用版', 
					1  => '基础版', 
					2  => '行业版', 
					3  => '旗舰版', 
					4  => '运营版', 
					5  => '定制版');
	}

	public static function func_get_sp_invite($item) {
		if(!empty($item['allow_plugins'])) $item['allow_plugins'] = json_decode($item['allow_plugins'], true);
		if(!empty($item['free_limit'])) $item['free_limit'] = json_decode($item['free_limit'], true);
		//todo 可以取一下商户信息
		if($item['sp_uid']) $item['sp'] = AccountMod::get_service_provider_by_uid($item['sp_uid']);

		return $item;
	}

	public static function check_invite_code($invitecode, $create = true) {
		if(defined('DEBUG_CHECK_CODE') && DEBUG_CHECK_CODE && $invitecode == '8888') {
			return array('uid' => 0, 'invite_code' => '8888', 'create_time' => 0, 'from_uid' => 0, 'use_time' =>0, 'sp_uid' => 0);
		}

		/*		
			可能是一个微信验证码
		*/
		if(checkString($invitecode, '/^(\d{6})$/')) {
			uct_use_app('wxcode');
			if(WxCodeMod::check_short_code($invitecode)) {
				if(!$create) {
					return true;
				}
				return SpInviteMod::add_a_test_invite_code();
			}
		}
		
		if(!($ic = Dba::readRowAssoc('select * from service_provider_invite where invite_code = "'.addslashes($invitecode).'"', 
										'SpInviteMod::func_get_sp_invite')) ||
			($ic['use_time'] > 0)) {
			setLastError(ERROR_INVALID_INVITE_CODE);
			return false;
		}

		return $ic;
	}

	/*
		邀请码注册
		根据邀请码作些商户资料初始化
	*/
	public static function invalidate_invite_code($ic, $sp) {
		$sql = 'update service_provider_invite set use_time = '.$_SERVER['REQUEST_TIME'].', sp_uid = '.$sp['uid']
			.' where uid = '.$ic['uid'];
		Dba::write($sql);

		//配额初始化
		$insert = array('sp_uid' => $sp['uid']);
		if(!empty($ic['free_limit']['expire'])) {
			$insert['expire_time'] = $_SERVER['REQUEST_TIME'] + $ic['free_limit']['expire'];
		}
		if(!empty($ic['free_limit']['sms'])) {
			$insert['sms_remain'] = $insert['sms_total'] = $ic['free_limit']['sms'];
		}
		if(!empty($ic['free_limit']['excel'])) {
			$insert['excel_remain'] = $insert['excel_total'] = $ic['free_limit']['excel'];
		}
		
		Dba::insert('service_provider_limit', $insert);
	}

	/*
		生成或编辑一个注册邀请码

		返回 array('uid' => xx, 'invite_code' => xxxxxx, ...)
	*/
	public static function add_or_edit_invite_code($ic, $prefix='') {
		if(empty($ic['invite_code']) && empty($ic['uid'])) {
			do {
				$invitecode = md5(uniqid().'nxZ4OHZiiVxsSj');
				if($prefix) $invitecode = $prefix . substr($invitecode, strlen($prefix));
			} while(Dba::readOne('select 1 from service_provider_invite where invite_code = "'.$invitecode.'"'));

			$ic['invite_code'] = $invitecode; 
			$ic['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('service_provider_invite', $ic);
			$ic['uid'] = Dba::insertID();
		}
		else {
			if(!empty($ic['uid'])) {
				$where = 'uid = '.$ic['uid'];
			}
			else if(!empty($ic['invite_code'])) {
				$where = 'invite_code = "'.$ic['invite_code'].'"';
			}
			Dba::update('service_provider_invite', $ic, $where);
		}

		return $ic;
	}

	/*
		批量生成邀请码, 不考虑重复的情况, 可能会出错
	*/
	public static function bat_add_invite_code($ic, $count = 100, $prefix='') {
		$ics = array();	
		for($i = 0; $i < $count; $i++) {
			$ic['invite_code'] = md5(uniqid().'nxZ4OHZiiVxsSj');
			if($prefix) $ic['invite_code'] = $prefix . substr($ic['invite_code'], strlen($prefix));
			$ic['create_time'] = $_SERVER['REQUEST_TIME'];
			$ics[] = $ic;
		}
		Dba::insertS('service_provider_invite', $ics);
	
		return array_column($ics, 'invite_code');
	}

	/*
		生成试用邀请码
	*/
	public static function add_a_test_invite_code() {
		$ic = array(
			'from_uid' => 1, 
			//'sp_uid' => 0, 
			'vip_type' => 0,
			'free_limit' => array('expire' => 86400 * 7 * 1, 'sms' => 0, 'excel' => 0, 'public' => 1),
			'allow_plugins' => array('default','keywords','menu','sendtemplatemsg','templatemsg','templatexcxmsg','welcome','whoami','wxmsg','mass','material','spider','wenku','domain','donate','job','pay','subsp','shop','su', 'site', 'kefu'),
		);
		return self::add_or_edit_invite_code($ic, 'free');
	}

	/*
		获取商户邀请码信息 
	*/
	public static function get_invite_code_by_sp_uid($sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_DBG_STEP_1);
			return false;
		}

		$sql = 'select * from service_provider_invite where sp_uid = '.$sp_uid;
		return Dba::readRowAssoc($sql, 'SpInviteMod::func_get_sp_invite');	
	}

	/*
		获取邀请码信息 
	*/
	public static function get_invite_code_by_uid($uid) {
		$sql = 'select * from service_provider_invite where uid = '.$uid;
		return Dba::readRowAssoc($sql, 'SpInviteMod::func_get_sp_invite');	
	}

	/*
		删除邀请码
		只能删除未使用过的邀请码
		
		返回删除的条数
	*/
	public static function delete_invite_code($uids) {
		if(!is_array($uids)) {
			$uids = array($uids);
		}

		$sql = 'delete from service_provider_invite where uid in ('.implode(',', $uids).') && use_time > 0';
		return Dba::write($sql);
	}

	/*
		开通插件安装权限

		$time 续费多久
	*/
	public static function pay_a_plugin($dir, $sp_uid = 0, $time = 0) {
		if($time) {
			if(!$sp_uid) {
				$public_uid = WeixinMod::get_current_weixin_public('uid');
			} else {
				//xxxxxx
				$public_uid = Dba::readOne('select * from weixin_public where sp_uid = '.$sp_uid.' limit 1');
			}
			
			//续费
			if($old = Dba::readRowAssoc('select * from weixin_plugins_installed where public_uid = '.$public_uid.' && dir = "'.$dir.'"')) {
				if($old['expire_time'] > 0) {
					Dba::write('update weixin_plugins_installed set expire_time = '
						.(max(time(), $old['expire_time']) + $time).' where uid = '.$old['uid']);
				} else {
					//否则已经是个永久有效的
				}
			} else {
			//直接新开
				$plugin = WeixinPlugMod::get_plugin_by_dir($dir);
				$insert =  array(
		   	       'name' => $plugin['name'],
		   	       'dir' => $plugin['dir'], //
       	   	       'processor' => $plugin['processor'],
		            'type' => $plugin['type'],
		            'create_time' => time(),
		            'expire_time' => time() + $time,
		            'enabled' => 2,
		            'trigger_mode' => $plugin['trigger_mode'],
                     
					'public_uid' => $public_uid,
       	   	        'keywords' => $plugin['keywords'],
    	   	      );
				Dba::insert('weixin_plugins_installed', $insert);
				$uid = Dba::insertID();
			}
			return;
		}
		//end of set time

		if(!($ic = self::get_invite_code_by_sp_uid($sp_uid)) || empty($ic['allow_plugins']) ||
			in_array($dir, $ic['allow_plugins'])) {
			return true;
		}
		
		$ic['allow_plugins'][] = $dir;
		$up = array('allow_plugins' => $ic['allow_plugins']);
		return Dba::update('service_provider_invite', $up, 'uid = '.$ic['uid']);
	}

	/*
		判断是否允许安装某个插件
	*/
	public static function can_sp_install_plugin($dir, $sp_uid = 0) {
		//默认允许安装 营销插件
		/*
		if(!strncmp('shop', $dir, 4)) {
			return true;
		} */
		

		if(!($ic = self::get_invite_code_by_sp_uid($sp_uid)) || empty($ic['allow_plugins'])) {
			//标记为hide的插件默认不允许安装
			if(!($plugin = WeixinPlugMod::get_plugin_by_dir($dir)) ||
				(!empty($plugin['hide']) && strncmp('shop', $dir, 4))) {
				return false;
			}

			return true;
		}

		return in_array($dir, $ic['allow_plugins']);
	}

	/*
		邀请码列表
	*/
	public static function get_invite_list($option) {
		$sql = 'select * from service_provider_invite';
		if(!empty($option['from_uid'])) {
			$where_arr[] = 'from_uid = '.$option['from_uid'];
		}
		if(!empty($option['vip_type'])) {
			$where_arr[] = 'vip_type = '.$option['vip_type'];
		}
		//未使用
		if(!empty($option['unused_only'])) {
			$where_arr[] = 'use_time = 0';	
		}
		if(!empty($option['key'])) {
			$where_arr[] = '(invite_code like "%'.addslashes($option['key']).'%" || (sp_uid>0 &&
				(select 1 from service_provider where service_provider.uid = service_provider_invite.sp_uid && (service_provider.name like "%'
				.addslashes($option['key']).'%" || service_provider.account like "%'.addslashes($option['key']).'%"))))';
		}
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}
		$sql .= ' order by uid desc';

		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'SpInviteMod::func_get_sp_invite');
	}

	/*
		只读模式 子账号
	*/
	public static function is_current_sp_readonly() {
		if($subsp_uid = AccountMod::has_subsp_login()) {
			$sp_uid = AccountMod::has_sp_login();
			if(($a=self::get_access('access_'.$sp_uid.'_'.$subsp_uid)) && isset($a['*']) && ($a['*'] == 2)) {
				return true;
			}
		}

		return (($a=self::get_access()) && isset($a['*']) && ($a['*'] == 2)); 
	}

	public static function get_access($key='') {
		!$key && $key = 'access_'.AccountMod::get_current_service_provider('uid');
		if(!isset($GLOBALS['arraydb_sys'][$key]) ||
			!($access = json_decode($GLOBALS['arraydb_sys'][$key], true)) 
			) {
			return false;
		}

		return $access;
	}

	/*
		设置后台访问权限
		$access = array(
			'*' => 1, //默认权限, 0禁止访问, 1允许访问,  2 只读模式([GET] *.api.*)
			'sp.*' => 1,
			'sp.index.*' => 0,
			'sp.index.publiclist' => 1,
		)
	*/
	public static function set_access($access, $key='') {
		!$key && $key = 'access_'.AccountMod::get_current_service_provider('uid');
		if(is_array($access)) $access = json_encode($access);
		return $GLOBALS['arraydb_sys'][$key] = $access;	
	}

	/*
		检查当前账号访问权限	
		支持子账号的情况
	*/
	public static function check_current_sp_access($url = array()) {
		$sp_uid = AccountMod::has_sp_login();
		$subsp_uid = AccountMod::has_subsp_login();

		$sa = $subsp_uid ? self::check_access($url, 'access_'.$sp_uid.'_'.$subsp_uid) : 1;
		$a = self::check_access($url, 'check_'.$sp_uid);

		/*
		$table = array(
			'1'	 => array('1' => 1,  '2' => 2,  '10' => 10),
			'2'	 => array('1' => 2,  '2' => 2,  '10' => 10),
			'10' => array('1' => 10, '2' => 10, '10' => 10),
		);
		*/
		
		if(!$a || !$sa) {
			return 0;
		}
		if($a > 1) {
			return $a;
		}
		return max($a, $sa);
	}

	/*
		检查用户的访问权限
		依次检查规则 $app.$ctl.$act, $app.$ctl, $app, 

		返回 1 允许访问
			 2 只读模式
			 10  特例允许访问(后台菜单强制显示)
		 或 0 禁止访问
	*/
	public static function check_access($url = array(), $key='') {
		$app = isset($url['app']) ? $url['app'] : $GLOBALS['_UCT']['APP'];
		$ctl = isset($url['ctl']) ? $url['ctl'] : $GLOBALS['_UCT']['CTL'];
		$act = isset($url['act']) ? $url['act'] : $GLOBALS['_UCT']['ACT'];

		//默认情况下, 已安装的插件允许访问,  未安装的禁止访问
		if(!($access = self::get_access($key))) {
			//到期的不给访问
			#return WeixinPlugMod::is_plugin_installed_to_sp_uid($app) ? 1 : 0;
			return WeixinPlugMod::check_current_plugin_access($app) ? 1 : 0;
					
		}	


		$rule = $app.'.'.$ctl.'.'.$act;
		if(isset($access[$rule])) {
			return $access[$rule];
		}

		$rule = $app.'.'.$ctl.'.*';
		if(isset($access[$rule])) {
			return $access[$rule];
		}

		$rule = $app.'.*';
		if(isset($access[$rule])) {
			return $access[$rule];
		}

		$rule = '*';
		if(isset($access[$rule])) {
			//只读模式
			if($access[$rule] == 2) {
				//翻页只能翻2页
				if((requestInt('page') > 1) || (requestInt('limit') > 20) || (requestInt('limit') < 0)) {
					return 0;
				}

				return ($_SERVER['REQUEST_METHOD'] == 'GET') && ($GLOBALS['_UCT']['CTL'] != 'api');
			}

			return $access[$rule];
		}

		//默认情况下, 已安装的插件允许访问,  未安装的禁止访问
		#return WeixinPlugMod::is_plugin_installed_to_sp_uid($app) ? 1 : 0;
		return WeixinPlugMod::check_current_plugin_access($app) ? 1 : 0;
	}

}

