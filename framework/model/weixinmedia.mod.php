<?php
//todo 增加小程序卡片 4

class WeixinMediaMod {
	public static function get_full_url_path($url) {
		if(!strncasecmp($url, 'http', 4)) {
			return $url;
		}

		return getUrlName() . $url;
	}

	
	/*
		回复素材消息 
		//todo 可以支持模板
		$media 素材内容数组, 或者素材id
	*/
	public static function weixin_reply_media($media) {
		if(is_numeric($media)) {
			$media = self::get_weixin_media_by_uid($media);
		}
		if(!$media) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return false;
		}

		switch($media['media_type']) {
			case 1: {
				return Weixin::weixin_reply_txt($media['content']);
			}
			case 2: 
			case 3: {
				return Weixin::weixin_reply_news($media['content']);
			}
			
			default: {
				setLastError(ERROR_UNKNOWN_DB_ERROR);
				return false;
			}
		}
	}

	/*
     *给小程序-商户发送客服提示
 	 */
	public static function xiaochengxu_reply_media($media,$sp_uid=0){

		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
			setLastError(ERROR_INVALID_REQUEST_PARAM);
			return false;
		}
		$public_uid = WeixinMod::get_current_weixin_public('uid');
		$public = WeixinMod::get_weixin_public_by_uid($public_uid);
		$access_token = WeixinMod::get_weixin_access_token();

		if($public['public_type'] != 8){
			return false;
		}
		//客服消息
		if(empty($GLOBALS['weixin_args'])){
			return false;
		}
		if($GLOBALS['weixin_args']['MsgType'] == 'event'){
			return false;
		}


		switch($media['media_type']) {
			case 1: {
				$data = array(
					'touser'  =>  $GLOBALS['weixin_args']['FromUserName'],
					'msgtype' => 'text',
					'text' 	  => array(
						'content' =>  $media['content']
					)
				);
				break;
			}
			case 2:{
				$data = array(
					"touser" => $GLOBALS['weixin_args']['FromUserName'],
					"msgtype"=> "link",
					"link"   => array(
						"title"      => $media['content']['Title'],
						"description"=> $media['content']['Description'],
						"url"        => $media['content']['Url'],
						"thumb_url"  => $media['content']['PicUrl']
					)
				);
				break;
			}
			default: {
				setLastError(ERROR_UNKNOWN_DB_ERROR);
				break;
			}
		}

		Weixin::weixin_send_custom_msg($data,$access_token);

	}

	public static function func_get_weixin_media($item) {
		if(is_numeric($item)) {
			return WeixinMediaMod::get_weixin_media_by_uid($item);
		}

		//单图文和多图文保存的是json格式
		if(isset($item['media_type']) && ($item['media_type'] == 2 || $item['media_type'] == 3)) {
			if(is_string($item['content']))
			{
				$item['content'] = json_decode($item['content'], true);
				$item['content'] = ($item['content']!='uctnull' )?$item['content'] :'';
			}

			//图片地址转为绝对地址
			if($item['content'])
			foreach($item['content'] as $k => $v) {
				if(!empty($v['PicUrl'])) {
					$item['content'][$k]['PicUrl'] = self::get_full_url_path($v['PicUrl']);
				}
				if(!empty($v['Url'])) {
					$item['content'][$k]['Url'] = self::get_full_url_path($v['Url']);
				}
			}

		}
		else {
			//先去掉, 微信某些表情中含有<会被过滤掉导致不显示
			//$item['content'] = XssHtml::clean_xss($item['content'], array('a'));
			//换行
			if(1 || uct_is_weixin_page()) {
				$item['content'] = str_replace(array('<br>', '\n', "\n"), 
						array(PHP_EOL, PHP_EOL, PHP_EOL), $item['content']);
			}
		}

		return $item;
	}

	public static function get_weixin_media_by_uid($uid) {
		return Dba::readRowAssoc('select * from weixin_media where uid = '.$uid, 'WeixinMediaMod::func_get_weixin_media');
	}

	public static function delete_weixin_media($uids, $sp_uid = 0) {
		if(!$sp_uid && !($sp_uid = AccountMod::get_current_service_provider('uid'))) {
		    setLastError(ERROR_INVALID_REQUEST_PARAM);
		    return false;
		}

		if(!is_array($uids)) {
			$uids = array($uids);
		}
		$sql = 'delete from weixin_media where uid in ('.implode(',',$uids).') and sp_uid = '.$sp_uid;
		$ret = Dba::write($sql);

		return Dba::affectedRows();
	}

	public static function add_or_edit_weixin_media($media) {
		if(is_array($media['content'])) {
			$media['content'] = json_encode($media['content']);
		}

		if(!empty($media['uid'])) {
			Dba::update('weixin_media', $media, 'uid = '.$media['uid'].' and sp_uid = '.$media['sp_uid']);
		}
		else {
			$media['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('weixin_media', $media);
			$media['uid'] = Dba::insertID();
		}

		return $media['uid'];
	}

	public static function get_weixin_media($option) {
		$sql = 'select * from weixin_media ';
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'sp_uid = '.$option['sp_uid'];
		}
		if(!empty($option['media_type'])) {
			$where_arr[] = 'media_type = '.$option['media_type'];
		}
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}
		$sql .= ' order by uid desc';

		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'WeixinMediaMod::func_get_weixin_media');
	}

}


