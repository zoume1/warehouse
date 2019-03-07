<?php
// 刷新所有一键接入账号的信息  2015年10月7日10:15:47 修复了微信文档的bug 同步了二维码  并不常用的一个任务
class SyncaWeixinpublicJob {
	public function perform($r) {
		$sp_uid=$r['sp_uid'];
		$public_uid = $r['uid'];
		$authorizer_appid = $r['app_id'];
		$ret_query_auth='';
		$ret_authorizer_info=ComponentMod::get_authorizer_info($authorizer_appid);
		Weixin::weixin_log('ret_authorizer_info  ===== '.json_encode($ret_authorizer_info));
		$public_uid=ComponentMod::add_or_edit_weixin_public($ret_query_auth,$ret_authorizer_info,$sp_uid);
		var_dump($public_uid);
	}

}

