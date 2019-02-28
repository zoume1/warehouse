<?php


class WeixinPlugMod {
	public static function func_get_plugins($item) {
		if($item['trigger_mode'] != 11) {
			$item['keywords'] = explode(',', $item['keywords']);
		}
		return $item;
	}

	protected static $plugins_all = array();

	public static function get_weixin_plugin_cats() {
		/*
			插件分类
		*/
		$cats = array(
			'basic'    => array('name' => '微信',   'icon' => 'am-icon-puzzle-piece'), 
			'content'  => array('name' => '内容',     'icon' => 'am-icon-file-text-o'),
			'advance'  => array('name' => '高级',     'icon' => 'am-icon-cube'), 
			'activity' => array('name' => '活动',     'icon' => 'am-icon-gear'), 
			'hardware' => array('name' => '智能硬件', 'icon' => 'am-icon-wifi'), 
			'industry' => array('name' => '行业',     'icon' => 'am-icon-gears'), 
			'tool'     => array('name' => '工具',     'icon' => 'am-icon-plug'), 
			'game'     => array('name' => '游戏',     'icon' => 'am-icon-gamepad'), 
			'other'    => array('name' => '其他',     'icon' => 'am-icon-cubes'), 
		);

		return $cats;
	}

	/*
		插件是否可用
	*/
	public static function is_plugin_available($p) {
		return 
				(($p['enabled'] > 0) &&             //启用
				(($p['try_time'] > 0 && $p['try_time'] > $_SERVER['REQUEST_TIME']) ||  //在试用期或者未到期
				 ($p['expire_time'] == 0 || $p['expire_time'] > $_SERVER['REQUEST_TIME'])) &&
				 ($p['count_all'] < 0 || $p['count_all'] > $p['count_used'])   //次数未用完
				); 
	}

	/*
		插件是否可以删除
	*/
	public static function can_plugin_be_removed($p) {
//		return false;
		//basic类型的插件不能删除
		return  !in_array($p['type'], array('basic'));
	}

	/*
	 * 删除插件
	 */
	public static function del_weixin_public_plugi($uid){
		if(!is_numeric($uid)) {
			if($p = self::is_plugin_installed($uid)) {
				$uid = $p['uid'];
			}
			else {
				return false;
			}
		}
		Dba::write('delete from weixin_plugins_installed where uid = ' . $uid);

		return true;
	}

	/*
		判断商户是否安装了某插件

		todo 如果已经安装了mooncake, 那么game也认为已安装
	*/
	public static function is_plugin_installed_to_sp_uid($dir, $sp_uid = 0) {
		if(in_array($dir, array('sp', 'web', 'upload'))) {
			return true;
		}

		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}

		$sql = 'select 1 from weixin_plugins_installed where public_uid in (select uid from weixin_public where sp_uid = '
				.$sp_uid.') && dir = "'.addslashes($dir).'" limit 1';
		return Dba::readOne($sql);
	}

	/*
		判断公众号是否安装了某插件

		@param $plugin 插件id或插件dir
		@param $public_uid  公众号uid

		@return 返回插件数组, 或false
	*/
	public static function is_plugin_installed($plugin, $public_uid = 0) {
		$plugs = self::get_weixin_public_plugins_all($public_uid);
		if(is_array($plugin)) {
			$plugin = $plugin['dir'];
		}
		$seacrh = is_numeric($plugin) ? 'uid' : 'dir';
		foreach($plugs as $p) {
			if($p[$seacrh] == $plugin) {
				return $p;
			}
		}

		return false;
	}

	/*
		获取公众号可用且trigger_mode > 0 插件列表

		@param $uid 公众号uid 默认为当前公众号
	*/
	public static function get_weixin_public_plugins_available($uid = 0) {
		$plugs = self::get_weixin_public_plugins_all($uid);
		$ret = array();
		foreach($plugs as $p) {
			if(self::is_plugin_available($p) && ($p['trigger_mode'] > 0)) {
				$ret[] = $p;
			}
		}

		return $ret;
	}

	/*
		根据用户发送内容获取插件

		@param $uid 公众号uid 默认为当前公众号
		@param $content 
	*/
	public static function get_weixin_public_plugins_by_content($uid = 0, $content) {
		$plugs = self::get_weixin_public_plugins_all($uid);
		foreach($plugs as $p) {
			if(!self::is_plugin_available($p)) {
				continue;
			}

			//关键词触发
			if(($p['trigger_mode'] == 10 || $p['trigger_mode'] == 1) && $p['keywords']) {
				foreach($p['keywords'] as $k) {
					if ($k && stripos($content, $k) !== false) {
						return $p;
					}
				}
			}

			//正则表达式触发
			if(($p['trigger_mode'] == 11) && $p['keywords']) {
				if(preg_match($p['keywords'], $content)) {
					return $p;
				}
			}
		}

		return false;
	}

	public static function get_weixin_public_plugins_by_uct_token($uct_token) {
		if(!($weixin_uid = Dba::readOne('select uid from weixin_public where uct_token = "'.$uct_token.'"'))) {
			return array();
		}

		return self::get_weixin_public_plugins_all($weixin_uid);
	}

	/*
		获取公众号插件列表

		@param $uid 公众号uid 默认为当前公众号
	*/
	public static function get_weixin_public_plugins_all($uid = 0) {
		if(!$uid) {
			$uid =  WeixinMod::get_current_weixin_public('uid');
		}

		if(isset(self::$plugins_all[$uid])) {
			return self::$plugins_all[$uid];
		}

		$sql = 'select * from weixin_plugins_installed where public_uid = '.$uid;

		$sp_uid = Dba::readOne('select sp_uid from weixin_public where uid = '.$uid);
		$uids = Dba::readAllOne('select uid from weixin_public where sp_uid = '.$sp_uid);
		if(count($uids) > 1) {
			$sql .= ' || (public_uid in ('.implode(',', $uids).') && type != "basic")';
		}

		$sql .= ' order by create_time desc';

		$plugs = Dba::readAllAssoc($sql, 'WeixinPlugMod::func_get_plugins');
		if(!$plugs) {
			$plugs = array(
		           array(
		   	       'name' => '欢迎语',
		   	       'dir' => 'welcome',
       	   	       'processor' => 'Welcome_WxPlugMod',
		            'type' => 'basic',
		            'create_time' => $_SERVER['REQUEST_TIME'],
		            'enabled' => 2,
		            'trigger_mode' => 1,
                     
					'public_uid' => $uid,
       	   	        'keywords' => '欢迎',
    	   	      ),
                     
		           array(
		   	       'name' => '默认回复',
		   	       'dir' => 'default',
       	   	       'processor' => 'Default_WxPlugMod',
		            'type' => 'basic',
		            'create_time' => $_SERVER['REQUEST_TIME'],
		            'enabled' => 2,
		            'trigger_mode' => 1,
                     
					'public_uid' => $uid,
       	   	        'keywords' => '',
    	   	      ),

		           array(
		   	       'name' => '群发素材',
		   	       'dir' => 'material',
       	   	       'processor' => '',
		            'type' => 'basic',
		            'create_time' => $_SERVER['REQUEST_TIME'],
		            'enabled' => 2,
		            'trigger_mode' => 0,
                     
					'public_uid' => $uid,
       	   	        'keywords' => '',
    	   	      ),

		           array(
		   	       'name' => '高级群发',
		   	       'dir' => 'mass',
       	   	       'processor' => '',
		            'type' => 'basic',
		            'create_time' => $_SERVER['REQUEST_TIME'],
		            'enabled' => 2,
		            'trigger_mode' => 1,
                     
					'public_uid' => $uid,
       	   	        'keywords' => '',
    	   	      ),

		           array(
			           'name' => '自定义回复',
			           'dir' => 'keywords',
			           'processor' => 'Keywords_WxPlugMod',
			           'type' => 'basic',
			           'create_time' => $_SERVER['REQUEST_TIME'],
			           'enabled' => 2,
			           'trigger_mode' => 1,

			           'public_uid' => $uid,
			           'keywords' => '',
		           ),

		           array(
			           'name' => '自定义菜单',
			           'dir' => 'menu',
			           'processor' => '',
			           'type' => 'basic',
			           'create_time' => $_SERVER['REQUEST_TIME'],
			           'enabled' => 2,
			           'trigger_mode' => 1,

			           'public_uid' => $uid,
			           'keywords' => '',
		           ),

		           array(
			           'name' => '微信验证码',
			           'dir' => 'wxcode',
			           'processor' => 'Wxcode_WxPlugMod',
			           'type' => 'advance',
			           'create_time' => $_SERVER['REQUEST_TIME'],
			           'enabled' => 2,
			           'trigger_mode' => 1,

			           'public_uid' => $uid,
			           'keywords' => '',
		           ),
                   array(
                       'name' => '用户管理',
                       'dir' => 'su',
                       'processor' => '',
                       'type' => 'tool',
                       'create_time' => $_SERVER['REQUEST_TIME'],
                       'enabled' => 2,
                       'trigger_mode' => 1,

                       'public_uid' => $uid,
                       'keywords' => '',
                   ),
                   array(
                       'name' => '客服',
                       'dir' => 'kefu',
                       'processor' => '',
                       'type' => 'basic',
                       'create_time' => $_SERVER['REQUEST_TIME'],
                       'enabled' => 2,
                       'trigger_mode' => 1,

                       'public_uid' => $uid,
                       'keywords' => '',
                   ),

			);
			$sqlw = Dba::makeInsertS('weixin_plugins_installed', $plugs);
			Dba::write($sqlw);

			$plugs = Dba::readAllAssoc($sql, 'WeixinPlugMod::func_get_plugins');
		}

		return self::$plugins_all[$uid] = $plugs;
	}

	/*
		获取用户符合条件的插件列表
	*/
	public static function get_weixin_public_plugins_option($uid = 0, $option = array()) {
		$all = self::get_weixin_public_plugins_all($uid);
		if(!empty($option['cat']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return $i['type'] == $option['cat'];	
			});	
		}
		if(!empty($option['key']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return stripos($i['name'], $option['key']) !== false;	
			});	
		}
		//需要配置关键字的
		if(!empty($option['config_keywords']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return $i['trigger_mode'] > 0;
			});	
		}

		$cnt = count($all);
		if($option['limit'] >= 0) {
			$all = array_slice($all, $option['page']*$option['limit'], $option['limit']);
		}
		array_walk($all, function(&$i){
			$i['available'] = WeixinPlugMod::is_plugin_available($i);
			$i['can_remove'] = WeixinPlugMod::can_plugin_be_removed($i);
		});
		return array('count' => $cnt, 'list' => $all);
	}

	/*
		更新已安装的插件
		关键字，是否禁用等

		@param $uid 插件uid, 也可以是dir名称	
	*/
	public static function update_weixin_public_plugin_installed($uid, $update) {
		if(!is_numeric($uid)) {
			if($p = self::is_plugin_installed($uid)) {
				$uid = $p['uid'];
			}
			else {
				return false;
			}
		}
		Dba::update('weixin_plugins_installed', $update, 'uid = '.$uid);
		
		return true;
	}

	/*
		获取商城插件列表

		从本地目录获取
	*/
	public static function get_store_plugins_list($uid = 0, $option = array()) {
		$dir = UCT_PATH.'app'.DS;
		$ds = scandir($dir);
		$ds = array_diff($ds, array('.', '..', 'web', 'sp', 'upload'));
		$all = array();
		foreach($ds as $d) {
			if(!is_dir($dir.$d) || !file_exists($dir.$d.DS.'pluginfo.php')) {
				continue;
			}
			$p = include $dir.$d.DS.'pluginfo.php';
			if(!is_array($p)) {
				continue;
			}
			if(empty($_REQUEST['_d']) && !empty($p['hide'])) {
				continue;
			}
			$p['dir'] = $d;
			$all[] = $p;
		}
		//var_export($ds);
		//var_export($all);


		if(!empty($option['cat']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return $i['type'] == $option['cat'];	
			});	
		}
		if(!empty($option['key']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return stripos($i['name'], $option['key']) !== false;	
			});	
		}

		$cnt = count($all);

		
		uct_use_app('sp');
		$ii=0;
		$has_installed_count=0;
		
		if(SpMod::has_weixin_public_set())
		{
			array_walk($all, function(&$i) use(&$has_installed_count,$option) {
				$i['has_installed'] = WeixinPlugMod::is_plugin_installed($i);
				$i['has_installed'] && $has_installed_count=$has_installed_count+1;
				!isset($option['installed']) && $option['installed'] = 0;
				switch ($option['installed'])//0 全部 1只安装 2 未安装
				{
					case '1':
						if(!$i['has_installed']) $i=null;
						break;
					case '2':
						if($i['has_installed']) $i=null;
						break;
				}
				//$i['can_remove'] = self::can_plugin_be_removed($i);
			});
		}
		$all = array_filter($all);
		if($option['limit'] >= 0) {
			$all = array_slice($all, $option['page']*$option['limit'], $option['limit']);
		}

		return array('count' => $cnt, 'has_installed_count'=>$has_installed_count,'list' => $all);
	}

	/*
		根据目录名获取插件信息
	*/
	public static function get_plugin_by_dir($dir) {
		$fd = UCT_PATH.'app'.DS.$dir;
		if(!is_dir($fd) || !file_exists($fd.DS.'pluginfo.php')) {
			return false;
		}

		$p = include $fd.DS.'pluginfo.php';
		$p['dir'] = $dir;
		$p['has_installed'] = self::is_plugin_installed($p);

		return $p;
	}

	/*
		从目录下安装插件
	*/
	public static function install_a_plugin($plugin, $uid = 0) {
		if(!$uid) {
			$uid =  WeixinMod::get_current_weixin_public('uid');
		}
		
		if($p = self::is_plugin_installed($plugin, $uid)) {
			setLastError(ERROR_OBJECT_ALREADY_EXIST);
			return $p['uid'];
		}

		if(!SpInviteMod::can_sp_install_plugin($plugin['dir'])) {
			setLastError(ERROR_PERMISSION_DENIED);
			return false;
		}

		$insert =  array(
		   	       'name' => $plugin['name'],
		   	       'dir' => $plugin['dir'], //
       	   	       'processor' => $plugin['processor'],
		            'type' => $plugin['type'],
		            'create_time' => $_SERVER['REQUEST_TIME'],
		            'enabled' => 2,
		            'trigger_mode' => $plugin['trigger_mode'],
                     
					'public_uid' => $uid,
       	   	        'keywords' => $plugin['keywords'],
    	   	      );
		Dba::insert('weixin_plugins_installed', $insert);
		$uid = Dba::insertID();

		return $uid;
	}

	/*
		当前公众号插件使用次数+1
	*/
	public static function increase_current_plugin_used_cnt($dir) {
		$sql = 'update weixin_plugins_installed set count_used = count_used + 1 where public_uid = '.
				WeixinMod::get_current_weixin_public('uid').' && dir = "'.addslashes($dir).'"';
		Dba::write($sql);
	}

	/*
	 * 编辑插件说明
	 */
	public static function add_or_edit_weixin_plugins_explain($weixin_plugin_explain)
	{
		if(!empty($weixin_plugin_explain['uid']))
		{
			Dba::update('weixin_plugins_explain',$weixin_plugin_explain,'uid ='.$weixin_plugin_explain['uid']);
		}
		else
		{
			Dba::insert('weixin_plugins_explain',$weixin_plugin_explain);
			$weixin_plugin_explain['uid'] = Dba::insertID();
		}
		return $weixin_plugin_explain['uid'];
	}

	/*
	 * 获取插件说明
	 */
	public static function get_weixin_plugins_explain_by_uid($uid)
	{
		return Dba::readRowAssoc('select * from weixin_plugins_explain where uid = '.$uid,'WeixinPlugMod::func_get_weixin_plugins__explain');
	}

	public static function get_weixin_pligins_explain_by_dir($dir)
	{
		return Dba::readRowAssoc('select * from weixin_plugins_explain where dir = "'.$dir.'"','WeixinPlugMod::func_get_weixin_plugins__explain');
	}
	/*
	 * 获取插件说明列表
	 */
	public static function get_weixin_plugins_explain_list($option)
	{
		$sql = 'select * from weixin_plugins_explain ';
		if (!empty($option['name']))
		{
			$where_arr[] = 'name ="'.$option['name'].'"';
		}
		if (!empty($option['type']))
		{
			$where_arr[] = 'type ="'.$option['type'].'"';
		}
		if (!empty($option['dir']))
		{
			$where_arr[] = 'dir ="'.$option['dir'].'"';
		}
		if (!empty($option['processor']))
		{
			$where_arr[] = 'processor ="'.$option['processor'].'"';
		}
		if (!empty($where_arr))
		{
			$sql .= ' where ' . implode(' and ', $where_arr);
		}
		$sql .= ' order by uid desc';
		$option['page']  = isset($option['page']) ? $option['page'] : 0;
		$option['limit'] = isset($option['limit']) ? $option['limit'] : 10;
		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'WeixinPlugMod::func_get_weixin_plugins__explain');
	}

	public static function func_get_weixin_plugins__explain($item)
	{
		if(!empty($item['content']))
		{
//			$item['content'] = XssHtml::clean_xss($item['content']);
			$item['content'] = htmlspecialchars($item['content']);
		}
		return $item;
	}

	/*
		根据商户安装的插件确定服务版本	

			行业型 安装了行业类industry插件(微官网site除外)
			营销型 安装了活动类activity插件(或su插件)
			展示型 

		@return 展示型 营销型 行业型
	*/
	public static function get_sp_service_type($sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return '';
		}

		$type_name = array(
			'1' => '展示型',
			'2' => '营销型',
			'3' => '行业型',
		);

		$type = 1;
		$sql = 'select name, dir, type from weixin_plugins_installed where public_uid in (select uid from weixin_public where sp_uid = '
				.$sp_uid.') && type != "basic" ';
		$ps = Dba::readAllAssoc($sql);
		if($ps)
		foreach($ps as $p) {
			if($p['type'] == 'industry' && $p['dir'] != 'site') {
				$type = 3;
				break;
			}
			if($p['type'] == 'activity' || $p['dir'] == 'su') {
				$type = 2;
			}
		}

		return $type_name[$type];
	}

	/*
		判断是否有访问权限， 检查了插件到期和禁用的情况
	*/
	public static function check_current_plugin_access($app, $sp_uid = 0) {
		if(in_array($app, array('sp', 'web', 'upload'))) {
			return true;
		}
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			return false;
		}
		$p = Dba::readRowAssoc('select * from weixin_plugins_installed where public_uid in (select uid from weixin_public where sp_uid = '
				.$sp_uid.') && dir = "'.addslashes($app).'" limit 1');
		
		return $p && WeixinPlugMod::is_plugin_available($p);	
	}

}

