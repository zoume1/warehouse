<?php
require UCT_PATH . 'framework/config.php';
require UCT_PATH . 'framework/function.php';
/*
主函数
*/
function uct_run() {
	spl_autoload_register('uct_autoload');
	date_default_timezone_set('Asia/Shanghai');
	if(!isset($_SERVER['REQUEST_TIME'])) $_SERVER['REQUEST_TIME'] = time();
	if(!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = 'unknown';
	if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'weixin.uctphp.com';
	
	/*	命令行下直接返回作为include使用 
		还是初始化一下全局变量, 某些job里会用到
	*/
	if (PHP_SAPI == 'cli' || ($_SERVER['PHP_SELF'] != '/index.php' && empty($_REQUEST['_a']))) {
		uct_init_arraydb();
		$GLOBALS['_UCT'] = array('APP' => '', 'CTL' => '', 'ACT' => '');

		return;
	}

	//url重写模式
	if (!empty($_REQUEST['_rewrite']) && is_string($_REQUEST['_rewrite'])) {
		//1.支持apache 重写模式下?后的参数缺失的情况 
		if(stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') === false) {
			$_REQUEST['_rewrite'] = urldecode(substr($_SERVER['QUERY_STRING'], strlen('_rewrite=')));
		}
		//2. 丢弃_rewrite中的后缀名
		$rewrite = substr($_REQUEST['_rewrite'], 0, strrpos($_REQUEST['_rewrite'], '.'));

		//3. 支持/作为分隔符
		$sp = '.';
		for($i = 0; $i < strlen($rewrite); $i++) {
			if(in_array($rewrite[$i], array('.', '/'))) {
				$sp = $rewrite[$i];
				break;
			}
		}
		$rewrite = explode($sp, $rewrite, 4);

		//最后1段是必填后缀名
		switch(count($rewrite)) {
			case 3:
			case 4: {
				$_GET['_a'] = $_REQUEST['_a'] = $rewrite[0];
				$_GET['_u'] = $_REQUEST['_u'] = $rewrite[1].'.'.$rewrite[2];
				if(!empty($rewrite[3])) {
					if(strpos($rewrite[3], '/')) {
						$params = explode('/', $rewrite[3]);
						for($i=0; $i+1<count($params); $i+=2) {
							$_REQUEST[urldecode($params[$i])] = urldecode($params[$i+1]);
						}
					}
					else if(strpos($rewrite[3], '.')) { //rewrite.web.component.uricallbcak.sp_uid=1.ap_uid=0.php
						$params = explode('.', $rewrite[3]);
						for($i=0; $i+1<count($params); $i++) {
							list($k, $v) = explode('=', $params[$i], 2);	
							$_REQUEST[urldecode($k)] = urldecode($v);
						}
					}
					else {
						foreach(explode('&', $rewrite[3]) as $p) {
							list($k, $v) = explode('=', $p, 2);	
							$_REQUEST[urldecode($k)] = urldecode($v);
						}
					}
				}
				break;
			}
			case 2:
				$_GET['_a'] = $_REQUEST['_a'] = $rewrite[0];
				$_GET['_u'] = $_REQUEST['_u'] = $rewrite[1];
				break;
			case 1: 
				$_GET['_a'] = $_REQUEST['_a'] = $rewrite[0];
				break;
			default:
				break;
		}
	}
	
	//easy 模式直接访问模板tpl
	if (!empty($_REQUEST['_easy']) && is_string($_REQUEST['_easy'])) {
		$easy = explode('.', $_REQUEST['_easy']);
		switch (count($easy)) {
			case 4:
				$_GET['_u'] = $_REQUEST['_u'] = $easy[2] . '.' . $easy[3];
				if (preg_match('/^[\w\.]+$/', $easy[1])) {
					$GLOBALS['_UCT']['TPL'] = $easy[1];
				}
				$_GET['_a'] = $_REQUEST['_a'] = $easy[0];
				break;
			case 3:
				$_GET['_u'] = $_REQUEST['_u'] = $easy[1] . '.' . $easy[2];
				$_GET['_a'] = $_REQUEST['_a'] = $easy[0];
				break;
			case 2:
				$_GET['_u'] = $_REQUEST['_u'] = $easy[1];
				$_GET['_a'] = $_REQUEST['_a'] = $easy[0];
				break;
			case 1:
				$_GET['_a'] = $_REQUEST['_a'] = $easy[0];
				break;
			default:
				exit('invalid _easy param! ' . htmlspecialchars($_REQUEST['_easy']));
		}
	}
	
	/*
	获取app
	*/
	$a = (!empty($_REQUEST['_a']) && is_string($_REQUEST['_a'])) ? $_REQUEST['_a'] : DEFAULT_APP;
	if (!preg_match('/^[\w\.]+$/', $a)) {
		exit('invalid _app name! ' . htmlspecialchars($a));
	}
	$GLOBALS['_UCT']['APP'] = !empty($a) ? strtolower($a) : DEFAULT_APP;
	
	$u = (!empty($_REQUEST['_u']) && is_string($_REQUEST['_u'])) ? $_REQUEST['_u'] : 'index.index';
	if (!preg_match('/^[\w\.]+$/', $u)) {
		exit('invalid _url name! ' . htmlspecialchars($u));
	}
	$u                      = explode('.', $u, 2);
	$GLOBALS['_UCT']['CTL'] = !empty($u['0']) ? strtolower($u['0']) : 'index';
	$GLOBALS['_UCT']['ACT'] = !empty($u['1']) ? strtolower($u['1']) : 'index';
	
	//查看是否有域名绑定
	{
		uct_use_app('domain');
		Event::addHandler('BeforeRun', array('DomainMod','checkDomainBind'));
		Event::addHandler('BeforeRun', array('DomainMod','checkAppMirror'));
	}
	Event::handle('BeforeRun');

	uct_start_session();
	uct_init_plugins();
	
	//重新包含一下domain ctl
	if($GLOBALS['_UCT']['APP'] == 'domain') {
		unset($GLOBALS['_UCT']['autoload'][array_search('domain', $GLOBALS['_UCT']['autoload'])]);	
	}

	uct_use_app($GLOBALS['_UCT']['APP']);
	$ctl = $GLOBALS['_UCT']['CTL'] . 'Ctl';
	$obj = new $ctl;
	
	if (!is_callable(array($obj,$GLOBALS['_UCT']['ACT'])) && empty($_REQUEST['_easy'])) {
		exit('method not exist! [' . $GLOBALS['_UCT']['APP'] . ']  ' . $GLOBALS['_UCT']['CTL'] . 'Ctl::' . $GLOBALS['_UCT']['ACT']);
	}
	
	Event::handle('BeforeAction');
	if (is_callable(array($obj, $GLOBALS['_UCT']['ACT']))) {
		//compatible for php7
		$p7 = $GLOBALS['_UCT']['ACT'];
		$obj->$p7();
	} else {
		//easy mode, render tpl directly
		//pass '_this' to tpl :)
		render_fg('', array('_this' => $obj));
	}
}

/*
自动包含某个app下的类
*/
function uct_use_app($app) {
	if (empty($GLOBALS['_UCT']['autoload'])) {
		$GLOBALS['_UCT']['autoload'] = array(
			$app
		);
		return true;
	}
	if (!in_array($app, $GLOBALS['_UCT']['autoload'])) {
		//$GLOBALS['_UCT']['autoload'][] = $app;	
		array_unshift($GLOBALS['_UCT']['autoload'], $app);
		return true;
	}
	
	return false;
}

/*
自动包含某个vendor下的类
*/
function uct_use_vendor($vendor) {
	if (empty($GLOBALS['_UCT']['autoload_psr'])) {
		$GLOBALS['_UCT']['autoload_psr'] = array(
			$vendor
		);
		return true;
	}
	if (!in_array($vendor, $GLOBALS['_UCT']['autoload_psr'])) {
		//$GLOBALS['_UCT']['autoload_psr'][] = $app;	
		array_unshift($GLOBALS['_UCT']['autoload_psr'], $vendor);
		return true;
	}
	
	return false;
}

/*
uct框架自动加载规则
根据类名包含对应文件

IndexCtl => control/index.ctl.php
IndexMod => model/index.mod.php
Sp_testJob => sp/job/sp_test.job.php

IndexCls => class/index.cls.php
Dba      => class/dba.cls.php

Welcome_WxPlugCtl
Welcome_WxPlugMod
Welcome_WxPlugCls

另外还支持类似Psr-4 Psr-0的方式
*/
function uct_autoload($class_name) {
	$auto_path = array(
		'ctl' => 'control',
		'mod' => 'model',
		'cls' => 'class',
		'job' => 'job',
	);
	$key       = strtolower(substr($class_name, -3));
	if (isset($auto_path[$key])) {
		$dir = $auto_path[$key] . DS . strtolower(substr($class_name, 0, -3)) . '.' . $key . '.php';
	} else {
		$dir = 'class' . DS . strtolower($class_name) . '.cls.php';
	}
	
	//job类型试着自动包含一下
	if($key == 'job' && strpos($class_name, '_')) {
		uct_use_app(strtolower(strstr($class_name, '_', true)));
	}

	if (!empty($GLOBALS['_UCT']['autoload'])) {
		foreach ($GLOBALS['_UCT']['autoload'] as $app) {
			if (file_exists(UCT_PATH . 'app' . DS . $app . DS . $dir)) {
				return include UCT_PATH . 'app' . DS . $app . DS . $dir;
			}
		}
	}
	
	if (file_exists(UCT_PATH . 'framework' . DS . $dir)) {
		return include UCT_PATH . 'framework' . DS . $dir;
	}
	
	//psr
	if (!empty($GLOBALS['_UCT']['autoload_psr'])) {
		//namespace
		if(false !== strpos($class_name, '\\')) {
			$dir = str_replace('\\', DS, ltrim($class_name, '\\')).'.php';
		}
		else {
			$dir = str_replace('_', DS, $class_name).'.php';
		}
		
		foreach ($GLOBALS['_UCT']['autoload_psr'] as $vendor) {
			if(!strncmp($vendor.DS, $dir, strlen($vendor) + 1)) {
				$f = UCT_PATH . 'vendor' . DS . $dir;
			}
			else {
				$f = UCT_PATH . 'vendor' . DS . $vendor . DS . $dir;
			}
			if (file_exists($f)) {
				return include $f;
			}
		}
	}
	
	//加载不存在的类失败时不退出
	if(!empty($GLOBALS['_UCT']['IGNORE_NOT_EXIST_CLASS_ONCE'])) {
		unset($GLOBALS['_UCT']['IGNORE_NOT_EXIST_CLASS_ONCE']);
		return false;
	}
	if(!empty($GLOBALS['_UCT']['USE_OTHER_AUTO_LOADER'])) {
		return false;
	}
	
	echo 'auto_load not found! ' . $class_name;
	exit(1);
}

/*
	判断类是否存在
	会试着自动加载, 没找到返回false
*/
function uct_class_exists($class, $app = '') {
	if($app) uct_use_app($app);
	$GLOBALS['_UCT']['IGNORE_NOT_EXIST_CLASS_ONCE'] = 1;
	return class_exists($class);
}

/*
启动session
todo
*/
function uct_start_session() {
	ini_set('session.cookie_httponly', true);
	if(isset($_GET['_PHPSESSID'])) session_id($_GET['_PHPSESSID']);
	session_start();
	
	uct_init_arraydb();
	
	/*
		如果未登陆，看看是否有通过微信WEIXINSESSIONID登陆
		同域名下有多个sp时可能会有问题, 因此始终检查是否有WEIXINSESSIONID	
	*/
	//if (empty($_SESSION['sp_uid']) && empty($_SESSION['su_uid'])) {
	if (!uct_is_backend_page() && !uct_is_weixin_page()) {
		WeixinMod::start_weixin_session();
	}
	//小程序接口
	if(!empty($_GET['_uct_token'])) {
		XiaochengxuMod::start_xiaochengxu_session();
	}

	if (!empty($_SESSION['sp_uid'])) {
		//可能是商户后台登陆
		AccountMod::set_current_service_provider($_SESSION['sp_uid']);
	}
	if (!empty($_SESSION['su_uid'])) {
		//可能是用户登陆
		AccountMod::set_current_service_user($_SESSION['su_uid']);
	}
}

function uct_init_arraydb() {
	//系统设置
	$GLOBALS['arraydb_sys'] = new ArrayDb('arraydb_sys');
	
	//微信session, 用于自动登陆
	$GLOBALS['arraydb_weixin_session'] = new ArrayDb('arraydb_weixin_session');
	
	//微信粉丝设置
	$GLOBALS['arraydb_weixin_fan'] = new ArrayDb('arraydb_weixin_fan');
	
	//单个公众号设置
	$GLOBALS['arraydb_weixin_public'] = new ArrayDb('arraydb_weixin_public');
	
	//支付设置
	$GLOBALS['arraydb_pay'] = new ArrayDb('arraydb_pay');

	//队列设置
	$GLOBALS['arraydb_job'] = new ArrayDb('arraydb_job');
}

/*
是否为后台管理页面
*/
function uct_is_backend_page() {
	return $GLOBALS['_UCT']['APP'] == 'sp' || in_array($GLOBALS['_UCT']['CTL'], array('sp','api'));
}

/*
是否为微信接口页面
*/
function uct_is_weixin_page() {
	return ($GLOBALS['_UCT']['CTL'] == 'weixin' && $GLOBALS['_UCT']['ACT'] == 'tencent_callback') ||
			($GLOBALS['_UCT']['CTL'] == 'component' && $GLOBALS['_UCT']['ACT'] == 'message'); 
}

/*
加载系统插件
*/
function uct_init_plugins() {
	if (uct_is_backend_page()) {
		//系统后台增加登陆权限检查
		uct_use_app('sp');
		Event::addHandler('BeforeAction', array('SpMod','checkActionPermission'));
		Event::addHandler('BeforeAction', array('SpMod','recordRecentUsedApp'), 11);

		//如果是子账号身份登陆， 对权限进行进一步检查
		if(AccountMod::has_subsp_login()) {
			//这个use写在SpMod::checkActionPermission里
			//uct_use_app('subsp');
			Event::addHandler('BeforeAction', array('SubspMod','checkActionPermission'), 12);
		}
	}
}

//--------------------------

/*
模板渲染函数
@param $view 模板文件 默认为 $ctl/$act.tpl
模板文件路径 默认为 app/$app/tpl/

@param $param 模板文件内可用的变量, 默认导出了 $tpl_path 模板路径 和 $static_path 静态文件路径
*/
function render($view = '', $param = array()) {
	if (empty($param['tpl_path'])) {
		$param['tpl_path']    = UCT_PATH . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
		$param['static_path'] = DS . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'static';
	}
	if (empty($view)) {
		$view = $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
	}
	
	extract($param);
	$file = $param['tpl_path'] . DS . $view;
	if (file_exists($file)) {
		include($file);
	} else {
		echo 'warning! tpl file not found! ' . substr($file, strlen(UCT_PATH));
	}
}

/*
模板包含函数
通常在模板文件中调用
@param $view 模板文件 默认为 $ctl/$act.tpl
模板文件路径 默认为 app/$app/tpl/

*/
function render_include($view, $param = array()) {
	if (is_string($param)) {
		$param['tpl_path'] = $param;
	}
	if (empty($param['tpl_path'])) {
		$param['tpl_path'] = UCT_PATH . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
	}
	if (empty($view)) {
		$view = $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
	}
	
	$file = $param['tpl_path'] . DS . $view;
	if (file_exists($file)) {
		include($file);
	} else {
		echo 'warning! tpl file not found! ' . substr($file, strlen(UCT_PATH));
	}
}

/*
	取当前页面url
*/
function this_url($key = null, $value = null) {
	$get = $_GET;
	if($key!==null) $get[$key] = $value;
	return '/?'.http_build_query($get);
}

/*
	哪个代理
*/
function which_agent_provider($uid = null) {
	$as = array(
		'weixin.uctphp.com',
		'x.59zan.com',
	);
	if($uid !== null) {
		return isset($as[$uid]) ? $as[$uid] : $as[0];
	}

	if($ap_uid = requestInt('_ap_uid')) {
		return $ap_uid;
	}	
	$a = array_search(strtolower(getDomainName()), $as);
	return $a ? $a : 0;
}

/*
取当前后台模板版本 app spv3
*/
function get_current_sp_tpl_version() {
	//dbg
	if(in_array(AccountMod::get_current_service_provider('uid'), array(1587, 2011))) {
		return 'app';
	}

	if(!empty($_REQUEST['_old'])) {
		return 'app';
	}
	if(!empty($_REQUEST['_spv3'])) {
		return 'spv3';
	}
	if(in_array(AccountMod::get_current_service_provider('uid'), array(31,2, 1052, 1085, 1522, 1523, 1524,1527, 1084,1532,1533,1534, 1096, 1064, 1094, 1520, 1081, 1067, 1595, 1088, 1500, 1511, 1093, 1097, 1091, 1065))) {
		return 'spv3';
	}
	if(in_array(AccountMod::get_current_service_provider('parent_uid'), array(1064))) {
		return 'spv3';
	}

	$v = 'app';
	if(defined('DEBUG_WXPAY') && DEBUG_WXPAY && defined('USE_V3_TPL') && USE_V3_TPL) {
		$v = 'spv3';
	}
	if($ic = SpInviteMod::get_invite_code_by_sp_uid()) {

/*
	邀请码说明：
	free 7天试用
	aaaa 全功能邀请码
	test 开发测试用	
	lian 散户

	guo1 郭代理1
*/
		if(in_array(substr($ic['invite_code'], 0 ,4), array('test', 'aaaa', 'free', 'guo1', 'lian'))) {
			$v = 'spv3';
		}
	}

	return $v;
}

/*
sp后台的模板渲染函数, 在一个框架页面内输出模板

@param $view 模板文件 默认为 $ctl/$act.tpl
模板文件路径 默认为 app/$app/tpl/

@param $param 模板文件内可用的变量, 默认导出了 $tpl_path 模板路径 和 $static_path 静态文件路径
如果指定了$param['menu_array'], 那么使用 app/sp/tpl/menuframe.tpl, 否则使用 app/sp/tpl/frame.tpl 

用spv3下的frame，如果没有内页，那么用旧的app下的内页, $tpl_path, $static_path 还是app下的
*/
function render_sp_inner($view = '', $param = array()) {
	$prefix_short = get_current_sp_tpl_version().DS;
	$prefix = UCT_PATH . $prefix_short;

	if (empty($param['tpl_path'])) {
		$param['tpl_path']    = !empty($GLOBALS['_UCT']['TPL']) ? 
				$prefix . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] : 
				$prefix . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
		$param['static_path'] = !empty($GLOBALS['_UCT']['TPL']) ? 
				DS . $prefix_short . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] . DS . 'static' : 
				DS . $prefix_short . $GLOBALS['_UCT']['APP'] . DS . 'static';
	}
	if (empty($view)) {
		if(!empty($GLOBALS['_UCT']['TPL'])) {
			if(isAjax() || $_SERVER['REQUEST_METHOD'] == 'POST') {
				$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.php.tpl';
				if(file_exists($view)) {
					extract($param);
					include($view);
					return;
				}
			}
		}

		$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
		if(!empty($GLOBALS['_UCT']['TPL']) && !file_exists($view)) {
			$param['tpl_path'] = $prefix . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
			$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
		}
	}

	/*
		新版后台， 如果没有内页，那么用旧的app下的内页, $tpl_path, $static_path 还是app下的
	*/
	if(empty($GLOBALS['_UCT']['DONT_USE_OLD_TPL']) && !file_exists($view) && ($prefix_short != 'app'.DS)) {
		$dft_prefix_short = 'app'.DS;
		$dft_prefix = UCT_PATH.$dft_prefix_short;
		$param['tpl_path']  = !empty($GLOBALS['_UCT']['TPL']) ? 
				$dft_prefix . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] : 
				$dft_prefix . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
		$param['static_path'] = !empty($GLOBALS['_UCT']['TPL']) ? 
				DS . $dft_prefix_short . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] . DS . 'static' : 
				DS . $dft_prefix_short . $GLOBALS['_UCT']['APP'] . DS . 'static';
		if(!empty($GLOBALS['_UCT']['TPL'])) {
			if(isAjax() || $_SERVER['REQUEST_METHOD'] == 'POST') {
				$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.php.tpl';
				if(file_exists($view)) {
					extract($param);
					include($view);
					return;
				}
			}
		}

		$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
		if(!empty($GLOBALS['_UCT']['TPL']) && !file_exists($view)) {
			$param['tpl_path'] = $dft_prefix . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
			$view = $param['tpl_path'] . DS . $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
		}
	} //end of 新版使用旧内页

	$param['view_path'] = isset($param['menu_array']) ? array(
		$view,
		$prefix . 'sp' . DS . 'tpl' . DS . 'menuframe.tpl'
	) : $view;
	
	extract($param);
	$file = $prefix . 'sp' . DS . 'tpl' . DS . 'frame.tpl';
	if (file_exists($file)) {
		include($file);
	} else {
		echo 'warning! tpl file not found! ' . substr($file, strlen(UCT_PATH));
	}
}

/*
前台的模板渲染函数
@param $view 模板文件 默认为 $ctl/$act.tpl
模板文件路径 默认为 app/$app/tpl/ 或 app/$app/view/$tpl/

@param $params 模板文件内可用的变量, 默认导出了 $tpl_path 模板路径 和 $static_path 静态文件路径
*/
function render_fg($view = '', $params = array()) {
	if (empty($params['tpl_path'])) {
		$params['tpl_path']    = !empty($GLOBALS['_UCT']['TPL']) ? UCT_PATH . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] . DS . 'tpl' : UCT_PATH . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'tpl';
		$params['static_path'] = !empty($GLOBALS['_UCT']['TPL']) ? DS . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'view' . DS . $GLOBALS['_UCT']['TPL'] . DS . 'static' : DS . 'app' . DS . $GLOBALS['_UCT']['APP'] . DS . 'static';
	}
	
	if (empty($view)) {
		$view = $GLOBALS['_UCT']['CTL'] . DS . $GLOBALS['_UCT']['ACT'] . '.tpl';
	}
	
	render($view, $params);
}

/*
	设置模板镜像	
	例如微外卖中店铺设置与微商城的店铺设置是一样的模板, 不需要再复制一份代码,直接在外卖中
	uct_set_mirror_tpl('shop', 'sp', 'tpl')
*/
function uct_set_mirror_tpl($app = '', $ctl = '', $act = '') {
	if($app) $GLOBALS['_UCT']['APP'] = $app;
	if($ctl) $GLOBALS['_UCT']['CTL'] = $ctl;
	if($act) $GLOBALS['_UCT']['ACT'] = $act;
}

function uct_check_mirror_tpl_access() {
	if(empty($_REQUEST['_r']) && !isAjax() && isset($_SERVER['HTTP_REFERER']) &&
		($u = parse_url($_SERVER['HTTP_REFERER'])) && !empty($u['query'])) {
		parse_str($u['query'], $q);
		if(!empty($q['_a']) && $q['_a'] != $GLOBALS['_UCT']['APP']) {
			//redirectTo('?_m=1&_a='.$q['_a'].'&_u='.$GLOBALS['_UCT']['CTL'].'.'.$GLOBALS['_UCT']['ACT']);
			$_GET['_a'] = $q['_a'];
			redirectTo('?'.http_build_query($_GET));
		}
	}
}

/*
分页

@param $page 当前页， 从0开始
@param $total 总页数
@param $url_left 分页链接地址
@param $pages 最多显示几个页码

尽量显示多个页码,当前页尽量居中
*/
function uct_pagination($page, $total, $url_left, $pages = 7) {
	if(get_current_sp_tpl_version() == 'spv3') {
		return uct_pagination_spv3($page, $total, $url_left, $pages);
	}

	$html = '<ul class="am-pagination"><li ';
	if ($page == 0) {
		$html .= 'class="am-disabled"';
	}
	$html .= '><a href="' . $url_left . '0">1 &laquo;</a></li>';
	
	$start = max(0, $page - $pages + 1);
	$end   = min($total - 1, $page + $pages);
	
	if ($end - $start > $pages) {
		$best_start    = max($start, $page - ceil($pages / 2) + 1); //当前页居中
		$max_fit_start = $end - $pages + 1; //右边页不被截断
		$start         = min($best_start, $max_fit_start);
		$end           = $start + $pages - 1;
	}
	
	for ($i = $start; $i <= $end; $i++) {
		$html .= '<li';
		if ($page == $i) {
			$html .= ' class="am-active"';
		}
		$html .= '><a href="' . $url_left . $i . '">' . ($i + 1) . '</a></li>';
	}
	
	$html .= '<li';
	if ($page == $total - 1) {
		$html .= ' class="am-disabled"';
	}
    $html .= '><a href="' . $url_left . ($total - 1) . '">&raquo; '.(($total==0)?'':$total).'</a></li>';
    $html .= '<li><input type="number" class="am-input-lg am-pagination pagination_page" data-url="';
    $html .= $url_left.'" style="width: 40px;" value="'.($page+1).'"></li>';
    $html .= '</ul>';
	return $html;
}

function uct_pagination_spv3($page, $total, $url_left, $pages = 7) {
	$html = '<div class="am-u-sm-12 pl0 pr0 pro-btngroup-self"><div class="am-fr am-cf"><ul data-am-widget="pagination" class="am-pagination am-pagination-default mg0 am-no-layout"><li class="am-pagination-prev';
	if ($page == 0) {
		$html .= ' am-disabled';
	}
	
	$html .= '"><a href="'.$url_left.($page-1).'" style="border-radius:6px;">上一页</a></li> <li ';

	if ($page == 0) {
		$html .= 'class="am-disabled"';
	}
	$html .= '><a href="' . $url_left . '0">1 &laquo;</a></li>';
	
	$start = max(0, $page - $pages + 1);
	$end   = min($total - 1, $page + $pages);
	
	if ($end - $start > $pages) {
		$best_start    = max($start, $page - ceil($pages / 2) + 1); //当前页居中
		$max_fit_start = $end - $pages + 1; //右边页不被截断
		$start         = min($best_start, $max_fit_start);
		$end           = $start + $pages - 1;
	}
	
	for ($i = $start; $i <= $end; $i++) {
		$html .= '<li';
		if ($page == $i) {
			$html .= ' class="am-active"';
		}
		$html .= '><a href="' . $url_left . $i . '">' . ($i + 1) . '</a></li>';
	}
	
	$html .= '<li';
	if ($page == $total - 1) {
		$html .= ' class="am-disabled"';
	}
    $html .= '><a href="' . $url_left . ($total - 1) . '">&raquo; '.(($total==0)?'':$total).'</a></li>';
	$html .= '<li class="time-btn"><div class="am-form-inline" role="form2" ><div class="am-form-group"><input type="number" class="am-form-field pagination-self am-pagination pagination_page" data-url="'.$url_left.'" value="'.($page + 1).'" style="width:60px;"></div><span>/'.$total.'</span></div></li>';
	$html .= '<li class="am-pagination-next';
	if ($page >= $total - 1) {
		$html .= ' am-disabled';
	}
	$html .= '"><a href="'.$url_left.($page+1).'" style="border-radius:6px;">下一页</a></li>';
#    $html .= '<li><input type="number" class="am-input-lg am-pagination pagination_page" data-url="';
#    $html .= $url_left.'" style="width: 40px;" value="'.($page+1).'"></li>';
    $html .= '</ul></div><p class="am-fr am-cf mt0">共 <span>'.$total.'</span>页，每页 <span>'.requestInt('limit',10).'</span>条</p></div>';
	return $html;
}

/*
	返回绝对url
*/
function uct_realurl($url) {
	if(!strncasecmp($url, 'http', 4)) {
		return $url;
	}

	return getUrlName() . $url;
}

