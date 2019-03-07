<?php
/*
 *  统一 附件文件 管理
 * all_attaches
 */
class AttachesMod {
	/*
		附件的位置

		如果传了key, 返回对应的名字
	*/
	public static function get_pos($key = '') {
		//mark 可以在此添加更多位置
		static $poss = array(
			'default'	 => array('name' => '未指定'),
		);
		
		return $key ? (isset($poss[$key]) ? $poss[$key]['name'] : false) : $poss;
	}

    /*
     附件
     不需要分页
    */
    public static function get_attaches($option) {
        $sql = 'select * from all_attaches';
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
        附件详情
    */
    public static function get_attach_by_uid($uid) {
        $sql = 'select * from all_attaches where uid = '.$uid;
        return Dba::readRowAssoc($sql);
    }

    public static function add_or_edit_attach($attach) {
		unset($attach['link_type']);
        if(!empty($attach['uid'])) {
            Dba::update('all_attaches', $attach, 'uid = '.$attach['uid']);
        }
        else {
            $attach['create_time'] = $_SERVER['REQUEST_TIME'];
            Dba::insert('all_attaches', $attach);
            $attach['uid'] = Dba::insertID();
        }
        return $attach['uid'];
    }

    /*
        删除分类
        返回删除的条数
    */
    public static function delete_attaches($sids, $sp_uid) {
        if(!is_array($sids)) {
            $sids = array($sids);
        }
        $sql = 'delete from all_attaches where uid in ('.implode(',',$sids).') and sp_uid = '.$sp_uid;
        $ret = Dba::write($sql);
        return $ret;
    }

}

