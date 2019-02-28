<?php

/*
	商户工单
*/
class SpFeedbackMod {
	public static function func_get_spfeedback($item) {
		$item['content'] = XssHtml::clean_xss($item['content']);
		return $item;
	}

	/*
		工单列表
	*/
	public static function get_sp_feedback_list($option) {
		$sql = 'select * from service_provider_feedback';
		if(!empty($option['sp_uid'])) {
			$where_arr[] = 'sp_uid = '.$option['sp_uid'];
		}
		if(isset($option['status']) && $option['status'] >= 0) {
			$where_arr[] = 'status = '.$option['status'];	
		}
		//搜索
		if(!empty($option['key'])) {
			$where_arr[] = 'content like "%'.$option['key'].'%"';
		}
		if(!empty($where_arr)) {
			$sql .= ' where '.implode(' and ', $where_arr);
		}
		$sql .= ' order by create_time desc';

		return Dba::readCountAndLimit($sql, $option['page'], $option['limit'], 'SpFeedbackMod::func_get_spfeedback');
	}

	public static function get_sp_feedback_by_uid($uid) {
		return Dba::readRowAssoc('select * from service_provider_feedback where uid = '.$uid, 'SpFeedbackMod::func_get_spfeedback');
	}

	public static function add_or_edit_sp_feedback($fb) {
		if(!empty($fb['uid'])) {
			Dba::update('service_provider_feedback', $fb, 'uid = '.$fb['uid'].' && sp_uid = '.$fb['sp_uid']);	
		}
		else {
			unset($fb['uid']);
			$fb['create_time'] = $_SERVER['REQUEST_TIME'];
			Dba::insert('service_provider_feedback', $fb);
			$fb['uid'] = Dba::insertID();
		}

		return $fb['uid'];
	}
	
	/*
		删除工单
		支持批量
	*/
	public static function delete_sp_feedback($uids, $sp_uid = 0) {
		$sql = 'delete from service_provider_feedback where uid in ('.
				implode(',', $uids).') && sp_uid = '.$sp_uid;
		
		return Dba::write($sql);
	}

}

