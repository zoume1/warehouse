<?php
/*
	
*/

class CommonCtl {
	/*
		统一幻灯片列表
		pos @see SlidesMod::get_pos
	*/
	public function slides_list() {
		$sp_uid = AccountMod::require_sp_uid();
		if(!$pos = requestString('pos', PATTERN_NORMAL_STRING)) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$option = array('sp_uid' => $sp_uid, 'pos' => $pos);

		$slides = SlidesMod::get_slides($option);
		if(!$slides) $slides = array();

		//简单一点不分页
		if(requestInt('page')) $slides = array();

		$ret = array('count' => count($slides), 'list' => $slides);
		outRight($ret);
	}

	/*
		文案
	*/
	public function article() {
		$sp_uid = AccountMod::require_sp_uid();
		uct_use_app('sp');
		if(!($uid = requestInt('uid')) || !($doc = SpMod::get_document_by_uid($uid)) ||
			($doc['sp_uid'] != $sp_uid)) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		outRight($doc);
	}

	/*
		图片地址
		根据不同商户设置，返回不同颜色图片
		?_u=common.img&name=xxxxxx
	*/
	public function img() {
		$sp_uid = AccountMod::require_sp_uid();
		$theme = isset($GLOBALS['arraydb_sys']['theme_'.$sp_uid]) ?
			($GLOBALS['arraydb_sys']['theme_'.$sp_uid]) : 'purple'; 
		#if($sp_uid == 1060)  $theme='green';
		if(!$name = requestString('name', PATTERN_NORMAL_STRING)) {
			header('HTTP/1.0 404 Not Found');
			exit();
		}
		
		$etag = md5($theme.$name);
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
			header('Cache-Control: public');
			header('Etag: '.$etag);
			header("HTTP/1.1 304 Not Modified");
			exit();
		}

		$dst = UCT_PATH.'static/theme/icon_'.$theme.'/'.$name;
		if (!file_exists($dst))
		{
			header('HTTP/1.0 404 Not Found');
			exit();
		}

		//完善一下 http://tool.oschina.net/commons
		$ct = array(
			'.jpeg' => 'image/jpeg',
			'.jpg'  => 'image/jpeg',
			'.png'  => 'image/png',
			'.gif'  => 'image/gif',
		);
		$ext = strtolower(strrchr($name, '.'));
		if (isset($ct[$ext]))
		{
			header('Content-Type: ' . $ct[$ext]);
		}

		//header('Content-Type: image/png');
		header('Cache-Control: public');
		header('Etag: '.$etag);
		//暂不支持send_file
		readfile($dst);
		exit();
	}
}

