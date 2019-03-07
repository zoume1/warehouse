<?php
/*
	计划任务

	lhliu
	2016-07-05
*/


class CronJob {
	/*
		增加计划任务
		支持时间粒度: 分钟 小时 到 周
		
		限制条件：	
		todo 1. 每个class最多只跑1个计划任务, 这样也方便通过名称删除任务
		2. 任务间隔时间不要过小， 最少>5分钟

		@param $time = array (
			'w' => '' //每周的天 0-6 0为星期天
			'H' => '' //小时 0- 23
			'i' => '' //分钟 0 - 59
		)		

		举例 
			每天(0点)运行一次 array('H' => 0) 
			每小时(0分)运行一次 array('i' => 0)
			每15分(第0,15,30,45分)钟运行一次 array('i' => '*\/15')


		@param $job = array(
			'class' => 
			'args' => 
		)
	*/
	public static function do_cron_job($time, $job) {
		$t = array(
			'w' => array(7,  86400),
			'H' => array(24, 3600),
			'i' => array(60, 60),
		);

		$expected = time() - 60; //允许1分钟延迟
		$offset = 0;
		$next = 0;
		$now = array_combine(array_keys($t), explode(',', date(implode(',', array_keys($t)), $expected)));

		$time = array_merge(array_fill_keys(array_keys($t), '*'), $time);

		foreach($t as $k => $v) {
			if(is_numeric($time[$k])) {
				if($now[$k] != $time[$k]) {
					if(!$offset) {
						$offset += ($now[$k] < $time[$k] ? ($time[$k] - $now[$k]) : 
								($time[$k] - $now[$k] + $t[$k][0])) * $t[$k][1];
					}
					else {
						$offset += ($time[$k] - $now[$k]) * $t[$k][1];
					}
				}	

				if(!$next) {
					$next += $t[$k][0] * $t[$k][1];
				}
			}	
			else if(!strncmp('*/', $time[$k], 2)) {
				$margin = (int)substr($time[$k], 2);
				if(!($now[$k] % $margin)) {
					$offset += ($margin - ($now[$k] % $margin)) * $t[$k][1];
				}

				if(!$next) {
					$next += $margin * $t[$k][1];
				}
			}
		}
		
		$this_time = $expected + $offset;
		$next_time = $this_time + $next - 30; //提前30秒准备

		Queue::do_job_at($this_time, $job['class'], $job['args']);

		echo 'cron job going to run '.date('Y-m-d H:i:s', $this_time).', '.var_export($job, true).PHP_EOL;	
		
		//self::delete_cron_job($job['class']);
		$id = Queue::do_job_at($next_time, 'CronJob', array($time, $job));
		Resque::redis()->hset('cronh', $job['class'], $id);
		
		return $id;
	}

	/*
		计划任务是否在运行
		每个类只能运行1个任务

		返回 job_id 或 false
	*/
	public static function is_cron_job_running($class) {
		uct_use_vendor('php_resque');
		if(!Resque::$redis) {
			Resque::setBackend(REDIS_DSN);
		}

		return Resque::redis()->hget('cronh', $class); 
	}

	/*
		删除计划任务
	*/
	public static function delete_cron_job($class) {
		uct_use_vendor('php_resque');
		if(!Resque::$redis) {
			Resque::setBackend(REDIS_DSN);
		}

		uct_use_app('admin');
		$jobs = ResqueMonitMod::get_jobs_list(array('queue' => 'defered', 
										'page'=> 0, 'limit' => -1));	
		if(!empty($jobs['list']))
		foreach($jobs['list'] as $j) {
			if(!strcasecmp($j['class'], $class) || 
				(1 && (!strcasecmp($j['class'], 'CronJob') && 
					!empty($j['args'][1]['class']) &&
					!strcasecmp($j['args'][1]['class'], $class)))) {
					Resque::defered_delete_job($j['id']);
			}
		}
		
		if($id = Resque::redis()->hget('cronh', $class)) {
			Resque::redis()->hdel('cronh', $class);
			return Resque::defered_delete_job($id);
		}

		return false;
	}

	public function perform($time, $job) {
		CronJob::do_cron_job($time, $job);
	}

}

