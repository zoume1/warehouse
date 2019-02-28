<?php
function requestInt($name, $default = 0) {
	$ret = isset($_REQUEST[$name]) ? checkInt($_REQUEST[$name], $default) : $default;
	//for safety
	if ($name == 'limit') {
		$ret = min(50, max(-1, $ret));
	}
	else if ($name == 'page') {
		$ret = max(0, $ret);
	}

	return $ret;
}

function requestFloat($name, $default = 0.0) {
	return isset($_REQUEST[$name]) ? checkFloat($_REQUEST[$name], $default) : $default;
}

function requestBool($name, $default = false) {
	return isset($_REQUEST[$name]) ? checkBool($_REQUEST[$name], $default) : $default;
}

function requestString($name, $check = '', $default = '') {
	return isset($_REQUEST[$name]) ? checkString($_REQUEST[$name], $check, $default) : $default;
}

//获取字符串，只取前length个字符
function requestStringLen($name, $length = 140) {
	return mb_substr(requestString($name), 0, $length, 'utf8');	
}

/**
* 将字符串分解并以数组方式返回,自动清除空字符串
*/
function requestStringArray($name, $check = '', $split_char = SPLIT_STRING) {
	if(empty($_REQUEST[$name])) {
		 return array();
	}
	if(is_string(($_REQUEST[$name]))) {
		$s = explode($split_char, $_REQUEST[$name]);
	}
	else if(is_array($_REQUEST[$name])) {
		$s = $_REQUEST[$name];
	}
	else {
		 return array();
	}

	if ($check) {
		return array_filter($s, function($v) use ($check) {
			return is_string($v) && preg_match($check, $v);	
		});
	}
	else  {
		return array_filter($s);
	}
}

/**
* 将字符串分解并以整形数组方式返回,自动清除空值
*/
function requestIntArray($name, $split_char = SPLIT_STRING) {
	if(empty($_REQUEST[$name])) {
		 return array();
	}
	if(is_string(($_REQUEST[$name]))) {
		$s = explode($split_char, $_REQUEST[$name]);
	}
	else if(is_array($_REQUEST[$name])) {
		$s = $_REQUEST[$name];
	}
	else {
		 return array();
	}

	$ret = array();
	foreach($s as $v) {
		if (is_numeric($v)) {
			$ret[] =  checkInt($v);
		}
	}

	return $ret;
}

/**
* 从$var中读取整数， 失败情况下返回$default
*/
function checkInt($var, $default = 0) {
	return  is_numeric($var) ? intval($var, (strncasecmp($var, '0x', 2) == 0 || strncasecmp($var, '-0x', 3) == 0) ? 16 : 10) : $default;
}

function checkFloat($var, $default = 0) {
	return  is_numeric($var) ? floatval($var) : $default;
}

function checkBool($var, $default = false) {
	if (is_bool($var))
	{
		return $var;
	}
	static $f = array('false', '0', 'no', 'off', 'null', 'nil', 'nan');

	if (in_array(strtolower($var), $f))
	{
		return false;
	}
	return $var ? true : $default;
}

function checkString($var, $check = '', $default = '') {
	if (!is_string($var)) {
		if(is_numeric($var)) {
			$var = (string)$var;
		}
		else {
			return $default;
		}
	}
	if ($check) {
		 return (preg_match($check, $var, $ret) ? $ret[1] : $default);
	}

	return $var;
}

/*
示例 $desc
 array(
   array('id', 'Int'),
   array('type', 'string', PATTERN_NORMAL_STRING),
   array('required', 'Bool', false),
   array('desc', 'string', PATTERN_NORMAL_STRING),
))
*/
function requestKvJson($var, $desc = array()) {
	return isset($_REQUEST[$var]) ? checkKvJson($_REQUEST[$var], $desc) : array();
}

function checkKvJson($var, $desc = array()) {
	if(is_string($var)) {
		$var = json_decode($var, true);
	}
	if(!$var || !is_array($var)) {
		return array();
	}

	if($desc)
	foreach($desc as $d) {
		
		if(!isset($var[$d[0]])) {
			return array();
		}	

		$ps = array_slice($d, 2);
		array_unshift($ps, $var[$d[0]]);
		$var[$d[0]] = call_user_func_array('check'.$d[1], $ps);
		if($var[$d[0]] === false && strcasecmp($d[1], 'Bool')) {
			return array();
		}
	}
	
	return $var;
}

/*
 * 获取真实的客户ip
 * */
function get_real_ip(){
    //判断服务器是否允许$_SERVER
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //bug:可以通过http头 x-forwarded-for 伪造
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $iplist = explode($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
                $realip = $iplist[0];
            }
        }else if(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    }else{
        //不允许就使用getenv获取
        if(getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        }else if(getenv("HTTP_CLIENT_IP")){
            $realip = getenv("HTTP_CLIENT_IP");
        }else{
            $realip = getenv("REMTE_ADDR");
        }
    }
    return $realip;
}


/*
	获取客户端IP地址
	todo
*/
function requestClientIP() {
	return checkString($_SERVER["REMOTE_ADDR"], PATTERN_IP, '0.0.0.0');
}

/*
	获取域名 
	考虑到服务器配置不合适的情况,域名绑定的情况, 优先读取HTTP_HOST
*/
	
function getDomainName() {
	if (($domain = checkString($_SERVER['HTTP_HOST'], PATTERN_DOMAIN_NAME))) {
		$white_list = array(
			'localhost', 'wx.localhost',
			'uctphp.com', 'www.uctphp.com', 'weixin.uctphp.com',
		);
		if (in_array($domain, $white_list)
			|| Dba::readOne('select 1 from domain_bind where domain = "'.addslashes($domain).'"')
			|| checkString($domain, PATTERN_IPV4)) {
			return $domain;
		}
	}

	return $_SERVER['SERVER_NAME'];
}

function isHttps() {
	if(isset($_SERVER['HTTPS'])) {
		if($_SERVER['HTTPS'] == 'on') {//nginx
			return true;
		}
		if($_SERVER['HTTPS'] == '1') {//apache
			return true;
		}
	}
	if(isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
		return true;
	}
	return false;
}

//获取url地址
function getUrlName() {
	$url = (isHttps() ? 'https://' : 'http://').getDomainName();
	if(isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], array(80, 443))) {
		$url .= ':'.$_SERVER['SERVER_PORT'];
	}
		
	return $url;
}


//取当前url地址, 不包含#及其后面部分
function getCurrentUrl() {
	return getUrlName().(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : http_build_query($_GET));
}

/*
	重定向
*/
function redirectTo($url) {
	if(strncasecmp($url, 'http', 4) != 0) {
		$url = (getUrlName()).$url;	
	}

	/*
		微信自动授权发消息 302 变成代理
	*/
	if($GLOBALS['_UCT']['CTL'] == 'component' && $GLOBALS['_UCT']['ACT'] == 'message') {
		$post = file_get_contents('php://input');
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_TIMEOUT, 4);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		$ret = curl_exec($c);
		curl_close($c);

		echo $ret;
		exit();
	} 

	//直接报错
	if(0&&headers_sent()) {
		echo '<script> console.log("warning in redirectTo! headers already sent!!! use js instead!!!");
window.location.href="'.$url.'";</script>';
	} else {
		header('Location: '.$url);
	}
	exit();
}

#-----------------------------
//获取错误号
function getLastError() {
	return isset($GLOBALS['_UCT']['errorno']) ? $GLOBALS['_UCT']['errorno'] : 0;
}

//获取错误位置
function getLastErrorPos() {
	return isset($GLOBALS['_UCT']['errorpos']) ? $GLOBALS['_UCT']['errorpos'] : '';
}

function setLastError($err) {
	$GLOBALS['_UCT']['errorno']  = $err;

	if(defined('DEBUG_ERROR_POS') && DEBUG_ERROR_POS) {
		$info = debug_backtrace();	
		$GLOBALS['_UCT']['errorpos'] = substr($info[0]['file'], strlen(UCT_PATH)).' +'.$info[0]['line'];
	}
}

//错误提示语
function setLastErrorString($str) {
	$GLOBALS['_UCT']['errorstr']  = $str;

	if(defined('DEBUG_ERROR_POS') && DEBUG_ERROR_POS) {
		$info = debug_backtrace();	
		$GLOBALS['_UCT']['errorpos'] = substr($info[0]['file'], strlen(UCT_PATH)).' +'.$info[0]['line'];
	}
}


function getErrorString($err = null) {
	if($err === null) {
		if(isset($GLOBALS['_UCT']['errorstr']))  return $GLOBALS['_UCT']['errorstr'];
		$err = getLastError();
	}
	if ($err == 0) {
		return 'ERROR_OK';
	}
	$c = get_defined_constants();
	foreach($c as $k => $v) {
		if (strncmp($k, 'ERROR_', 6) == 0) {
			if ($v == $err) {
				return $k;
			}
		}
	}

	return 'UNDEFINED ERROR CODE!';
}

function outError($errno = null, $errstr = '')
{
	if($errno === null) {
		$errno = getLastError();
	}
	else {
		if(defined('DEBUG_ERROR_POS') && DEBUG_ERROR_POS) {
			$info = debug_backtrace();	
			$GLOBALS['_UCT']['errorpos'] = substr($info[0]['file'], strlen(UCT_PATH)).' +'.$info[0]['line'];
		}
	}

	#header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array('errno' => $errno, 'errstr' => $errstr ? $errstr : getErrorString($errno), 
							'errpos' => getLastErrorPos()));	
	exit();	
}

function outRight($ret) {
	#header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array('errno' => getLastError(), 'errstr' => getErrorString(), 'data' => $ret,
							'errpos' => getLastErrorPos()));	
	exit();	
}

/*
	类似array_search, 可以自定义比较函数
	@param mix $needle 
	@param array $haystack 在此数组中搜索
	@param function $comparator
	
	@return 搜索到的值或false
*/
function array_usearch($needle, $haystack, $comparator) {
	foreach($haystack as $h) {
		if($comparator($needle, $h)) {
			return $h;
		}
	}
	return false;
}

/*
* 根据UserAgent判断客户端类型
* 0 PC浏览器， 1 iphone客户端，2 android客户端, >=10 微信浏览器
* 
*/
function getUserAgent() {
	static $cache = -1;
	if ($cache != -1) {
		return $cache;
	}

	$client = strtolower(isset($_REQUEST['_agent']) ? $_REQUEST['_agent'] : $_SERVER['HTTP_USER_AGENT']);

	$ua_arr = array(
				1 => array('iphone', 'ipad'),
				2 => array('android'),
			);	
	
	foreach($ua_arr as $k => $v) {
		foreach($v as $str) {
			if (strpos($client, $str) !== false) {
				if (strpos($client, 'micromessenger') !== false) {
					$k += 10;
				}
				$cache = $k;	
				return $k;
			}
		}
	}

	$cache = 0;
	return 0;
}

/*
	是否为微信浏览器
*/
function isWeixinBrowser() {
	return getUserAgent() >= 10;
}

/*
	是否为ie浏览器
*/
function isIEBrowser() {
	return stripos($_SERVER['HTTP_USER_AGENT'], 'trident') !== false;
}

/*
	是否为手机浏览器
*/
function isMobileBrowser() {
	return getUserAgent() > 0;
}

/*
	是否为ajax请求
*/
function isAjax() {
	if(isset($_REQUEST['_is_ajax'])) return (bool)$_REQUEST['_is_ajax'];
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') == 0);
}

//simple compatible for php < 5.5
if(!function_exists('array_column')) {
	function array_column($arr, $column) {
		return array_map(function($a) use($column){ return $a[$column];}, $arr);
	}
}

/*
	整数 "ps"
	通常用于会员卡或优惠券uid前端显示成一串编号而不是一个整数
	$i不能大于36^6

	
*/
function ps_int($i, $split='-', $pad_length = 6) {
	if(is_array($i)) {
		return implode($split, array_map(function($ii) use($pad_length) {
			return strtoupper(strrev(str_pad(base_convert($ii, 10, 36), $pad_length, '0', STR_PAD_LEFT)));}, $i));
	}

	return strtoupper(strrev(str_pad(base_convert($i, 10, 36), $pad_length, '0', STR_PAD_LEFT)));
}

function get_ps_int($psi, $split='-') {
	if(strpos($psi, $split)) {
		return array_map(function($ii){ return base_convert(strrev($ii), 36, 10);}, explode($split, $psi));
	}
	return base_convert(strrev($psi), 36, 10);
}

function requestPsInt($name, $default = 0) {
	if(!$psi = requestString($name, PATTERN_PS_INT)) {
		return $default;
	}
	
	$i = @get_ps_int($psi);
	return is_array($i) ? $i[0] : $i;
}

/*
	把中文string转数字int

	
*/
function checkNatInt($str) {
	$map = array(
		'一' => '1','二' => '2','三' => '3','四' => '4','五' => '5','六' => '6','七' => '7','八' => '8','九' => '9',
		'壹' => '1','贰' => '2','叁' => '3','肆' => '4','伍' => '5','陆' => '6','柒' => '7','捌' => '8','玖' => '9',
		'零' => '0','两' => '2',
		'仟' => '千','佰' => '百','拾' => '十',
		'万万' => '亿',
	);

	$str = str_replace(array_keys($map), array_values($map), $str);
	$str = checkString($str, '/([\d亿万千百十]+)/u');

	$func_c2i = function ($str, $plus = false) use(&$func_c2i) {
		if(false === $plus) {
			$plus = array('亿' => 100000000,'万' => 10000,'千' => 1000,'百' => 100,'十' => 10,);
		}

		$i = 0;
		if($plus)
		foreach($plus as $k => $v) {
			$i++;
			if(strpos($str, $k) !== false) {
				$ex = explode($k, $str, 2);
				$new_plus = array_slice($plus, $i, null, true);
				$l = $func_c2i($ex[0], $new_plus);
				$r = $func_c2i($ex[1], $new_plus);
				if($l == 0) $l = 1;
				return $l * $v + $r;
			}
		}

		return (int)$str;
	};

	return $func_c2i($str);
}

/*
	file_get_contents  当服务器为Connection: keep-alive 时将一直等到超时
	如 微信头像 , 应该使用此函数立即返回
	http://wx.qlogo.cn/mmopen/pibp762tfrel7WBnOd2HjHAhSGO8Vbaibn30ibHbHPlduaDXItylQgpfOTo1nBecp4wAyheMnqabKL9Edq48JX0uTMbLGVt9H8e/0

*/
function curl_file_get_contents($url)
{
	if(strncasecmp($url, 'http', 4)) {
		return file_get_contents($url);
	}

        #Weixin::weixin_log('weixin get '.$url);
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	if(!strncasecmp($url, 'https', 5)) {
        	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
	}
        $ret = curl_exec($c);
        curl_close($c);

        return $ret;
}

