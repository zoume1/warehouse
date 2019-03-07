<?php

// 同步自动回复
class SyncaAutoreplyJob {
	private $job_uid;
	private $call_back;
	public  $args;
	private $sp_uid;
	private $public_uid;
	private $func;

	//开始运行时更改 状态
	public function set_statu_running()
	{
		uct_use_app('job');
		$job = array('status' => JobMod::STATUS_RUNNING);
		Dba::update('job', $job, 'uid="' . $this->job_uid . '"');
	}

	//运行结束更改 状态为已运行
	public function __destruct()
	{
		$job = array('status'       => JobMod::STATUS_COMPLETE,
		             'end_time'     => time(),
		             'job_callback' => (isset($this->call_back) ? $this->call_back : array()));
		Dba::update('job', $job, 'uid="' . $this->job_uid . '"');
	}

	//job 里运行的内容
	public function perform()
	{
		$this->args       = func_get_args();
		$this->sp_uid     = isset($this->args['0']['sp_uid']) ? $this->args['0']['sp_uid'] : 0;
		$this->public_uid = isset($this->args['0']['public_uid']) ? $this->args['0']['public_uid'] : 0;
		$this->func       = isset($this->args['0']['func']) ? $this->args['0']['func'] : '';
		$this->job_uid    = isset($this->args['0']['job_uid']) ? $this->args['0']['job_uid'] : '';
		$this->set_statu_running();//开始运行时更改 状态
		$_SERVER['REQUEST_TIME'] = time();
		AccountMod::set_current_service_provider($this->sp_uid);
		$wx                    = WeixinMod::get_weixin_public_by_uid($this->public_uid);
		$_SESSION['uct_token'] = $wx['uct_token'];
		if (!empty($this->func))
		{
			call_user_func_array(array($this, $this->func), array());
			exit;
		}

		$data = $this->args['1']['0'];


		$autoreply = $data;
		if (!empty($autoreply['add_friend_autoreply_info']))
		{

			//开启 同步关注时回复 规则设置 任务
			$data                          = $autoreply['add_friend_autoreply_info'];
			$args                          = array('basic_arg' => array('sp_uid'        => $this->sp_uid,
			                                                            'public_uid'    => $this->public_uid,
			                                                            'job_uid'       => '',
			                                                            'job_parent_id' => (isset($this->job_uid) ? $this->job_uid : ''),
			                                                            'func'          => 'synca_welcome'),
			                                       'fun_args'  => array($data));
			$this->call_back['add_friend'] = JobMod::add_job('SyncaAutoreplyJob', $args, 'welcome');
		}

		if ((!empty($autoreply['message_default_autoreply_info']) || !empty($autoreply['keyword_autoreply_info'])))
		{
			//开启 默认回复 规则设置 任务
			$data = $autoreply['message_default_autoreply_info'];
			$args = array('basic_arg' => array('sp_uid'        => $this->sp_uid,
			                                   'public_uid'    => $this->public_uid,
			                                   'job_uid'       => '',
			                                   'job_parent_id' => (isset($this->job_uid) ? $this->job_uid : ''),
			                                   'func'          => 'synca_default'),
			              'fun_args'  => array($data));
			!empty($data) && ($this->call_back['message_default'] = JobMod::add_job('SyncaAutoreplyJob', $args, 'default'));

			//开启 关键词回复 规则设置 任务
			$data = $autoreply['keyword_autoreply_info'];
			$args = array('basic_arg' => array('sp_uid'        => $this->sp_uid,
			                                   'public_uid'    => $this->public_uid,
			                                   'job_uid'       => '',
			                                   'job_parent_id' => (isset($this->job_uid) ? $this->job_uid : ''),
			                                   'func'          => 'synca_keyword'),
			              'fun_args'  => array($data));
			!empty($data) && ($this->call_back['keyword'] = JobMod::add_job('SyncaAutoreplyJob', $args, 'keywords'));
		}
		else
		{
			//设置默认回复为 无动作
			uct_use_app('default');
			$dft                     = array(
				'type'    => 'nodo',
				'msg'     => '',
				'keyword' => array('key' => ''),
				'proxy'   => array('url'      => '',
				                   'token'    => '',
				                   'msg_mode' => '',
				                   'aes_key'  => '',
				),
				'nodo'    => 'nodo',
			);
			$this->call_back['nodo'] = Default_WxPlugMod::set_public_default_action($dft, $this->public_uid);
		}

	}


	public function synca_welcome()
	{
		//暂时只支持文字
		$data = $this->args['1']['0'];
		uct_use_app('welcome');
		$msg = Welcome_WxPlugMod::get_public_welcome_msg($this->public_uid);
		//已配置不进行同步
		if ($msg === false)
		{
			$this->call_back['set_public_welcome'] = 'true';
		}
		switch ($data['type'])
		{
			case 'text':

				$media                                 = array('media_type'  => '1',
				                                               'sp_uid'      => $this->sp_uid,
				                                               'content'     => $data['content'],
				                                               'create_time' => time());
				$media_uid                             = WeixinMediaMod::add_or_edit_weixin_media($media);
				$msg                                   = array('media_type' => 0, 'media_uid' => $media_uid);
				$this->call_back['set_public_welcome'] = Welcome_WxPlugMod::set_public_welcome_msg($msg, $this->public_uid);


				break;
			case 'img':
				break;
			case 'voice':
				break;
			case 'video':
				break;
		}


	}

	public function synca_default()
	{
		//暂时只支持文字
		$data = $this->args['1']['0'];
		uct_use_app('default');
		$default = Default_WxPlugMod::get_public_default_action($this->public_uid);
		//已配置不进行同步
		if ($default === false)
		{
			$this->call_back['set_public_welcome'] = 'true';
		}
		switch ($data['type'])
		{
			case 'text':
				$media                                 = array('media_type'  => '1',
				                                               'sp_uid'      => $this->sp_uid,
				                                               'content'     => $data['content'],
				                                               'create_time' => time());
				$media_uid                             = WeixinMediaMod::add_or_edit_weixin_media($media);
				$msg                                   = array('media_type' => 0, 'media_uid' => $media_uid);
				$default                               = array(
					'type'    => 'msg',
					'msg'     => $msg,
					'keyword' => array('key' => ''),
					'proxy'   => array('url'      => '',
					                   'token'    => '',
					                   'msg_mode' => '',
					                   'aes_key'  => '',
					),
					'nodo'    => 'nodo',
				);
				$this->call_back['set_default_action'] = Default_WxPlugMod::set_public_default_action($default, $this->public_uid);
				break;
			case 'img':
				break;
			case 'voice':
				break;
			case 'video':
				break;
		}
	}

	public function synca_keyword()
	{
		$data = $this->args['1']['0'];
		//暂时只支持文字 和 图文
		uct_use_app('keywords');
		foreach ($data['list'] as $autoreply)
		{
			$reply_list = $autoreply['reply_list_info']['0'];
			switch ($reply_list['type'])
			{
				case 'text':
					$media     = array('media_type'  => '1',
					                   'sp_uid'      => $this->sp_uid,
					                   'content'     => $reply_list['content'],
					                   'create_time' => time());
					$media_uid = WeixinMediaMod::add_or_edit_weixin_media($media);
					break;
				case 'img':
					$media_uid = '';
					break;
				case 'voice':
					$media_uid = '';
					break;
				case 'video':
					$media_uid = '';
					break;
				case 'news':
					count($reply_list['news_info']['list']) < 1 && exit('error count of news');
					$media     = array('media_type'  => (count($reply_list['news_info']['list']) == 1 ? 2 : 3),
					                   'sp_uid'      => $this->sp_uid,
					                   'content'     => $this->get_news_content($reply_list['news_info']),
					                   'create_time' => time());
					$media_uid = WeixinMediaMod::add_or_edit_weixin_media($media);
					break;
			}
			if (empty($media_uid))
			{
				$this->call_back['error'][] = 'miss media_uid';
			}
			foreach ($autoreply['keyword_list_info'] as $keyword_list)
			{
				$data                             = WeixinMediaMod::get_weixin_media_by_uid($media_uid);
				$keyword                          = array('keyword'    => $keyword_list['content'],
				                                          'public_uid' => $this->public_uid,
				                                          'data'       => $data);
				$this->call_back['set_keyword'][] = Keywords_WxPlugMod::add_or_edit_public_keywords($keyword);
			}


		}
	}

	public function get_news_content($news)
	{

		$content = array();
		foreach ($news['list'] as $new)
		{
			$i['Title'] = isset($new['title']) ? checkString($new['title']) : '';
			//			$i['Author'] = isset($new['Author']) ? checkString($new['Author']) : '';//yhc
			$i['Description'] = isset($new['digest']) ? checkString($new['digest']) : '';
			$i['PicUrl']      = isset($new['cover_url']) ? checkString($new['cover_url'], PATTERN_URL) : '';
			$i['Url']         = isset($new['content_url']) ? checkString($new['content_url'], PATTERN_URL) : '';

			$content[] = $i;
		}

		return $content;
	}

}

