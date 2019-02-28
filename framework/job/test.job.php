<?php

class testJob {
	private $job_uid;
	private $call_back;
	public function __construct()
	{
		uct_use_app('job');
	}

	//开始运行时更改 状态
	public  function set_statu_running()
	{
		$job = array('status'=>JobMod::STATUS_RUNNING);
		Dba::update('job',$job,'uid="'.$this->job_uid.'"');
	}

	//运行结束更改 状态为已运行
	public function __destruct()
	{
		$job = array('status'=>JobMod::STATUS_COMPLETE,'end_time'=>time());
		Dba::update('job',$job,'uid="'.$this->job_uid.'"');
	}

	//job 里运行的内容
	public function perform()
	{
		$args          = func_get_args();
		$sp_uid        = isset($args['0']['sp_uid']) ? $args['0']['sp_uid'] : 0;
		$public_uid    = isset($args['0']['public_uid']) ? $args['0']['public_uid'] : 0;
		$this->job_uid = isset($args['0']['job_uid']) ? $args['0']['job_uid'] : '';
		$this->set_statu_running();//开始运行时更改 状态
		$func = isset($args['0']['func']) ? $args['0']['func'] : '';

		$data = $args['1']['0'];
		if (!empty($func))
		{
			call_user_func_array(array($this,$func),array($data));
			exit;
		}

		$args = array('basic_arg' => array('sp_uid'        => '19',
		                                   'public_uid'    => '1',
		                                   'job_uid'       => '',
		                                   'job_parent_id' => (isset($this->job_uid) ? $this->job_uid : ''),
		                                   'func'=>'doit'),
		              'fun_args'      => array('12'));
		$t = time() - 1001;
		$job  = JobMod::do_job_at($t, 'testJob', $args);
	}

	public function doit()
	{
		$args = func_get_args();
		var_dump($args);

	}
}

