<?php


class WeixinCtl {
	public function index() {
		echo 'hello uct php! this is the default index control!'.PHP_EOL;
	}

	/*
		腾讯微信接口回调地址入口
	*/
	public function tencent_callback() {
		//1. 是否为微信验证调用
		Weixin::weixin_validate(WeixinMod::get_tencent_token());

		//2. 解析微信传入参数, 确定公众号、粉丝信息
		WeixinMod::prepare_weixin_session();

		//3. 微信业务逻辑
		WeixinMod::process_weixin_msg();

	}

	public function debug() {
		$text = requestString('text', '', '呵呵');
		$post = '<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName> 
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA['.$text.']]></Content>
<MsgId>1234567890123456</MsgId>
</xml>
';
//		$_GET['uct_token'] = '444b9871d32edfee3f9441bb7c9baaef';
		$_GET['uct_token'] = (empty($_GET['uct_token'])?'018e42c0a8547ae75180e1ce4eb8dd7b':$_GET['uct_token']);
		//$_GET['uct_token'] = 'e77562cb80a49fd734ae565e489e7386';
		
		libxml_disable_entity_loader();
		$GLOBALS['weixin_args'] = @ array_map(function($v){ return is_object($v) ? (array)$v : $v;},
								(array)(simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA)));


		WeixinMod::on_init_weixin_session();
		WeixinMod::process_weixin_msg();
	}

	public function test() {
		$public_uid = 1;		
		$wx = Dba::readRowAssoc('select * from weixin_public where uid = '.$public_uid);
		WeixinMod::set_current_weixin_public($wx);
		
		echo '欢迎 =>  '.WeixinMod::get_current_weixin_public('public_name').'<br>';
		echo 'url => '.WeixinMod::get_tencent_callback_url().'<br>';
		echo 'token => '.WeixinMod::get_tencent_token().'<br>';
		
		
	}
	
	
	
}

