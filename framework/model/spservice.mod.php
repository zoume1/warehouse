<?php
/*
	付费服务
*/

class SpServiceMod {
	const PAY_TYPE_NULL = 0; //未设置付款方式
	const PAY_TYPE_FREE = 1; //免费无需付款
	const PAY_TYPE_CACHE = 2; //货到付款
	const PAY_TYPE_BALANCEPAY = 8; //余额支付
	const PAY_TYPE_TESTPAY = 9; //测试支付
	const PAY_TYPE_ALIPAY = 10; //支付宝
	const PAY_TYPE_WEIXINPAY = 11; //微信支付
	

	const ORDER_WAIT_USER_PAY = 1; //待付款
	const ORDER_WAIT_FOR_DELIVERY = 2; //待发货
	const ORDER_WAIT_USER_RECEIPT= 3; //待收货
	const ORDER_DELIVERY_OK = 4; //已收货
	const ORDER_COMMENT_OK = 5; //协商完成
	const ORDER_NEGOTATION_OK = 6; //协商完成
	const ORDER_UNDER_NEGOTATION = 8; //协商中(退货,换货)
	const ORDER_SHOP_CANCELED = 9; //店家已取消
	const ORDER_CANCELED = 10; //已取消
	const ORDER_WAIT_SHOP_ACCEPT = 11; //等待卖家确认
    const ORDER_WAIT_GROUP_DONE = 12; //待成团

	const VIRTUAL_RENEW = 1; //专业版续费
	const VIRTUAL_SMS_1000 = 2; //短信1000条
	const VIRTUAL_QUOTA_PUBLIC_1 = 4; //新增公众号配额1个
	const VIRTUAL_QUOTA_SUBSP_1 = 6; //新增子帐号配额1个
	const VIRTUAL_ADD_APP = 8; //开通插件

	public static function func_get_service_order($item) {
		if(!empty($item['sp_uid'])) $item['sp'] = AccountMod::get_service_provider_by_uid($item['sp_uid']);
		if(!empty($item['service'])) $item['service'] = json_decode($item['service'], true);
		if(!empty($item['pay_info'])) $item['pay_info'] = json_decode($item['pay_info'], true);
		
		return $item;
	}

	public static function func_get_service_product($item) {
		$item['name'] = htmlspecialchars($item['name']);
		if(!empty($item['brief'])) $item['brief'] = XssHtml::clean_xss($item['brief']);
		if(!empty($item['sku_table'])) $item['sku_table'] = json_decode($item['sku_table'], true);

		//插件的sku
		if(($item['uid'] == 8) && ($dir = requestString('dir', PATTERN_APP_NAME))) {
			$dir = WeixinPlugMod::get_plugin_by_dir($dir);	
			if(!empty($dir['sku_table']))
			$item['sku_table'] = $dir['sku_table'];
		}
		return $item;
	}

	protected static $all = array(
						array(
							  'uid'  => SpServiceMod::VIRTUAL_RENEW, //1
						  	  'name' => '专业版续费',
						  	  'price' => 30000,
						  	  //'price' => 1,
						  	  'brief' => 'UCT微信服务平台专业版服务',
							  'thumb' => '/app/sp/static/images/continue.png',
							  'sku_table' => array (
									 'table' => 
									  array (
									    '期限' => 
									    array (
									      0 => '1月',
									      1 => '3月',
									      2 => '6月',
									      3 => '1年',
									    ),
									    '版本' => 
									    array (
									      0 => '展示型',
									      1 => '营销型',
									      2 => '行业型',
									    ),
									  ),
									  'info' => 
									  array (
									    '期限:1月;版本:展示型' => 
									    array (
									      'price' => 10000,
									      'ori_price' => 10000,
									      'quantity' => 6666600,
									    ),
									    '期限:1月;版本:营销型' => 
									    array (
									      'price' => 20000,
									      'ori_price' => 20000,
									      'quantity' => 6666600,
									    ),
									    '期限:1月;版本:行业型' => 
									    array (
									      'price' => 30000,
									      'ori_price' => 30000,
									      'quantity' => 6666600,
									    ),
									    '期限:3月;版本:展示型' => 
									    array (
									      'price' => 30000,
									      'ori_price' => 30000,
									      'quantity' => 7777700,
									    ),
									    '期限:3月;版本:营销型' => 
									    array (
									      'price' => 60000,
									      'ori_price' => 60000,
									      'quantity' => 7777700,
									    ),
									    '期限:3月;版本:行业型' => 
									    array (
									      'price' => 90000,
									      'ori_price' => 90000,
									      'quantity' => 7777700,
									    ),
									    '期限:6月;版本:展示型' => 
									    array (
									      'price' => 50000,
									      'ori_price' => 50000,
									      'quantity' => 8888800,
									    ),
									    '期限:6月;版本:营销型' => 
									    array (
									      'price' => 80000,
									      'ori_price' => 80200,
									      'quantity' => 8888800,
									    ),
									    '期限:6月;版本:行业型' => 
									    array (
									      'price' => 150000,
									      'ori_price' => 150000,
									      'quantity' => 8888800,
									    ),
									    '期限:1年;版本:展示型' => 
									    array (
									      'price' => 80000,
									      'ori_price' => 80000,
									      'quantity' => 9999900,
									    ),
									    '期限:1年;版本:营销型' => 
									    array (
									      'price' => 120000,
									      'ori_price' => 120000,
									      'quantity' => 9999900,
									    ),
									    '期限:1年;版本:行业型' => 
									    array (
									      'price' => 240000,
									      'ori_price' => 240000,
									      'quantity' => 9999900,
									    ),
									  ),
									), //end of sku_table
						),

						array(
							  'uid'  => SpServiceMod::VIRTUAL_SMS_1000, //2
							  'name' => '短信1000条',
						  	  'price' => 10000,
						  	  'brief' => '发送短信通知',
							  'thumb' => '/app/sp/static/images/msg.png'),
						/*
						array(
							  'uid'  => 3,
							  'name' => '导出excel表格10次',
						  	  'price' => 100,
						  	  'brief' => '将数据导出到excel表格',
							  'thumb' => '/app/sp/static/images/excel.png'),
						*/
						array(
							  'uid'  => SpServiceMod::VIRTUAL_QUOTA_PUBLIC_1, //4
							  'name' => '新增公众号配额1个',
						  	  'price' => 10000,
						  	  //'price' => 1,
						  	  'brief' => '添加更多的公众号',
							  'thumb' => '/app/sp/static/images/public.png'),
						/*
						array(
							  'uid'  => 5,
							  'name' => '测试支付功能',
						  	  'price' => 1,
						  	  'brief' => '测试支付功能,捐赠1分',
							  'thumb' => '/app/sp/static/images/pay.png'),
						*/
						array(
							  'uid'  => SpServiceMod::VIRTUAL_QUOTA_SUBSP_1, //6
							  'name' => '新增子帐号配额1个',
						  	  'price' => 10000,
						  	  //'price' => 1,
						  	  'brief' => '添加更多的管理员账号',
							  'thumb' => '/app/sp/static/images/public-m.png'),

						array(
							  'uid'  => SpServiceMod::VIRTUAL_ADD_APP, //6
							  'name' => '开通插件',
						  	  'price' => 1,
						  	  //'price' => 1,
						  	  'brief' => '开通插件',
							  'thumb' => '/app/sp/static/images/public-m.png'),

				);
	public static function get_store_service_list($option) {
		return self::get_store_service_product_list($option);

		$cnt = count(self::$all);
		if($option['limit'] >= 0) {
			$list = array_slice(self::$all, $option['page']*$option['limit'], $option['limit']);
		}
		else {
			$list = self::$all;
		}
		return array('count' => $cnt, 'list' => $list);
	}

	/*
		获取服务详情
	*/
	public static function get_service_by_uid($uid) {
		return self::get_store_service_product_by_uid($uid);
	
		return array_usearch($uid, self::$all, function($uid, $item){
			return $item['uid'] == $uid;
		});
	}

	public static function get_store_service_product_by_sku_uid($sku_uid) {
		$sku_uid = explode(';', $sku_uid, 2);
		$uid = $sku_uid[0];
		$ret = self::get_store_service_product_by_uid($uid);
		if(!$ret) {
			return false;
		}
		if(!empty($sku_uid[1]) && !empty($ret['sku_table']['info'][$sku_uid[1]])) {
			$ret = array_merge($ret, $ret['sku_table']['info'][$sku_uid[1]]);
		}
		$ret['sku_uid'] = $sku_uid;

		return $ret;
	}

	public static function get_store_service_product_list($option) {
		$sql = 'select * from service_store_product ';
		if(isset($option['status']) && $option['status'] >= 0) {
			$where_arr[] = 'status = '.$option['status'];
		}
		//搜索
		if(!empty($option['key'])) {
			$where_arr[] = '(name like "%'.$option['key'].'%" or brief like "%'.$option['key'].'%")';
		}
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}
		$sort = 'sort desc, create_time desc';
		$sql .= ' order by '.$sort;

		$ret = Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'SpServiceMod::func_get_service_product');

		/*	
			 选个时机初始化一下服务商城商品数据
		*/
		if(!$ret['list'] && !Dba::readOne('select count(*) from service_store_product')) {
			$ps = self::$all;
			foreach($ps as &$p) {
				$p['is_virtual'] = $p['uid'];
				$p['ori_price'] = $p['price'];
				$p['quantity'] = 1000000;
				$p['create_time'] = $_SERVER['REQUEST_TIME'];
				$p['modify_time'] = $_SERVER['REQUEST_TIME'];
			}

			Dba::insertS('service_store_product', $ps);
		}

		return $ret;
	}

	public static function get_store_service_product_by_uid($uid) {
		return Dba::readRowAssoc('select * from service_store_product where uid = '.$uid, 'SpServiceMod::func_get_service_product');
	}

	public static function add_or_edit_service_product($product) {
		$product['modify_time'] = $_SERVER['REQUEST_TIME'];
		if(!empty($product['uid'])) {
			Dba::update('service_store_product', $product, 'uid = '.$product['uid']);
		}
		else {
			$product['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('service_store_product', $product);
			$product['uid'] = Dba::insertID();
		}

		return $product['uid'];
	}

	/*
		删除商品 
		返回删除的条数
	*/
	public static function delete_service_products($pids) {
		if(!is_array($pids)) {
			$pids = array($pids);
		}
		$sql = 'delete from service_store_product where uid in ('.implode(',',$pids).')';
		return Dba::write($sql);
	}


	/*
		服务订单
		下单或编辑
	*/
	public static function make_a_service_order($order) {
		if(!empty($order['uid'])) {
			Dba::update('service_provider_order', $order, 'uid = '.$order['uid']);
		}
		else {
			$order['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('service_provider_order', $order);
			$order['uid'] = Dba::insertID();

			//商户下单以后事件,可以发送短信通知管理员
			Event::handle('AfterMakeServiceOrder', array($order));
		}

		return $order['uid'];
	}

	public static function onAfterMakeServiceOrder($order)
	{
		uct_use_app('templatemsg');
		$sp_uid = 0;
		$su_uid = 0;
		$args  = Template_Msg_WxPlugMod::get_spservice_args_by_order($order);
		Template_Msg_WxPlugMod::after_even_send_template_msg(__CLASS__.'.'.__FUNCTION__,$sp_uid,$su_uid,$args);
	}


	public static function get_service_order_by_uid($uid) {
		$sql = 'select * from service_provider_order where uid = '.$uid;
		return Dba::readRowAssoc($sql, 'SpServiceMod::func_get_service_order');
	}

	/*
		订单列表 
	*/
	public static function get_service_order_list($option) {
		$sql = 'select service_provider_order.* from service_provider_order';	
			    
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'sp_uid = '.$option['sp_uid'];
		}
		if(!empty($option['status'])) {
			$where_arr[] = 'status = '.$option['status'];
		}
		if(!empty($option['service_uid'])) {
			$where_arr[] = 'service_uid = '.$option['service_uid'];
		}
		if(!empty($option['key'])) { //搜索订单号 和 服务名称
			$where_arr[] = '(service_provider_order.uid = "'.addslashes($option['key']).'"  || service like "%'.
							addslashes(trim(str_replace(array('\\u'), array('\\\\u'),json_encode($option['key'])), '"')).'%")';
		}
		
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}

		empty($option['sort']) && $option['sort'] = SORT_CREATE_TIME;
		switch($option['sort']) {
			default:
				$sort = 'create_time desc';
		}
		$sql .= ' order by '.$sort;

		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'SpServiceMod::func_get_service_order');
	}

	/*
		取消订单

		只有在待付款的订单能取消
	*/
	public static function do_cancel_order($o) {
		if(!in_array($o['status'], array(SpServiceMod::ORDER_WAIT_USER_PAY,
										))) {
			setLastError(ERROR_BAD_STATUS);
			return false;
		}

		$update = array(
			'uid' => $o['uid'],
			'status' => SpServiceMod::ORDER_CANCELED,
		);
		self::make_a_service_order($update);

		$order = self::get_service_order_by_uid($o['uid']);
		Event::handle('AfterCancelServiceOrder', array($order));
		return true;
	}

	public static function onAfterCancelServiceOrder($order)
	{
		uct_use_app('templatemsg');
		$sp_uid = 0;
		$su_uid = 0;
		$args  = Template_Msg_WxPlugMod::get_spservice_args_by_order($order);

		Template_Msg_WxPlugMod::after_even_send_template_msg(__CLASS__.'.'.__FUNCTION__,$sp_uid,$su_uid,$args);
	}

	/*
		删除订单

		只有已取消的和未付款的订单可以删除
	*/
	public static function delete_order($o) {
		if(!in_array($o['status'], array(SpServiceMod::ORDER_CANCELED, SpServiceMod::ORDER_WAIT_USER_PAY))) {
			setLastError(ERROR_BAD_STATUS);
			return false;
		}

		$sql = 'delete from service_provider_order where uid = '.$o['uid'];
		Dba::write($sql);
		return true;
	}
	
	public static function onAfterSendOrder($order) {
		$msg = array(
					'title'   => '发货提醒',
					'content' => '订单号 <a href="?_a=sp&_u=index.orderdetail&uid='.$order['uid'].'">'
									.$order['uid'].'</a> 已于 '.date('Y-m-d H:i:s', $order['service']['send_time']).' 发货, 请等待查收.',
					'sp_uid'  => $order['sp_uid'], 
		);
		uct_use_app('sp');
		SpMsgMod::add_sp_msg($msg);
	}

	/*
		发货
	*/
	public static function do_send_order($o, $deliver_info) {
		if($o['status'] != SpServiceMod::ORDER_WAIT_FOR_DELIVERY) {
			setLastError(ERROR_BAD_STATUS);
			return false;
		}

		$service = $o['service'];
		$service['send_time'] = $_SERVER['REQUEST_TIME'];
		$service['delivery_info'] = $deliver_info;
		$update = array(
			'uid' => $o['uid'],
			'status' => SpServiceMod::ORDER_WAIT_USER_RECEIPT,
			'service' => $service,
		);
		Dba::beginTransaction(); {
			self::make_a_service_order($update);

			$order = self::get_service_order_by_uid($o['uid']);
			self::onAfterSendOrder($order);
			//发货以后事件,可以发送短信通知商户
			//Event::handle('AfterSendGoods', array($order));
		} Dba::commit();
		return true;
	}

	/*
		收货
	*/
	public static function do_receipt_order($o) {
		if($o['status'] != SpServiceMod::ORDER_WAIT_USER_RECEIPT) {
			setLastError(ERROR_BAD_STATUS);
			return false;
		}

		$service = $o['service'];
		$service['recv_time'] = $_SERVER['REQUEST_TIME'];
		$update = array(
			'uid' => $o['uid'],
			'status' => SpServiceMod::ORDER_DELIVERY_OK,
			'service' => $service,
		);
		Dba::beginTransaction(); {
			self::make_a_service_order($update);
		
			//收货事件
			//$order = self::get_service_order_by_uid($o['uid']);
			//Event::handle('AfterRecvGoods', array($order));
		} Dba::commit();
		return true;
	}

	/*
		服务发货,增加配额,发送消息通知
	*/
	public static function do_service_order_delivery($virtual_sku_uid, $sp_uid, $quantity = 1) {
		$virtual_sku_uid = explode(';', $virtual_sku_uid, 2);
		$service_name = '';
		switch($virtual_sku_uid[0]) {
			case SpServiceMod::VIRTUAL_ADD_APP: { //续费
				if((!$dir = requestString('dir', PATTERN_APP_NAME)) || !($dir = WeixinPlugMod::get_plugin_by_dir($dir))) {
					setLastError(ERROR_INVALID_REQUEST_PARAM);
					return false;
				}
				//1个月， 3个月， 6个月， 1年
				if(!empty($virtual_sku_uid[1])) {
					$long = min(12, max(1, checkNatInt($virtual_sku_uid[1])));
					$map = array('月' => 86400 * 30, '年' => 86400 * 365, '天' => 86400);
					$unit = 86400 * 30;
					foreach($map as $k => $v) {
						if(false !== strpos($virtual_sku_uid[1], $k)) {
							$unit = $v;
							break;
						}
					}
					$time = $long * $unit;
				}
				if(empty($time)) $time = 0;
				SpInviteMod::pay_a_plugin($dir['dir'], $sp_uid, $time);
				$msg = array(
					'content' => '恭喜! 您已成功开通 <span>'.$dir['name'].'</span>',
				);
				//todo 
				break;
			}
			
			case SpServiceMod::VIRTUAL_RENEW: { //续费
				$service_name = '续费';
				//1个月， 3个月， 6个月， 1年
				if(!empty($virtual_sku_uid[1])) {
					$long = min(12, max(1, checkNatInt($virtual_sku_uid[1])));
					$map = array('月' => 86400 * 30, '年' => 86400 * 365, '天' => 86400);
					$unit = 86400 * 30;
					foreach($map as $k => $v) {
						if(false !== strpos($virtual_sku_uid[1], $k)) {
							$unit = $v;
							break;
						}
					}
					$time = $long * $unit;
				}
				if(empty($time)) $time = 86400 * 30 * 6;
				
				SpLimitMod::increase_expire_time($time * $quantity, $sp_uid);
				$msg = array(
					'content' => '恭喜! 您的服务已续期至 <span>'.(SpLimitMod::get_current_sp_limit('expire_time', $sp_uid) ?
								date('Y-m-d H:i:s', SpLimitMod::get_current_sp_limit('expire_time', $sp_uid)) : '永久').'</span>',
				);
				break;
			}
			case SpServiceMod::VIRTUAL_SMS_1000 : { //短信
				SpLimitMod::increase_current_sms_cnt(1000 * $quantity, $sp_uid);
				$service_name = '短信';
				$msg = array(
					'content' => '恭喜! 您的可用短信数目增至 <span>'.SpLimitMod::get_current_sp_limit('sms_remain', $sp_uid).' </span>条',
				);
				break;
			}
			case 3: { //excel
				SpLimitMod::increase_current_excel_cnt(10 * $quantity, $sp_uid);
				$service_name = 'excel';
				$msg = array(
					'content' => '恭喜! 您的可用excel导出数目增至 <span>'.SpLimitMod::get_current_sp_limit('excel_remain', $sp_uid).' </span>次',
				);
				break;
			}
			case SpServiceMod::VIRTUAL_QUOTA_PUBLIC_1: { //公众号数目
				SpLimitMod::increase_current_public_cnt(1 * $quantity, $sp_uid);
				$service_name = '公众号配额';
				$msg = array(
					'content' => '恭喜! 您的总共可添加公众号数目增至 <span>'.SpLimitMod::get_current_max_public_cnt($sp_uid).'  </span>个',
				);
				break;
			}
			case 5: {
				$msg = array(
					'content' => '恭喜! 测试服务购买成功!',
				);
				break;
			}
			case SpServiceMod::VIRTUAL_QUOTA_SUBSP_1 : { //子账号数目
				SpLimitMod::increase_current_subsp_cnt(1 * $quantity, $sp_uid);
				$service_name = '子账号配额';
				$msg = array(
					'content' => '恭喜! 您的总共可添加子账号数目增至 <span>'.SpLimitMod::get_current_max_subsp_cnt($sp_uid).'  </span>个',
				);
				break;
			}


			default:
			return true;
		}

		$msg['title'] = '"'.$service_name.'" 服务购买成功!';
		$msg['sp_uid'] = $sp_uid;
		if(!empty($GLOBALS['_TMP']['oid'])) {
			$msg['content'] .= '<br/> 服务订单号 <a href="?_a=sp&_u=index.orderdetail&uid='.$GLOBALS['_TMP']['oid'].'">'
									.$GLOBALS['_TMP']['oid'].'</a>';
		}
		uct_use_app('sp');
		SpMsgMod::add_sp_msg($msg);
		return true;
	}

	/*
		减库存 , 加销量
	*/
	public static function decrease_service_product_quantity($sku_uid, $quantity) {
		$sku_uid = explode(';', $sku_uid, 2);	
		do {
			$p = Dba::readRowAssoc('select uid, quantity, sku_table from service_store_product where uid = '.$sku_uid[0]);
			$p['sku_table_ori'] = $p['sku_table'];
			if($p['sku_table']) $p['sku_table'] = json_decode($p['sku_table'], true);
			
			if(empty($sku_uid[1]) || ($sku_uid[0] == 8)) { //普通减库存
				if($p['quantity'] < $quantity) {
					setLastError(ERROR_OUT_OF_LIMIT);
					return false;
				}	

				$sql = 'update service_store_product set quantity = quantity - '.$quantity.', sell_cnt=sell_cnt+'.$quantity
						.' where uid = '.$p['uid'].' && quantity >= '.$quantity;
			}
			else {
				if(empty($p['sku_table']['info'][$sku_uid[1]])) {
					setLastError(ERROR_OBJ_NOT_EXIST);
					return false;
				}
				if($p['sku_table']['info'][$sku_uid[1]]['quantity'] < $quantity) {
					setLastError(ERROR_OUT_OF_LIMIT);
					return false;
				}	

				$new = $p['sku_table'];
				$new['info'][$sku_uid[1]]['quantity'] -= $quantity;
				$sql = 'update service_store_product set sku_table = "'.addslashes(json_encode($new)).'", sell_cnt=sell_cnt+'.$quantity
						.' where uid = '.$p['uid'].' && sku_table = "'.addslashes(($p['sku_table_ori'])).'"';
			}

		} while(!Dba::write($sql));
		
		return true;
	}

	/*
		订单支付
	*/
	public static function onAfterServiceOrderPay($o) {
		if(is_numeric($o)) {
			$o = self::get_service_order_by_uid($o);
		}
		if(!$o) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return false;
		}
		if($o['status'] != SpServiceMod::ORDER_WAIT_FOR_DELIVERY) {
			setLastError(ERROR_BAD_STATUS);
			return false;
		}

		//如果是虚拟服务则自动发货
		if(!empty($o['service']['is_virtual'])) {
			if(!Dba::write('update service_provider_order set status = '.SpServiceMod::ORDER_DELIVERY_OK.' where uid = '.$o['uid']
						.' && status = '.SpServiceMod::ORDER_WAIT_FOR_DELIVERY)) {
				setLastError(ERROR_DB_CHANGED_BY_OTHERS);
				return false;
			}

			$GLOBALS['_TMP']['oid'] = $o['uid'];
			if(!empty($o['service']['dir'])) $_REQUEST['dir'] = $o['service']['dir'];
			self::do_service_order_delivery($o['service']['virtual_sku_uid'], $o['sp_uid'], $o['service']['quantity']);
		}
		uct_use_app('templatemsg');
		$sp_uid = 0;
		$su_uid = 0;
		$args  = Template_Msg_WxPlugMod::get_spservice_args_by_order($o);
		Template_Msg_WxPlugMod::after_even_send_template_msg(__CLASS__.'.'.__FUNCTION__,$sp_uid,$su_uid,$args);
		return true;
	}

}

