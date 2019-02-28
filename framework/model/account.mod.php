<?php

class AccountMod {
	public static function func_get_service_user($item) {
		//把密码去掉:)
		unset($item['passwd']);

		$item['ps_uid'] = ps_int(array($item['uid'], $item['create_time']));

		//七位数推荐编码
		$item['recommend_uid'] = $item['uid'];
		for($i = strlen($item['recommend_uid']);$i<7;$i++){
			$item['recommend_uid'] ="0".$item['recommend_uid'];
		}

		return $item;
	}

	public static function func_get_service_provider($item) {
		//把密码去掉:)
		unset($item['passwd']);
//		$item['service_provider_limit'] = SpLimitMod::get_current_sp_limit('',$item['uid']);

		return $item;
	}

	/*
		获取当前服务商信息
	*/
	public static function get_current_service_provider($field = '') {
		return $field ? (isset($GLOBALS['service_provider'][$field]) ? $GLOBALS['service_provider'][$field] : false) 
						: (isset($GLOBALS['service_provider']) ? $GLOBALS['service_provider'] : false); 
	}

	/*
		获取当前用户信息
	*/
	public static function get_current_service_user($field = '') {
		return $field ? (isset($GLOBALS['service_user'][$field]) ? $GLOBALS['service_user'][$field] : false) 
						: (isset($GLOBALS['service_user']) ? $GLOBALS['service_user'] : false); 
	}

	/*
		设置当前服务商信息, 可以直接设置一个数组或者设置一个uid
	*/
	public static function set_current_service_provider($sp) {
		if (is_array($sp)) {
			$GLOBALS['service_provider'] = $sp;	
		}
		else if(is_numeric($sp)) {
			uct_use_app('domain');
			DomainMod::goto_its_top_domain($sp);

			$GLOBALS['service_provider'] =  self::get_service_provider_by_uid($sp);
			/* 
				服务商是否到期 
				如果到期, 那么不允许访问前台页面和微信接口, 但仍然可以进入后台
			*/
			if((!SpLimitMod::is_current_sp_available() || 
				(AccountMod::get_current_service_provider('status') ==2))
				&& ($GLOBALS['_UCT']['APP'] != 'pay') //可以进收银台 
				&& ($GLOBALS['_UCT']['APP'] != 'upload') //允许显示图片
				&& ($GLOBALS['_UCT']['APP'] != 'admin') //管理后台
				) {
				if(uct_is_weixin_page()) {
					Weixin::weixin_reply_txt('服务已到期 - powered by uctphp');
				}
				if(!uct_is_backend_page()) {
					echo '服务已到期 - powered by uctphp';
					exit(1);
				}
			}
		}
	}

	/*
		设置当前用户信息, 可以直接设置一个数组或者设置一个uid
	*/
	public static function set_current_service_user($su) {
		if (is_array($su)) {
			$GLOBALS['service_user'] = $su;	
		}
		else if(is_numeric($su)){
			$GLOBALS['service_user'] =  self::get_service_user_by_uid($su);	
			/*
				todo 检查封号之类的
			*/
		}
	}

	/*
		前端页面获取商户uid
		@param $exit 失败退出
	*/
	public static function require_sp_uid($exit=true) {
		static $cache = 0;
		if($cache) {
			return $cache;
		}
		
		if($cache = requestInt('_sp_uid')) {
			return $cache;
		}
		if($cache = requestInt('sp_uid')) {
			return $cache;
		}

		//再加一个__sp_uid
		if($cache = requestInt('__sp_uid')) {
			setcookie('__sp_uid', $cache, 0, '/');
			return $cache;
		}
		if(isset($_COOKIE['__sp_uid']) && ($cache = checkInt($_COOKIE['__sp_uid']))) {
			return $cache;
		}
	
		if($cache = AccountMod::get_current_service_provider('uid')) {
			return $cache;
		}

		if($exit) {
			//temporary process
			if(!empty($GLOBALS['_UCT']['APP']) && in_array($GLOBALS['_UCT']['APP'], array('dating', 'msms'))) {
				return 31;
			}
			if(!empty($GLOBALS['_UCT']['APP']) && $GLOBALS['_UCT']['APP'] == 'expresstui') {
				return 578;
			}
			echo ('fatal error! require sp_uid failed!'); exit(1);
		}
		return 0;
	}

	/*
		获取微信授权登陆，微信支付 的回调地址
	*/
	public static function require_wx_redirect_uri() {
		$sp_uid = AccountMod::require_sp_uid();
		if($sp_uid == 578) {
			return 'http://www.laila.co';
		}

		return WEIXIN_REDIRECT_URI;
	}

	/*
		获取用户列表 
	*/
	public static function get_service_user_list($option) {
        $join='';
        $sql = 'select service_user.*';
		if(!empty($option['public_uid'])) {
			$sql .= ', weixin_fans.open_id, weixin_fans.has_subscribed';
		}
		if(isset($option['g_uid'])) {
			$sql .= ', groups_users.g_uid';
		}
		if(isset($option['wechat_g_uid'])) {
			$sql .= ', wechat_groups_users.g_uid as wechat_g_uid';
		}
		$sql .= ' from service_user ';
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'service_user.sp_uid='.$option['sp_uid'];
		}

		if(!empty($option['from_su_uid'])) { //一级用户
			$where_arr[] = 'service_user.from_su_uid ='.$option['from_su_uid'];
		} 
		else if(!empty($option['from_su_uid2'])) { //二级用户
			$where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid = '.$option['from_su_uid2'].')';
		}
		else if(!empty($option['from_su_uid3'])) { //三级用户
			$where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid3'].'))';
		}
		else if(!empty($option['from_su_uid4'])) { //四级用户
			$where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid4'].')))';
		}
		else if(!empty($option['from_su_uid5'])) { //五级用户
			$where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid5'].'))))';
		}
        else if(!empty($option['from_su_uid6'])) { //六级用户
                $where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid6'].')))))';
        }
        else if(!empty($option['from_su_uid7'])) { //七级用户
                $where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid7'].'))))))';
        }
        else if(!empty($option['from_su_uid8'])) { //八级用户
                $where_arr[] = 'service_user.from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in(select uid from service_user where from_su_uid in (select uid from service_user where from_su_uid = '.$option['from_su_uid8'].')))))))';
        }



		if(!empty($option['valid_account'])) {
			$where_arr[] = 'service_user.account is not null';
		}


		if(isset($option['status'])) {
			$where_arr[] = 'service_user.status='.$option['status'];
		}
		if(!empty($option['key'])) {
			//搜手机号码
			if(checkString($option['key'], '/^(\d+)$/')) {
				$join .= ' left join service_user_profile on service_user_profile.uid = service_user.uid';
				$where_arr[] = '(service_user.name like "%'.addslashes($option['key'])
							.'%" || service_user.account like "%'.addslashes($option['key']).'%" ||
								service_user_profile.phone like "%'.addslashes($option['key']).'%")';
			} else {
			$where_arr[] = '(service_user.name like "%'.addslashes($option['key'])
							.'%" || service_user.account like "%'.addslashes($option['key']).'%")';
			}
		}
		if(!empty($option['uid'])) {
			$where_arr[] = 'service_user.uid = '.$option['uid'];
		}
        if(!empty($option['public_uid'])) {
            $join .= ' left join weixin_fans on weixin_fans.su_uid = service_user.uid';
            $where_arr[] = ' weixin_fans.public_uid ='.$option['public_uid'];
        }
        if(!empty($join))
        {
            $sql .= $join;
        }
		//查找某个组下的粉丝列表
		if(isset($option['g_uid'])) {
			$sql .= ' left join groups_users on groups_users.su_uid = service_user.uid';
			if(!empty($option['g_uid']))
			$where_arr[] = 'groups_users.g_uid = '.$option['g_uid'];

		}
		//查找某个组下的粉丝列表
		if(isset($option['wechat_g_uid'])) {
			$sql .= ' left join wechat_groups_users on wechat_groups_users.su_uid = service_user.uid';
			if(!empty($option['wechat_g_uid']))
			$where_arr[] = 'wechat_groups_users.g_uid = '.$option['wechat_g_uid'];

		}

		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' && ', $where_arr);
		}
		$sql .= ' order by service_user.uid desc';

		#if(!empty($option['key'])) die($sql);
		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 
				!empty($option['func']) ? $option['func'] : 'AccountMod::func_get_service_user');
	}

	/*
		获取用户资料
		支持批量
	*/
	public static function get_service_user_by_uid($uid) {
		if(is_array($uid)) {
			$in = implode(',', $uid);
			$sql = 'select * from service_user where uid in ('.$in.') order by find_in_set(uid,"'.$in.'")';
			return Dba::readAllAssoc($sql, 'AccountMod::func_get_service_user');
		}
		else {
			return Dba::readRowAssoc('select * from service_user where uid = '.$uid, 'AccountMod::func_get_service_user');
		}
	}

	/*
		如果有union_id 先看看能不能绑定到现有的su上去
	*/
	public static function add_or_edit_service_user($su) {
		if(!empty($su['uid'])) {
			Dba::update('service_user', $su, 'uid = '.$su['uid']);
		}
		else {
			if(!empty($su['union_id'])) {
				if($su_uid = Dba::readOne('select su_uid from weixin_unionid where union_id = "'.
					$su['union_id'].'"')) {
					unset($su['union_id']);
					Dba::update('service_user', $su, 'uid = '.$su_uid);

					return $su_uid;
				}
				else {
					$has_union_id = $su['union_id'];
					unset($su['union_id']);	
				}
			}

			$su['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('service_user', $su);
			$su['uid'] = Dba::insertID();

			if(isset($has_union_id)) {
				Dba::replace('weixin_unionid', array('su_uid' => $su['uid'], 
							'union_id' => $has_union_id));
			}
		}

		return $su['uid'];
	}

	/*
		检查用户是否登陆, 通过$_SESSION['su_login']进行判断
	*/
	public static function has_su_login() {
		if(!empty($_SESSION['su_login']) && !empty($_SESSION['su_uid'])) {
			//检查一下是否属于当前商户
			if(Dba::readOne('select sp_uid from service_user where uid = '.$_SESSION['su_login']) != AccountMod::require_sp_uid(false)) {
				unset($_SESSION['su_login']);	
				unset($_SESSION['su_uid']);	
				return 0;
			}
			

			return $_SESSION['su_login'];
		}

		return 0;
	}

	/*
		检查商户是否登陆, 通过$_SESSION['sp_login']进行判断
	*/
	public static function has_sp_login() {
		if(!empty($_SESSION['sp_login']) && !empty($_SESSION['sp_uid'])) {
			return $_SESSION['sp_login'];
		}

		return 0;
	}

	/*
		检查子账号是否登陆, 通过$_SESSION['subsp_login']进行判断
	*/
	public static function has_subsp_login() {
		if(!empty($_SESSION['subsp_login']) && !empty($_SESSION['subsp_uid'])) {
			return $_SESSION['subsp_login'];
		}

		return 0;
	}

	/*
		获取商户资料
		支持批量
	*/
	public static function get_service_provider_by_uid($uid) {
		if(is_array($uid)) {
			$in = implode(',', $uid);
			$sql = 'select * from service_provider where uid in ('.$in.') order by find_in_set(uid,"'.$in.'")';
			return Dba::readAllAssoc($sql, 'AccountMod::func_get_service_provider');
		}
		else {
			return Dba::readRowAssoc('select * from service_provider where uid = '.$uid, 'AccountMod::func_get_service_provider');
		}
	}

	/*
		获取商户列表
	*/
	public static function get_service_provider_list($option) {
		$sql = 'select * from service_provider ';
		if(!empty($option['uid']))
		{
			$where_arr[] = 'uid = '.$option['uid'];
		}
		if(!empty($option['parent_uid']))
		{
			$where_arr[] = 'parent_uid = '.$option['parent_uid'];
		}
		if(isset($option['status']) && ($option['status'] >= 0)) {
			$where_arr[] = 'status='.$option['status'];
		}
		if(!empty($option['key'])) {
			$where_arr[] = '(name like "%'.addslashes($option['key']).'%") || (account like "%'.addslashes($option['key']).'%")';
		}

		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' && ', $where_arr);
		}
		$sql .= ' order by uid desc';
		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'AccountMod::func_get_service_provider');
	}

	/*
	 * 获取微信粉丝
	 */
	public static function get_weixin_fans_list($option)
	{
		$table = 'weixin_fans';
		if(isset($option['public_uid']) && (8==(8&Dba::readOne('select public_type from weixin_public where uid = '.$option['public_uid'])))) {
			$table = 'weixin_fans_xiaochengxu';
		}

		$join='';
		$sql = 'select  public_uid, open_id, has_subscribed, '.$table.'.last_time as last_msg_time';
		if(!empty($option['sp_uid'])) {
			$sql .=',service_user.* ';
		}
		$sql .= ' from '.$table;

		if(!empty($option['sp_uid'])) {
			$join = ' left join service_user on '.$table.'.su_uid = service_user.uid';
			$where_arr[] = 'service_user.sp_uid='.$option['sp_uid'];
		}
		if (!empty($option['public_uid']))
		{
			$where_arr[] = 'public_uid ='.$option['public_uid'];
		}
		if(!empty($option['has_subscribed']))
		{
			$where_arr[] = 'has_subscribed ='.$option['has_subscribed'];
		}
        if(!empty($option['key'])) {
            $where_arr[] = 'service_user.name like "%'.addslashes($option['key'])
                .'%" || service_user.account like "%'.addslashes($option['key']).'%"';
        }
		if(!empty($join))
		{
			$sql .= $join;
		}
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' && ', $where_arr);
		}
		$sql .= ' order by '.$table.'.last_time desc';
		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'AccountMod::func_get_service_user');
	}



}

