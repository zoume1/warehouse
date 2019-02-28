<?php
/*
	把图片放到其他服务器上去
	2018-07-25
	lhliu

	可以是 jsd1.uctphp.com
	或者 qiniu.com 等地方
*/

class FilespeedupMod {
	//不启用的server_id
	static $disabled_svrs = array();

	/*
		
		成功重定向到文件地址
		失败返回false
	*/
	public static function out($uid) {
		$f = Dba::readRowAssoc('select * from files_speed_up where file_id = '.$uid);
		if(!$f || $f['status'] || in_array($f['server_id'], self::$disabled_svrs) || !$f['url']) {
			return false;
		}

		if(isset($_GET['w']))$f['url'].='&w='.$_GET['w'];
		if(isset($_GET['h']))$f['url'].='&h='.$_GET['h'];
		redirectTo($f['url']);
	}

	
	public static function add_file_speed_up($uid) {
		$f = Dba::readRowAssoc('select * from files_speed_up where file_id = '.$uid);
		if($f && $f['url']) {
			return $f;
		}
		
		if(!($file = Dba::readRowAssoc('select * from files where uid = '.$uid)) || !($dst = UploadMod::get_file_dst($uid))) {
			return false;
		}
		
		if (class_exists('\CURLFile', false)) {
			$data = array('file' => new \CURLFile($dst, '', $file['file_name']));
		}
		else {
			$data = array('file' => '@' . $dst . ';filename=' . $file['file_name'],);
		}
		$url = 'http://jsd1.uctphp.com/?_a=upload&_u=index.upload';
		$ret = Weixin::weixin_upload_file($url, $data);	
		#var_export($ret);
		if(!$ret || !($ret = json_decode($ret, true)) || empty($ret['data']['url'])) {
			return false;
		}
				
		$url = 'http://jsd1.uctphp.com/'.$ret['data']['url'];
		$f = array('file_id' => $uid,
					'create_time' => time(),
					'status' => 0, //0 生效， 1 失效
					'server_id' => 1,
					'url' => $url,
		);
		Dba::replace('files_speed_up', $f);
		$f['uid'] = Dba::insertID();

		return $f;
	}

}

