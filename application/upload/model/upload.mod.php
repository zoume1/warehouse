<?php


class UploadMod {
	/*
		为文件增加一个访问url			
		uidm,在uid后增加md5信息防止遍历下载
	*/
	public static function fill_file_url($file)
	{
		$file['url'] = '?_a=upload&_u=index.out&uidm=' . $file['uid'] . substr($file['md5'], 0, 4);

		return $file;
	}

	public static function func_get_file_list($file)
	{
		$file['url'] = '?_a=upload&_u=index.out&uidm=' . $file['uid'] . substr($file['md5'], 0, 4);
		unset($file['md5']);
		unset($file['file_size']);

		return $file;
	}


	//根据 url 取文件路径
	public static function get_file_dst_by_url($url)
	{

		if (substr($url, 0, 1) == '?')
		{
			$muid    = strtr($url, array('?_a=upload&_u=index.out&uidm=' => ''));
			$file_id = substr($muid, 0, -4);
			$ret     = self::get_file_dst($file_id);
		}
		else
		{
			$ret = $url;
		}

		return $ret;
	}

	/*
		获取文件保存路径, 自动创建目录
	*/
	public static function get_file_dst($file)
	{
		if (is_numeric($file))
		{
			$file = array('uid' => $file);
		}

		$path = UPLOAD_PATH . ceil($file['uid'] / 256) . DS;
		if (!file_exists($path))
		{
			mkdir($path, 0777);
			chmod($path, 0777);
		}

		return $path . $file['uid'];
	}

	/*
	*/
	protected static function check_upload_file($fs)
	{
		if ($fs['error'] != 0)
		{
			setLastError(ERROR_DBG_STEP_2);

			return false;
		}
		if (!is_uploaded_file($fs['tmp_name']))
		{
			setLastError(ERROR_DBG_STEP_3);

			return false;
		}
		if ($fs['size'] > MAX_UPLOAD_SIZE)
		{
			setLastError(ERROR_OUT_OF_LIMIT);

			return false;
		}
		if (!($fs['name'] = checkString($fs['name'], PATTERN_FILE_NAME)))
		{
			//文件名警告
			setLastError(ERROR_INVALID_REQUEST_PARAM);
		}

		return $fs;
	}

	/*
		单文件上传
		@param $fs  php $_FILES[xx] 数据结构 
		@return array('uid' => 文件uid
					  'url' => 文件访问地址 
					  ...)
	*/
	public static function do_upload_one($fs)
	{
		if (!($fs = self::check_upload_file($fs)))
		{
			return false;
		}
		Event::handle('BeforeUploadOne', array($fs));

		//已经存在，直接返回
		$md5 = md5_file($fs['tmp_name']);
		if ($file = Dba::readRowAssoc('select * from files where md5 = "' . $md5 . '"'))
		{
			$dst = self::get_file_dst($file);
			if (!file_exists($dst))
			{
				move_uploaded_file($fs['tmp_name'], $dst);
			}
			setLastError(ERROR_OBJECT_ALREADY_EXIST);
			Event::handle('AfterUploadOne', array($file));

			return self::fill_file_url($file);
		}

		$file = array('md5'         => $md5,
		              'file_name'   => $fs['name'],
		              'file_size'   => $fs['size'],
		              'create_time' => $_SERVER['REQUEST_TIME']);

		if($file_name = requestString('real_name', PATTERN_FILE_NAME)) {
			$file['file_name'] = $_REQUEST['real_name'];
		}

		Dba::beginTransaction();
		{
			Dba::insert('files', $file);
			$file['uid'] = Dba::insertID();
			$dst         = self::get_file_dst($file);
			if (!move_uploaded_file($fs['tmp_name'], $dst))
			{
				Dba::rollBack();
				setLastError(ERROR_DBG_STEP_4);
				
				return false;
			}
			chmod($dst, 0777);
			Event::handle('AfterUploadOne', array($file));
		}
		Dba::commit();

		return self::fill_file_url($file);
	}

	
	/*
		多文件上传
	*/
	public static function do_upload_multi($fss)
	{
		$ret = array();

		$cnt = count($fss['tmp_name']);
		for ($i = 0; $i < $cnt; $i++)
		{
			$fs    = array(
				'name'     => $fss[$i]['name'],
				'type'     => $fss[$i]['type'],
				'tmp_name' => $fss[$i]['tmp_name'],
				'error'    => $fss[$i]['error'],
				'size'     => $fss[$i]['size'],
			);
			$ret[] = self::do_upload_one($fs);
		}

		return $ret;
	}

	/*
		输出文件
	*/
	public static function out($uid, $md5_pre, $w = 0, $h = 0)
	{
		if (!($file = Dba::readRowAssoc('select * from files where uid = ' . $uid)) ||
			strncmp($md5_pre, $file['md5'], strlen($md5_pre)) != 0
		)
		{
			header('HTTP/1.0 404 Not Found');
			exit();
		}

		//304支持
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			if ($file['create_time'] == $_SERVER['HTTP_IF_MODIFIED_SINCE'])
			{
				header('Cache-Control: public');
				header('Last-Modified:' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
				exit();
			}
		}
		
		if(defined('FILES_SPEED_UP') && FILES_SPEED_UP)
		FilespeedupMod::out($uid);

		$dst = self::get_file_dst($file);
		if (!file_exists($dst))
		{
			header('HTTP/1.0 404 Not Found');
			exit();
		}

		header('Cache-Control: public');
		header('Last-Modified: ' . $file['create_time']);
		
		//完善一下 http://tool.oschina.net/commons
		$ct = array(
			'.jpeg' => 'image/jpeg',
			'.jpg'  => 'image/jpeg',
			'.png'  => 'image/png',
			'.gif'  => 'image/gif',

			'.mp3'  => 'audio/mp3',
			'.wav'  => 'audio/wav',

			'.pdf'  => 'application/pdf',
			'.ppt'  => 'application/x-ppt',
			'.pptx'  => 'application/x-ppt',
			'.xls'  => 'application/x-xls',
			'.xlsx'  => 'application/x-xls',
			'.doc'  => 'application/msword',
			'.docx'  => 'application/msword',
		);

		$ext = strtolower(strrchr($file['file_name'], '.'));
		if (isset($ct[$ext]))
		{

			// 判断图片类型 gif 跳过 只压缩非gif 图片
			if (!($imginfo = getimagesize($dst)) || empty($w) || empty($h) || end($imginfo) == 'image/gif')
			{
				header('Content-Type: ' . $ct[$ext]);
			}
			else
			{
				include_once UCT_PATH . 'vendor/images/image.php';
				$image = getImageineInstance();
				$image->show_thumbnail($dst, $w, $h);
//				$image->img2thumb($dst,'' ,$w, $h);

				exit;
			}

		}

		/*
		//header('E-tag: '.$uid.$md5_pre.$file['create_time']);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $file['name']);
		header('Content-Transfer-Encoding: binary');
		*/
		if (defined('USE_X_SEND_FILE') && USE_X_SEND_FILE)
		{
			//			var_dump(__file__.' line:'.__line__,$w,$h);exit;

			$dst = '/x_send_file/' . substr($dst, strlen(UPLOAD_PATH));
			if (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false)
			{
				//this one for nginx
				header('X-Accel-Redirect: ' . $dst);
			}
			else
			{
				//this is for apache 
				header('X-Sendfile: ' . $dst);
			}
		}
		else
		{
			readfile($dst);
		}
		exit();
	}

	/*
		上传统计
	*/
	public static function onAfterUploadOne($file)
	{
		$type = requestInt('type', 1);

		uct_use_app('sp');
		if ($user_id = SpMod::has_sp_login())
		{
			//3为微信服务下载
			$from = isset($file['from']) ? 3 : 1;

		}
		else if(!empty($_SESSION['admin_login'])) { //入驻商后台
			$user_id = Dba::readOne('select sp_uid from shop join shop_biz on shop.uid = shop_biz.shop_uid where shop_biz.uid =  '.$_SESSION['admin_login']);	
			$from = 1;
		}
		else
		{
			$from    = 2;
			$user_id = AccountMod::has_su_login();
		}

		//假如为超级登陆
		if ($GLOBALS['_UCT']['APP'] == 'admin')
		{
			uct_use_app('admin');
			$user_id = 0;
		}
		$file_group = isset($GLOBALS['_TMP']['load_file_group']) ? $GLOBALS['_TMP']['load_file_group'] : '0';

		$sql = 'select uid from files_user_upload_stat where user_id = ' . $user_id .
			' && `from` = ' . $from . ' && file_id = ' . $file['uid'];
		if (!($uid = Dba::readOne($sql)))
		{
			$insert = array('user_id'     => $user_id,
			                'from'        => $from,
			                'file_id'     => $file['uid'],
			                'type'        => $type,
			                //			                'create_time' => $file['create_time'],
			                'create_time' => $_SERVER['REQUEST_TIME'],//更新时间
			                'file_group'  => $file_group);
			Dba::insert('files_user_upload_stat', $insert);
		}
		else
		{
			Dba::update('files_user_upload_stat', array('create_time' => $_SERVER['REQUEST_TIME'],
			                                            'file_group'  => $file_group), 'uid =' . $uid);
		}
	}

	/*
		获取用户上传文件列表
	*/
	public static function get_user_file_list($option, $page = 0, $limit = 10)
	{
		$sql = 'from files_user_upload_stat ' .
			'join files on files_user_upload_stat.file_id = files.uid';
		if (!empty($option['from']))
		{
			$where_arr[] = '(files_user_upload_stat.from = ' . $option['from'] . ' or files_user_upload_stat.from = 3)';
		}
		if (isset($option['user_id']))
		{
			$where_arr[] = 'files_user_upload_stat.user_id = ' . $option['user_id'];
		}
		if (!empty($option['type']))
		{
			$where_arr[] = 'files_user_upload_stat.type = ' . $option['type'];
		}
		if (!empty($option['file_group']))
		{
			$where_arr[] = 'files_user_upload_stat.file_group = ' . $option['file_group'];
		}
		if (!empty($option['no_in_weixin']))
		{

			$where_arr[] = 'files.uid not in (select file_id from weixin_material where public_uid =' . $option['public_uid'] . ')';
		}
		
		if (isset($where_arr))
		{
			$sql .= ' where ' . implode(' and ', $where_arr);
		}

		$sql_cnt = 'select count(*) ' . $sql;

		$sql = 'select files.*, files_user_upload_stat.create_time ' . $sql;

		$sql .= ' order by files_user_upload_stat.create_time desc';
		if ($limit >= 0)
		{
			$sql .= ' limit ' . (($page) ? ($page * $limit) . ', ' . $limit : $limit);
		}
//		var_dump($sql);exit;
		$count = Dba::readOne($sql_cnt);
		$list  = Dba::readAllAssoc($sql, 'UploadMod::func_get_file_list');

		return array('count' => $count, 'list' => $list);
	}

	/*
		
	*/
	public static function upload_sp_private($fs, $sp_uid, $type)
	{
		$to = array(
			'1' => CERT_PATH . $sp_uid . '/wx/apiclient_cert.pem',  //微信支付证书
			'2' => CERT_PATH . $sp_uid . '/wx/apiclient_key.pem',   //
		);
		if (empty($to[$type]))
		{
			setLastError(ERROR_INVALID_REQUEST_PARAM);

			return false;
		}
		$path = dirname($to[$type]);
		if (!file_exists($path))
		{
			mkdir($path, 0777, true);
			chmod($path, 0777);
		}


		if (!($fs = self::check_upload_file($fs)))
		{
			return false;
		}

		return move_uploaded_file($fs['tmp_name'], $to[$type]);
	}
	

	/*
		检查从微信服务器下载的内容
	*/
	public static function check_download_file($fs)
	{

		if (!isset($fs['http_code']) || $fs['http_code'] != 200)
		{
			return false;
		}

		if (!isset($fs['download_content_length']) || (!isset($fs['body'])) || ($fs['download_content_length'] != strlen($fs['body'])))
		{
			return false;
		}

		return true;
	}
	
	public static function download_weixin($fs)
	{
		if (!self::check_download_file($fs))
		{
			return false;
		}

		Event::handle('BeforeUploadOne', array($fs));


		//已经存在，直接返回

		$md5 = md5($fs['body']);
		if ($file = Dba::readRowAssoc('select * from files where md5 = "' . $md5 . '"'))
		{
			$dst = self::get_file_dst($file);
			if (!file_exists($dst))
			{
				file_put_contents($dst, $fs['body']);

			}
			Event::handle('AfterUploadOne', array($file));

			return $file['uid'];
		}

		$file = array('md5'         => $md5,
		              'file_name'   => $fs['name'],
		              'file_size'   => $fs['download_content_length'],
		              'create_time' => $_SERVER['REQUEST_TIME']);
		// Dba::beginTransaction(); {
		Dba::insert('files', $file);
		$file['uid'] = Dba::insertID();
		$dst         = self::get_file_dst($file);
		if (!file_put_contents($dst, $fs['body']))
		{
			Dba::rollBack();

			setLastError(ERROR_DBG_STEP_4);

			return false;
		}
		chmod($dst, 0777);
		$file['from'] = 3;
		Event::handle('AfterUploadOne', array($file));

		// } Dba::commit();
		return $file['uid'];
	}

	//取商户分组列表 带数目
	public static function get_file_group_list_with_cnt($option) {
		$type = 1;	
		$sp_uid = isset($option['sp_uid']) ? $option['sp_uid'] : 
					AccountMod::get_current_service_provider('uid');
		$gs = UploadMod::get_file_group_list($sp_uid, $type);
		if(empty($gs['list'])) {
			return array();
		}
		foreach($gs['list']  as $k => $v) {
			$gs['list'][$k] = array(
				'title' => $v,
				'cnt' => Dba::readOne('select count(*) from files_user_upload_stat where user_id = '.$sp_uid.' && `from` = '.$type.' && file_group = '.$k),
			);
		}

		return $gs;
	}

	//获取用户文件分组列表
	public static function get_file_group_list($sp_uid, $type)
	{
		isset($sp_uid) || $sp_uid = AccountMod::get_current_service_provider('uid');
		$file_group_list = empty($GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid]) ? array() : json_decode($GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid], true);
		empty($file_group_list[$type]) && $file_group_list[$type]['1'] = '默认';
		$GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid] = json_encode($file_group_list);
		$ret['count']                                         = count($file_group_list[$type]);
		$ret['list']                                          = $file_group_list[$type];

		return $ret;
	}

	//$new_file_group_lists =array('1'=>'我的','2'=>'')
	public static function add_or_edit_file_group_list($new_file_group_lists, $sp_uid, $type)
	{
		isset($sp_uid) || $sp_uid = AccountMod::get_current_service_provider('uid');
		$file_group_list = $GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid];
		$file_group_list = json_decode($file_group_list, true);

		foreach ($new_file_group_lists as $nlk => $nl)
		{
			if ($nlk == 1)
			{
				setLastError('ERROR_DBG_STEP_1');//1 是默认分组 不给改名字
				continue;
			}
			if (in_array($nl, $file_group_list[$type]))
			{
				setLastError('ERROR_DBG_STEP_2');//重名 不给予修改
				continue;
			}
			isset($file_group_list[$type][$nlk]) ? ($file_group_list[$type][$nlk] = $nl) : ($file_group_list[$type][] = $nl);
		}
		//		var_dump($file_group_list, __file__ . ' line:' . __line__);
		//		var_dump( json_encode($file_group_list),__file__.' line:'.__line__);
		$GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid] = json_encode($file_group_list);

		return $file_group_list[$type];

	}

	/*
	 *  删除文件分组
	 *  from 1 商户上传 2 用户上传 3 微信下载
	 *  sp_uid 为0 时 是 公用 素材
	 */
	public static function delete_a_file_group($sp_uid, $type, $file_group_id, $from = array('1', '3'))
	{
		isset($sp_uid) || $sp_uid = AccountMod::get_current_service_provider('uid');
		$file_group_list = $GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid];
		$file_group_list = json_decode($file_group_list, true);
		$where           = 'file_group = ' . $file_group_id . ' and user_id=' . $sp_uid .
			' and ( `from` =' . trim(implode($from, ' or `from` ='), 'or') . ') and type =' . $type;
		$count           = Dba::readOne('select count(*) from files_user_upload_stat where ' . $where);

		if (isset($file_group_list[$type][$file_group_id]))
		{
			//删除分组
			unset($file_group_list[$type][$file_group_id]);
			//将该分组内 文件重新分到 未分组
			$ret = Dba::update('files_user_upload_stat', array('file_group' => '1'), $where);
			if (!$ret && $count > 0)
			{

				return false;
			}
			$GLOBALS['arraydb_sys']['file_group_list_' . $sp_uid] = json_encode($file_group_list);

			return $file_group_list[$type];
		}
		else
		{
			return $file_group_list[$type];
		}
	}

	/*
	 * 修改 文件分组
	 */
	public static function edit_user_file_group($file)
	{
		if (empty($file['uid']))
		{
			return false;
		}
		$group = array('file_group' => $file['file_group']);

		return Dba::update('files_user_upload_stat', $group, 'uid = ' . $file['uid']);
	}

	/*
	 * 获取用户文件信息
	 */
	public static function get_user_file_info_by_uid($uid)
	{
		return Dba::readRowAssoc('select * from files_user_upload_stat where uid=' . $uid, 'UploadMod::func_get_user_file_info');
	}

	public static function func_get_user_file_info($item)
	{
		if (!empty($item['file_id']))
		{
			$item['file'] = self::get_file_by_uid($item['file_id']);
		}

		return $item;
	}

	/*
	 * 取文件详细信息
	 */
	public static function get_file_by_uid($uid)
	{
		return Dba::readAllAssoc('select * from files where uid=' . $uid, 'UploadMod::func_get_file_list');
	}

	//删除图片信息  删除文件  只删除文件和用户映射信息
	public static function delete_file_info($uids, $sp_uid = 0)
	{
		if (is_numeric($uids))
		{
			$uids = array($uids);
		}

		return Dba::write('delete from files_user_upload_stat where uid in (' . implode(',', $uids) . ') and user_id=' . $sp_uid);
	}

	//删除图片信息  删除文件  只删除文件和用户映射信息
	public static function delete_file_info_by_file_id($uids, $sp_uid = 0)
	{
		if (is_numeric($uids))
		{
			$uids = array($uids);
		}

		return Dba::write('delete from files_user_upload_stat where file_id in (' . implode(',', $uids) . ') and user_id=' . $sp_uid);
	}



	//取无拥有者的文件列表
	public static function get_files_list_out_owner($option)
	{
		$page  = (isset($option['page']) ? $option['page'] : 0);
		$limit = (!empty($option['limit']) ? $option['limit'] : 10);
		$sql   = 'select * from files where';
		$sql .= ' uid not in (select file_id from files_user_upload_stat)';
		$sql .= ' and uid not in (select file_id from news_content_image)';
		$sql .= ' order by uid desc ';
		$sql .= ' limit ' . (($page) ? ($page * $limit) . ', ' . $limit : $limit);

		return Dba::readCountAndLimit($sql);
	}

	//删除文件
	public static function delete_files($uids)
	{
		if (is_numeric($uids))
		{
			$uids = array($uids);
		}
		$count1 = Dba::readOne('select count(*) from  files_user_upload_stat  where file_id in (' . implode(',', $uids) . ')');
		$count2 = Dba::readOne('select count(*) from  news_content_image  where file_id in (' . implode(',', $uids) . ')');
		$count  = $count1 + $count2;
		if ($count > 0)
		{
			setLastError(ERROR_DBG_STEP_1);

			return false;
		}

		Dba::beginTransaction();
		{
			$ret = Dba::write('delete from files where uid in (' . implode(',', $uids) . ') ');
			foreach ($uids as $uid)
			{
				$dst = self::get_file_dst($uid);
				file_exists($dst) && unlink($dst);

			}
		}
		Dba::commit();

		return $ret;
	}

	/*
		@alias upload_vip_imge

		@param $data 文件数据
		@param $name 文件名

		@return @see do_upload_one

		保存文件数据		
	*/
	public static function save_as_upload_file($data, $name = '') {
		return self::upload_vip_imge($data, $name);
	}

	//保存 生成的会员卡照片
	public static function upload_vip_imge($fs, $fname = '')
	{

		//已经存在，直接返回
		$md5 = md5($fs);
		if ($file = Dba::readRowAssoc('select * from files where md5 = "' . $md5 . '"'))
		{
			$dst = self::get_file_dst($file);
			if (!file_exists($dst))
			{
				file_put_contents($dst, $fs);
				setLastError(ERROR_OBJECT_ALREADY_EXIST);
			}
			Event::handle('AfterUploadOne', array($file));

			return self::fill_file_url($file);
		}
		if(!$fname) $fname = md5(microtime()) . ".png";
		$file = array('md5'         => $md5,
		              'file_name'   => $fname,
		              'file_size'   => strlen($fs),
		              'create_time' => $_SERVER['REQUEST_TIME']);
		Dba::beginTransaction();
		{
			Dba::insert('files', $file);
			$file['uid'] = Dba::insertID();
			$dst         = self::get_file_dst($file);
			if (!file_put_contents($dst, $fs))
			{
				Dba::rollBack();
				Weixin::weixin_log('file put_contents error! '.$dst.', file size '.strlen($fs));
				setLastError(ERROR_DBG_STEP_4);

				return false;
			}
			chmod($dst, 0777);
			Event::handle('AfterUploadOne', array($file));
		}
		Dba::commit();

		return self::fill_file_url($file);
	}

}

