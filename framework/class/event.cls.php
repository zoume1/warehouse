<?php
/*
	框架事件注册与触发调用

	处理函数按添加时的sort从小到大顺序调用
*/

class Event {
    protected static $_handlers = array();

    public static function handle($name, $args=array()) {
        if (empty(Event::$_handlers[$name])) {
			return null;
		}

		usort(Event::$_handlers[$name], function($a, $b){
			return $a[1] == $b[1] ? 0 : ($a[1] > $b[1] ? 1 : -1);
		});
		foreach (Event::$_handlers[$name] as $handler) {
			$ret = call_user_func_array($handler[0], $args); 
			if ($ret === false) {
				break;
			}
		}

		return $ret;
   }

    public static function addHandler($name, $handler, $sort = 10) {
		Event::$_handlers[$name][] = array($handler, $sort);
    }

    public static function addHandlerOnce($name, $handler, $sort = 10) {
		if(empty(Event::$_handlers[$name]) || !in_array(array($handler, $sort), Event::$_handlers[$name])) {
			Event::$_handlers[$name][] = array($handler, $sort);
		}
    }

	/*
		注册处理事件，一次添加一个类
		添加的处理函数需要是静态函数,形如onXXX方式命名
	*/
	public static function addStaticClassHandler($class, $sort = 10) {
		$ms = get_class_methods($class);
		if($ms)
		foreach ($ms as $method) {
            if (strncmp($method, 'on', 2) == 0) {
                self::addHandler(substr($method, 2), array($class, $method), $sort);
            }
        }
	}
}
