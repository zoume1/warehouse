<?php
/*
	文件上传下载
*/

class IndexCtl {
	public function __construct() {
		uct_use_app('sp');
		Event::addHandler('AfterUploadOne', array('UploadMod', 'onAfterUploadOne'));
	}

	/*
		文件上传
		参数          必须      说明
		file_group   否        默认 1 设定文件上传后分组
	*/
	public function upload() {
		//todo 检查一下是否登陆
		$GLOBALS['_TMP']['load_file_group'] = requestInt('file_group',1);
		$src = 'file';		
		if (!isset($_FILES[$src])) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		$fs = $_FILES[$src];
		if (is_array($fs['tmp_name'])) {
			outRight(UploadMod::do_upload_multi($fs));
		}

		outRight(UploadMod::do_upload_one($fs));
	}

	/*
		文件输出
	*/
	public function out() {
		if(!($uidm = requestString('uidm', PATTERN_UIDM))) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$uid = substr($uidm, 0, -4);	
		$md5_pre = substr($uidm, -4);
		 $w = requestInt('w');
		 $h = requestInt('h');
		outRight(UploadMod::out($uid, $md5_pre,$w,$h));
	}

	public function crop() {
		//支持完整图片url地址
		if(!($uidm = requestString('uidm', '/uidm=(\d+[\da-z]{4})/')) &&
		   !($uidm = requestString('uidm', PATTERN_UIDM))) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$uid = substr($uidm, 0, -4);	
		$md5_pre = substr($uidm, -4);	

		$option = array('md5_pre' => $md5_pre);
		$option['a'] = requestFloat('aspect', 1);
		$option['w'] = requestInt('width', 64);
		$option['h'] = requestInt('height', 64);
		$option['x'] = requestInt('x');
		$option['y'] = requestInt('y');

		outRight(ImageProcessMod::crop_avatar($uid, $option));
	}

	/*
		获取服务商图片列表
		参数             必要       说明
		page            是         分页输出所需参数
		limit           否         分页输出所需参数
		no_in_weixin    否         仅在上传到微信上时使用
		public_imge     否         存在时返回公共图片
		file_group      否         存在时返回此分组下图片
	*/
	public function sp_img_list() {
		$page = requestInt('page');
		$limit = requestInt('limit', 10);
		$no_in_weixin = requestInt('no_in_weixin',0);
		$public_image = requestInt('public_image',0);
		$option['type'] = '1';
		if (SpMod::has_sp_login()) { //商户后台
			$option['user_id'] =  AccountMod::get_current_service_provider('uid');
			$option['from'] = '1';
			$option['no_in_weixin'] = $no_in_weixin?1:0;
			$option['public_uid'] = WeixinMod::get_current_weixin_public( 'uid' );
			isset($_REQUEST['file_group']) && $option['file_group'] =requestInt('file_group');
		}
		else if(!empty($_SESSION['admin_login'])) { //入驻商后台
			$sp_uid = Dba::readOne('select sp_uid from shop join shop_biz on shop.uid = shop_biz.shop_uid where shop_biz.uid =  '.$_SESSION['admin_login']);	
			$option['user_id'] =  $sp_uid;
			$option['from'] = '1';
		}
		else {
		//todo 是否限制访问 商家登录，图片管理
		}
		empty($public_image) || $option['user_id'] = '0';
		$ret = UploadMod::get_user_file_list($option, $page, $limit);
		$ret['pagination'] = uct_pagination($page, ceil($ret['count']/$limit), 'javascript:;" useless="');
		return outRight($ret);
	}



	/*
		服务商上传私有文件
	*/
	public function uploadprivate() {
		if (!SpMod::has_sp_login()) {
			outError(ERROR_USER_HAS_NOT_LOGIN);
		}
		$src = 'file';		
		if (!isset($_FILES[$src]) || is_array($_FILES[$src]['tmp_name'])) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		if(!($type = requestInt('type'))) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}

		outRight(UploadMod::upload_sp_private($_FILES[$src], AccountMod::get_current_service_provider('uid'), $type));
	}

	/*
	 * 获取文件分组
	 * 参数           必要          说明
	 * type          否            1图片, 2视频, 3音频, 4文档, 5程序... 默认1
	 */
	public function get_file_group()
	{
		if (!($sp_uid = AccountMod::get_current_service_provider('uid')))
		{
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		isset($_REQUEST['public_image']) && $sp_uid = 0;
		$type = requestInt('type','1');

		if(!empty($_GET['with_cnt'])) 
		outRight(UploadMod::get_file_group_list_with_cnt(array('sp_uid' => $sp_uid)));

		outRight(UploadMod::get_file_group_list($sp_uid,$type));
	}


	/*
	* 编辑分组
	* 参数                   必要              说明
	* file_group_list       是                用于修改组名和添加新分组   {"2":"我的分组"} 当编号为2的分组存在时 更新组名为【我的分组】 不存在时添加【我的分组】
	 *                                        编号 1 为默认 分组 不给予改名字
	*/
	public function edit_file_group_list()
	{
		if (!($sp_uid = AccountMod::get_current_service_provider('uid')))
		{
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$type = requestInt('type','1');

		$new_file_group_lists = requestKvJson('file_group_list');
		outRight(UploadMod::add_or_edit_file_group_list($new_file_group_lists,$sp_uid,$type));
	}

	/*
	 *     删除分组
	 *     参数           必须              说明
	 *     type          否                1图片, 2视频, 3音频, 4文档, 5程序... 默认1
	 *     file_group_id 是                要删除分组的 索引id
	 *     from          否                素材材料 1 商户上床 2用户上传 3 微信上传 默认 1;3
	 */
	public function delete_file_group()
	{
		if (!($sp_uid = AccountMod::get_current_service_provider('uid')))
		{
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$type = requestInt('type','1');
		$file_group_id  = requestInt('file_group_id');
		$from = isset($_REQUEST['from'])?requestString('from'):array('1','3');
		if(empty($file_group_id) || !$from)
		{
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		outRight(UploadMod::delete_a_file_group($sp_uid ,$type,$file_group_id,$from));
	}

	/*
	 *        编辑用户文件分组
	 *        参数                必须              说明
	 *        uid                是                用户文件uid
	 *        type               否                默认 1
	 *        file_group_id      是                文件新分组id
	 */
	public function edit_file_group()
	{
		if (!($sp_uid = AccountMod::get_current_service_provider('uid')))
		{
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		if(!($uid = requestInt('uid')) || !($file = UploadMod::get_user_file_info_by_uid($uid)) ||!($sp_uid = $file['user_id']))
		{
			//			var_dump(__file__.' line:'.__line__,$file);
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$type = requestInt('type',1);
		$file_group_list = UploadMod::get_file_group_list($sp_uid,$type);

		if(!($file_group_id = requestInt('file_group_id')) || !isset($file_group_list['list'][$type][$file_group_id]))
		{
			//			var_dump(__file__.' line:'.__line__,$file_group_list);exit;
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
		$file['file_group'] = $file_group_id;
		outRight(UploadMod::edit_user_file_group($file));
	}

}

