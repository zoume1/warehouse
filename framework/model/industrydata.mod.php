<?php

/*
	行业分类数据
	这个分类跟微信官方模板消息的行业分类是一致的
*/

class IndustryDataMod {
	/*
		获取所有行业名称和id
	*/
	public static function get_all() {
		return self::$data;
	}	

	/*
		获取行业主类
	*/
	public static function get_main_name() {
		$m = array();
		foreach(self::$data as $d) {
			if(!in_array($d['name'], $m)) {
				$m[] = $d['name'];
			}
		}
		return $m;
	}

	public static $data = array
	(
	  1 => 
	  array (
	    'uid' => 1,
	    'name' => 'IT科技',
	    'sub_name' => '互联网/电子商务',
	  ),
	  2 => 
	  array (
	    'uid' => 2,
	    'name' => 'IT科技',
	    'sub_name' => 'IT软件与服务',
	  ),
	  3 => 
	  array (
	    'uid' => 3,
	    'name' => 'IT科技',
	    'sub_name' => 'IT硬件与设备',
	  ),
	  4 => 
	  array (
	    'uid' => 4,
	    'name' => 'IT科技',
	    'sub_name' => '电子技术',
	  ),
	  5 => 
	  array (
	    'uid' => 5,
	    'name' => 'IT科技',
	    'sub_name' => '通信与运营商',
	  ),
	  6 => 
	  array (
	    'uid' => 6,
	    'name' => 'IT科技',
	    'sub_name' => '网络游戏',
	  ),
	  7 => 
	  array (
	    'uid' => 7,
	    'name' => '金融业',
	    'sub_name' => '银行',
	  ),
	  8 => 
	  array (
	    'uid' => 8,
	    'name' => '金融业',
	    'sub_name' => '基金|理财|信托',
	  ),
	  9 => 
	  array (
	    'uid' => 9,
	    'name' => '金融业',
	    'sub_name' => '保险',
	  ),
	  10 => 
	  array (
	    'uid' => 10,
	    'name' => '餐饮',
	    'sub_name' => '餐饮',
	  ),
	  11 => 
	  array (
	    'uid' => 11,
	    'name' => '酒店旅游',
	    'sub_name' => '酒店',
	  ),
	  12 => 
	  array (
	    'uid' => 12,
	    'name' => '酒店旅游',
	    'sub_name' => '旅游',
	  ),
	  13 => 
	  array (
	    'uid' => 13,
	    'name' => '运输与仓储',
	    'sub_name' => '快递',
	  ),
	  14 => 
	  array (
	    'uid' => 14,
	    'name' => '运输与仓储',
	    'sub_name' => '物流',
	  ),
	  15 => 
	  array (
	    'uid' => 15,
	    'name' => '运输与仓储',
	    'sub_name' => '仓储',
	  ),
	  16 => 
	  array (
	    'uid' => 16,
	    'name' => '教育',
	    'sub_name' => '培训',
	  ),
	  17 => 
	  array (
	    'uid' => 17,
	    'name' => '教育',
	    'sub_name' => '院校',
	  ),
	  18 => 
	  array (
	    'uid' => 18,
	    'name' => '政府与公共事业',
	    'sub_name' => '学术科研',
	  ),
	  19 => 
	  array (
	    'uid' => 19,
	    'name' => '政府与公共事业',
	    'sub_name' => '交警',
	  ),
	  20 => 
	  array (
	    'uid' => 20,
	    'name' => '政府与公共事业',
	    'sub_name' => '博物馆',
	  ),
	  21 => 
	  array (
	    'uid' => 21,
	    'name' => '政府与公共事业',
	    'sub_name' => '公共事业|非盈利机构',
	  ),
	  22 => 
	  array (
	    'uid' => 22,
	    'name' => '医药护理',
	    'sub_name' => '医药医疗',
	  ),
	  23 => 
	  array (
	    'uid' => 23,
	    'name' => '医药护理',
	    'sub_name' => '护理美容',
	  ),
	  24 => 
	  array (
	    'uid' => 24,
	    'name' => '医药护理',
	    'sub_name' => '保健与卫生',
	  ),
	  25 => 
	  array (
	    'uid' => 25,
	    'name' => '交通工具',
	    'sub_name' => '汽车相关',
	  ),
	  26 => 
	  array (
	    'uid' => 26,
	    'name' => '交通工具',
	    'sub_name' => '摩托车相关',
	  ),
	  27 => 
	  array (
	    'uid' => 27,
	    'name' => '交通工具',
	    'sub_name' => '火车相关',
	  ),
	  28 => 
	  array (
	    'uid' => 28,
	    'name' => '交通工具',
	    'sub_name' => '飞机相关',
	  ),
	  29 => 
	  array (
	    'uid' => 29,
	    'name' => '房地产',
	    'sub_name' => '建筑',
	  ),
	  30 => 
	  array (
	    'uid' => 30,
	    'name' => '房地产',
	    'sub_name' => '物业',
	  ),
	  31 => 
	  array (
	    'uid' => 31,
	    'name' => '消费品',
	    'sub_name' => '消费品',
	  ),
	  32 => 
	  array (
	    'uid' => 32,
	    'name' => '商业服务',
	    'sub_name' => '法律',
	  ),
	  33 => 
	  array (
	    'uid' => 33,
	    'name' => '商业服务',
	    'sub_name' => '会展',
	  ),
	  34 => 
	  array (
	    'uid' => 34,
	    'name' => '商业服务',
	    'sub_name' => '中介服务',
	  ),
	  35 => 
	  array (
	    'uid' => 35,
	    'name' => '商业服务',
	    'sub_name' => '认证',
	  ),
	  36 => 
	  array (
	    'uid' => 36,
	    'name' => '商业服务',
	    'sub_name' => '审计',
	  ),
	  37 => 
	  array (
	    'uid' => 37,
	    'name' => '文体娱乐',
	    'sub_name' => '传媒',
	  ),
	  38 => 
	  array (
	    'uid' => 38,
	    'name' => '文体娱乐',
	    'sub_name' => '体育',
	  ),
	  39 => 
	  array (
	    'uid' => 39,
	    'name' => '文体娱乐',
	    'sub_name' => '娱乐休闲',
	  ),
	  40 => 
	  array (
	    'uid' => 40,
	    'name' => '印刷',
	    'sub_name' => '印刷',
	  ),
	  41 => 
	  array (
	    'uid' => 41,
	    'name' => '其它',
	    'sub_name' => '其它',
	  ),
	);
	
}

