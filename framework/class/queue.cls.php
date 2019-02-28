<?php
/*
	异步队列, 定时任务 采用resque实现
*/

//http触发执行异步任务  用于验证调用合法性, 请修改此key
define('SIMPLE_JOB_KEY', 'xxxsssssssxx');

if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	function queue_log($str) {
		#echo $str.PHP_EOL;
		#error_log($str);
		Weixin::weixin_log($str);
	}

	ignore_user_abort(true);
	//set_time_limit(0);
	include '../../index.php';

	$class = requestString('class', PATTERN_NORMAL_STRING);
	$args = requestString('args');
	$nonce = requestString('nonce', PATTERN_NORMAL_STRING);
	$sign = requestString('sign', PATTERN_NORMAL_STRING);
	if($sign != md5($class.$args.$nonce.SIMPLE_JOB_KEY)) {
		queue_log('check sign failed!');
		return false;
	}
	#echo 'ok';
	#flush();

	queue_log('queue job running ... '.$class);

	if(!class_exists($class)) {
		queue_log('Could not find queue job class ' . $class);
		return false;
	}   
	if(!method_exists($class, 'perform')) {
		queue_log('queue Job class ' . $class . ' does not contain a perform method.');
		return false;
	}   

	$cls = new $class;
	$args = json_decode($args, true);
	if(!$args) $args = array();
	$cls->args = $args;
	if(method_exists($cls, 'setUp')) {
		$cls->setUp();
	}   

	#$cls->perform();
	call_user_func_array(array($cls, 'perform'), $args);

	if(method_exists($cls, 'tearDown')) {
		$cls->tearDown();
	}   

	queue_log('queue job done!');
	return true;
}

uct_use_vendor('php_resque');

class Queue {
	/*
		异步任务 简易版
		如果没有安装resque可以使用这个

		采用发起新http请求的方式实现
		@param $args  参数array  
	*/
	public static function simple_do_job_async($class, $args = null, $rmt_url = '') {
		$args = json_encode($args);
		$nonce = substr(md5(uniqid()), 6, 16);	
		$sign = md5($class.$args.$nonce.SIMPLE_JOB_KEY);

		if(!$rmt_url) {
			#$rmt_url = getUrlName().'/framework/class/queue.cls.php';
			$rmt_url = getUrlName().substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT']));
		}
		$rmt_url .= strpos($rmt_url, '?') === false ? '?' : '&';
		$rmt_url .= 'class='.$class.'&args='.urlencode($args).'&nonce='.$nonce.'&sign='.$sign;

		#var_export($rmt_url);
		$timeout = 3;
		if(1 && function_exists('fsockopen')) {
			$url = parse_url($rmt_url);	
			$fp= fsockopen($url['host'], isset($url['port']) ? $url['port'] : 80, $errno, $errstr, $timeout);
			if(!$fp) {
				return false;
			}
			$out = 'GET '.$url['path'].'?'.$url['query'].' HTTP/1.1'."\r\n"
					.'Host: '.$url['host'].''."\r\n"
					.'Connection: Close'."\r\n\r\n";
 
			fwrite($fp, $out);
			/*忽略执行结果
			while (!feof($fp)) {
				echo fgets($fp, 128);
			}*/
			fclose($fp);
			return true;
		}
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL	=> $rmt_url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $timeout,
		));
		$ret = curl_exec($ch);
		curl_close($ch);
		return true;
	}

	/*
		添加异步任务
		@param $class 类名,里面的perform成员函数为具体的任务函数
		@param $args  参数array
		@param $queue 队列名称, 默认按模块名取
		@param $trackStatus 监控任务状态,默认开启 todo 需要一个任务状态查看界面

		@return string job_id
	*/
	public static function add_job($class, $args = null, $queue = '', $trackStatus = false) {

		if(!Resque::$redis) {
			Resque::setBackend(REDIS_DSN);
		}

		if(!$queue) {
			$queue = !empty($GLOBALS['_UCT']['APP']) ? $GLOBALS['_UCT']['APP'] : 'framework';
		}

		//立即执行 变成同步任务
		if($queue == 'right_now') {
			$cls = new $class;
			if(!$args) $args = array();
 			if(method_exists($cls, 'setUp')) {
				$cls->setUp();
			}

 			$ret = call_user_func_array(array($cls, 'perform'), $args);

			if(method_exists($cls, 'tearDown')) {
				$cls->tearDown();
			}
			return $ret;
		}

		try {
			return Resque::enqueue($queue, $class, $args, $trackStatus);
		} catch (Exception $e) {
			Weixin::weixin_log('EXCEPTION! resque enqueue failed! do right now! '.$class);
			//无redis时，立即执行, 或者换个方式
			return register_shutdown_function(function($class, $args){
				ob_start();	
				Queue::add_job($class, $args, 'right_now');
				ob_end_clean();
			}, $class, $args);
			#return self::add_job($class, $args, 'right_now', $trackStatus);
			#return self::simple_do_job_async($class, $args);
			return false;
		}
	}

	/*
		添加定时任务
	*/
	public static function do_job_at($timestamp, $class, $args = null) {
		if($timestamp > 0 && $timestamp <= 2592000) { //86400 * 30
			$timestamp += time();
		}
		if(!Resque::$redis) {
			Resque::setBackend(REDIS_DSN);
		}

		try {
			return Resque::defered_enqueue($timestamp, $class, $args);
		} catch (Exception $e) {
			Weixin::weixin_log('EXCEPTION! resque defered enqueue failed! '.$class);
			//失败，转为立即执行
			return self::add_job($class, $args);
			return false;
		}
	}

	/*
		添加工作进程	
		@param $queues 只做某个队列的任务, * 不限
		@param $count  一次启动多个进程
	*/
	public static function add_worker($queues = '*', $count = 1, $verbose = false) {
		if(PHP_SAPI != 'cli') {
			die('error! resque add_worker can only run in cli_mode !');
		}

		if(!Resque::$redis) {
			Resque::setBackend(REDIS_DSN);
		}

		$logger = new Resque_Log($verbose);
		if($count > 1) {
			for($i = 0; $i < $count; $i++) {
				$pid = Resque::fork();
				if(-1 == $pid) {
					echo ('resque fork fail!'); exit(1);
				}
				//child worker
				if(!$pid) {
					$worker = new Resque_Worker($queues);
					$worker->setLogger($logger);
					$worker->work();
				}
				else {
					sleep(1);
				}
			}
		}
		else {
			$worker = new Resque_Worker($queues);
			$worker->setLogger($logger);
			$worker->work();
		}
	}
}

