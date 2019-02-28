<?php
/*
 *  统一 幻灯片 管理
 * all_slides
 */
class SlidesMod {
	/*
		幻灯片的位置

		如果传了key, 返回对应的名字
	*/
	public static function get_pos($key = '') {
		//mark 可以在此添加更多位置
		static $poss = array(
			'default'	 => array('name' => '未指定'),

			'activity1'	 => array('name' => '活动轮播图1'),
			'activity2'	 => array('name' => '活动轮播图2'),

			'book1'	     => array('name' => '预约轮播图1'),
			'book2'	     => array('name' => '预约分类图'),

			'exam1'	     => array('name' => '考试系统'),
			'exam2'	     => array('name' => '考试系统2'),

			'xiaochengxu1' => array('name' => '小程序首页模块'),
			'movecar1' => array('name' => '扫码挪车首页'),
			'class1' => array('name' => '分类信息首页'),
			'biz1' => array('name' => '商家入驻首页'),

			'taoke1' => array('name' => '淘客首页'),
			'taoke2' => array('name' => '淘客首页2'),
		);
		
		return $key ? (isset($poss[$key]) ? $poss[$key]['name'] : false) : $poss;
	}

	/*
		预设幻灯片
	*/
	public static function get_slide_tpl($key = '') {
		$sp_uid = AccountMod::require_sp_uid();
		$all = array(
		'完整版小程序' => array(
			'type' => 'xiaochengxu1',
			'data' => array(
				array(#'image' => '?_a=upload&_u=index.out&uidm=17819ffb5',
					'image' => '?_u=common.img&name=store.png&sp_uid='.$sp_uid,
					'title' => '精选商户',
					'link' => 'pages/business/business',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17811b33c',
					'image' => '?_u=common.img&name=coupon_1.png&sp_uid='.$sp_uid,
					'title' => '领券中心',
					'link' => 'pages/couponCenter/couponCenter',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17808c770',
					'image' => '?_u=common.img&name=integral.png&sp_uid='.$sp_uid,
					'title' => '我的积分',
					'link' => '../my/pages/wallet/wallet',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17810c8fb',
					'image' => '?_u=common.img&name=bussiness-card.png&sp_uid='.$sp_uid,
					'title' => '邀请好友',
					'link' => '../my/pages/qrCode/qrCode',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17807af80',
					'image' => '?_u=common.img&name=tuan.png&sp_uid='.$sp_uid,
					'title' => '拼团',
					'link' => 'pages/cluster/cluster',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=1780601e0',
					'image' => '?_u=common.img&name=process.png&sp_uid='.$sp_uid,
					'title' => '我的粉丝',
					'link' => '../my/pages/team/team',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=1783040fc',
					'image' => '?_u=common.img&name=information.png&sp_uid='.$sp_uid,
					'title' => '关于我们',
					'link' => 'pages/article/article?uid=28',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=1782487ba',
					'image' => '?_u=common.img&name=truck.png&sp_uid='.$sp_uid,
					'title' => '物流查询',
					'link' => '../my/pages/myOrders/myOrders?index=2',
					'pos' => 'xiaochengxu1',
				),
				/*
				array(#'image' => '?_a=upload&_u=index.out&uidm=17872cd19',
					'image' => '?_u=common.img&name=help.png&sp_uid='.$sp_uid,
					'title' => '定制服务',
					'link' => '../class/pages/goodDetail/goodDetail?uid=1120',
					'pos' => 'xiaochengxu1',
				),*/
				array(#'image' => '?_a=upload&_u=index.out&uidm=17872cd19',
					'image' => '?_u=common.img&name=favorite.png&sp_uid='.$sp_uid,
					'title' => '我的收藏',
					'link' => '../my/pages/favorite/favorite',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17872cd19',
					'image' => '?_u=common.img&name=credit-level.png&sp_uid='.$sp_uid,
					'title' => '会员卡',
					'link' => '../my/pages/myvip/myvip',
					'pos' => 'xiaochengxu1',
				),
				array(#'image' => '?_a=upload&_u=index.out&uidm=17872cd19',
					'image' => '?_u=common.img&name=business_center.png&sp_uid='.$sp_uid,
					'title' => '商家入驻',
					'link' => '../cart/pages/businessCenter/businessCenter',
					'pos' => 'xiaochengxu1',
				),
				array(
					'image' => '?_u=common.img&name=jewelry.png&sp_uid='.$sp_uid,
					'title' => '分销中心',
					'link' => '../cart/pages/distributionCenter/distributionCenter',
					'pos' => 'xiaochengxu1',
				),
				array(
					'image' => '?_u=common.img&name=location.png&sp_uid='.$sp_uid,
					'title' => '一键导航',
					'link' => 'map-22.969890-113.122240-腾讯大厦',
					'pos' => 'xiaochengxu1',
				),
			),
		),	
		);

		return $key ? (isset($all[$key]) ? $all[$key] : false) : $all;
	}

	/*
		添加预设幻灯片
	*/
	public static function add_tpl_slide($key, $sp_uid) {
		if(!$ss = SlidesMod::get_slide_tpl($key)) {
			return false;
		}
		$time = $_SERVER['REQUEST_TIME'];
		$data = array();
		foreach($ss['data'] as $s) {
			$s['sp_uid'] = $sp_uid;	
			$s['create_time'] = $time++;	
			$data[] = $s;
		}

		return Dba::insertS('all_slides', $data);
	}

    /*
     幻灯片
     不需要分页
    */
    public static function get_slides($option) {
        $sql = 'select * from all_slides';
        if(!empty($option['sp_uid'])) {
            $where_arr[] = 'sp_uid = '.$option['sp_uid'];
        }
        if(!empty($option['pos'])) {
            $where_arr[] = 'pos = "'.addslashes($option['pos']).'"';
        }

		//默认只要能显示的
		if(empty($option['with_all'])) {
            $where_arr[] = '(status = 0 && (on_time=0 || on_time <= '.$_SERVER['REQUEST_TIME'].
							') && (off_time=0 || off_time >= '.$_SERVER['REQUEST_TIME'].'))';
		}

        if(!empty($where_arr)) {
            $sql .= ' where '.implode(' and ', $where_arr);
        }
        $sql .= ' order by sort desc, uid asc';

        return Dba::readAllAssoc($sql);
    }

    /*
        幻灯片详情
    */
    public static function get_slide_by_uid($uid) {
        $sql = 'select * from all_slides where uid = '.$uid;
        return Dba::readRowAssoc($sql);
    }

    public static function add_or_edit_slide($slide) {
		unset($slide['link_type']);
        if(!empty($slide['uid'])) {
            Dba::update('all_slides', $slide, 'uid = '.$slide['uid']);
        }
        else {
            $slide['create_time'] = $_SERVER['REQUEST_TIME'];
            Dba::insert('all_slides', $slide);
            $slide['uid'] = Dba::insertID();
        }
        return $slide['uid'];
    }

    /*
        删除分类
        返回删除的条数
    */
    public static function delete_slides($sids, $sp_uid) {
        if(!is_array($sids)) {
            $sids = array($sids);
        }
        $sql = 'delete from all_slides where uid in ('.implode(',',$sids).') and sp_uid = '.$sp_uid;
        $ret = Dba::write($sql);
        return $ret;
    }

}

