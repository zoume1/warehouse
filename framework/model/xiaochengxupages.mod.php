<?php
/*
	本文件由 *uctphp框架代码半自动生成工具* 自动生成

	中文名： 数据
	类名： XiaochengxuPages

	模块名： xiaochengxu_pages
	表名： xiaochengxu_pages
*/

class XiaochengxuPagesMod {
	public static function func_get_xiaochengxu_pages($item) {
		//todo
		return $item;
	}

	/*
		添加编辑数据	
	*/
	public static function add_or_edit_xiaochengxu_pages($i) {
		if(!empty($i['uid'])) {
			!isset($i['modify_time']) && $i['modify_time'] = $_SERVER['REQUEST_TIME'];
			Dba::update('xiaochengxu_pages', $i, 'uid = '.$i['uid'].' && sp_uid = '.$i['sp_uid']);	
		}	
		else {
			unset($i['uid']);
			!isset($i['create_time']) && $i['create_time'] = $_SERVER['REQUEST_TIME'];
			!isset($i['modify_time']) && $i['modify_time'] = $i['create_time'];

			Dba::insert('xiaochengxu_pages', $i);	
			$i['uid'] = Dba::insertID();
		}

		//上线
		if(!empty($i['sort']) && $i['sort'] >= 999999) {
			$ii = Dba::readRowAssoc('select * from xiaochengxu_pages where uid = '.$i['uid']);
			unset($ii['uid']);
			$ii['sort'] = 999999;
			$ii['status'] = 0;
			Dba::write('update xiaochengxu_pages set sort = 0 where uid = '.$i['uid']);	
			Dba::write('delete from xiaochengxu_pages where sp_uid = '.$i['sp_uid'].' && sort >= 999999');	
			Dba::insert('xiaochengxu_pages', $ii);
		}

		return $i['uid'];
	}
	
	public static function get_xiaochengxu_pages_by_uid($uid) {
		return Dba::readRowAssoc('select * from xiaochengxu_pages where uid = '.$uid, 'XiaochengxuPagesMod::func_get_xiaochengxu_pages');
	}

	public static function get_xiaochengxu_pages_by_title($title, $sp_uid) {
		return Dba::readRowAssoc('select * from xiaochengxu_pages where sp_uid = '.$sp_uid.
								' && title = "'.addslashes($title).'"', 'XiaochengxuPagesMod::func_get_xiaochengxu_pages');
	}

	//取默认首页, 第一次创建那个
	public static function get_xiaochengxu_pages_by_default($sp_uid, $public_uid) {
		return Dba::readRowAssoc('select * from xiaochengxu_pages where sp_uid = '.$sp_uid.
								' && public_uid = '.$public_uid.' && status = 0 order by sort desc,uid asc limit 1', 'XiaochengxuPagesMod::func_get_xiaochengxu_pages');
	}

	/*
		 数据数据列表	
	*/
	public static function get_xiaochengxu_pages_list($option) {
		$sql = 'select * from xiaochengxu_pages';
		
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'sp_uid = '.$option['sp_uid'];
		}
		if(!empty($option['public_uid'])) {
			$where_arr[] = 'public_uid = '.$option['public_uid'];
		}
		//todo
		if(isset($option['status']) && ($option['status'] >= 0)) {
			$where_arr[] = 'status = '.$option['status'];
		}

		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}

		!isset($option['sort']) && $option['sort'] = 0;
		switch($option['sort'] ) {
			default: 
				$order = ' order by uid desc'; 
		}
		$sql .= $order;

		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'XiaochengxuPagesMod::func_get_xiaochengxu_pages');
	}

	/*
		删除数据
	*/
	public static function delete_xiaochengxu_pages($uids, $sp_uid) {
		if(!is_array($uids)) $uids = array($uids);
		$sql = 'delete from xiaochengxu_pages where uid in ('.implode(',', $uids).') && sp_uid = '.$sp_uid;
		return Dba::write($sql);
	}
}




