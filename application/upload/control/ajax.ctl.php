<?php

class AjaxCtl {

    //删除文件 不删除文件 只删除文件和用户的映射关系
    public function delete_file_info()
    {
        if(!$sp_uid = AccountMod::get_current_service_provider('uid')) {
			outError(ERROR_USER_HAS_NOT_LOGIN);
		}
        if(!($uids = requestIntArray('uids'))) //not uidm
        {
            outError(ERROR_INVALID_REQUEST_PARAM);
        }
        outRight(UploadMod::delete_file_info_by_file_id($uids,$sp_uid));
    }

	/*
		后台改文件名字
	*/
	public function edit_file_name() {
		$file_id = requestInt('uid');
		$name = requestString('file_name', PATTERN_FILE_NAME);
		if(!$file_id || !$name) {
			outError(ERROR_INVALID_REQUEST_PARAM);
		}
        if(!$sp_uid = AccountMod::get_current_service_provider('uid')) {
            outError(ERROR_USER_HAS_NOT_LOGIN);
        }
		if(!Dba::readOne('select uid from files_user_upload_stat where user_id = '.
			$sp_uid.' && `from` = 1 && file_id = '.$file_id)) {
			outError(ERROR_OBJ_NOT_EXIST);
		}
		
		$ret = Dba::update('files', array('file_name' => $name), 'uid = '.$file_id);	
		outRight($ret);	
	}

    /*
     * 移动图片类别file_group
     */
    public function edit_file_group()
    {
        if(!$sp_uid = AccountMod::get_current_service_provider('uid')) {
            outError(ERROR_USER_HAS_NOT_LOGIN);
        }

        if(!($uid = requestInt('uid')) && !($uid = requestIntArray('uids'))) {
            outError(ERROR_INVALID_REQUEST_PARAM);
        }
		if(is_numeric($uid)) {
			$uid = array($uid);
		}

        $file_group = requestInt('file_group', 1);

        outRight(Dba::write('update files_user_upload_stat set file_group = '.
			$file_group.' where user_id = '.$sp_uid.' && `from` = 1 && file_id in ('.
			implode(',', $uid).')'));
    }

    //删除无拥有者的文件  错误信息 ERROR_DBG_STEP_1 有文件是有拥有者的
//    public function delete_files()
//    {
//        if(!($uids = requestStringArray('uids')))
//        {
//            outError(ERROR_INVALID_REQUEST_PARAM);
//        }
//        outRight(UploadMod::delete_files($uids));
//    }


}


