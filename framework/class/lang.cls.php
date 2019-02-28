<?php
/**
 * User: lhliu 
 *
 * 获取国际化字符串
 */


class Lang{
	protected static $locale = '';
	protected static $data = array();

	public static function set_locale($l = 'zh_cn') {
		self::$locale = $l;
	}

	public static function get_locale() {
		if(!self::$locale) {
			self::get_text('hehe');
		}
		return self::$locale;
	}
	
    public static function get_text($id, $module = 'common', $locale = null) {
		if(!$locale) {
			/*
				自动选择客户端语言
				1. url参数指定 __lang
				2. cookie中 __lang
				3. 根据 http_accept_language 判断

				默认为中文
			*/
			if(!self::$locale) {
				if(isset($_REQUEST['__lang']) && in_array($_REQUEST['__lang'], array('zh_cn', 'en'))) {
					self::$locale = $_REQUEST['__lang'];
					setcookie('__lang', self::$locale, 0, '/');
				}
				else if(isset($_COOKIE['__lang']) && in_array($_COOKIE['__lang'], array('zh_cn', 'en'))) {
					self::$locale = $_COOKIE['__lang'];
				}
				else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
					self::$locale = (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh')!==false) ? 'zh_cn' : 'en';
				}
				else {
					self::$locale = 'zh_cn';
				}
			}

			$locale = self::$locale;	
		}

		if(!isset(self::$data[$module][$locale])) {
			self::$data[$module][$locale] = include UCT_PATH."i18n/$module/$locale.php";
		}

		return isset(self::$data[$module][$locale][$id]) ? self::$data[$module][$locale][$id] : $id;
    }
} 

