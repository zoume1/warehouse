<?php
/*
	图片缩放裁剪
*/

class ImageProcessMod {
	/*
		头像图片缩放裁剪
	*/
	public static function crop_avatar($file_id, $option) {
		//todo  检查一下用户权限
		if(!($file_info = Dba::readRowAssoc('select * from files where uid = '.$file_id)) ||
			strncmp($option['md5_pre'], $file_info['md5'], strlen($option['md5_pre'])) != 0) {
			setLastError(ERROR_OBJ_NOT_EXIST);
			return false;
		}

		$origin_file = UploadMod::get_file_dst($file_info);
		if(!file_exists($origin_file)) {
			setLastError(ERROR_DBG_STEP_1);
			return false;
		}


		include_once UCT_PATH.'vendor/images/image.php';
		try {
     		$buff = file_get_contents($origin_file);
			
			$image = getImageineInstance();
            $new = $image->crop_buff($buff, $option['a'], $option['w'], $option['h'], $option['x'], $option['y']);
			
			$file = array('md5' => md5($new), 'file_name' => $file_info['file_name'], 'file_size' => strlen($new), 
						'create_time' => $_SERVER['REQUEST_TIME']);
			if ($file_exist = Dba::readRowAssoc('select * from files where md5 = "'.$file['md5'].'"')) {
				$dst = UploadMod::get_file_dst($file_exist);
				//var_dump($dst);
				if(!file_exists($dst)) {
					file_put_contents($dst, $new);
					
				}
				return UploadMod::fill_file_url($file_exist);
			}
			
			Dba::beginTransaction(); {
				Dba::insert('files', $file);
				$file['uid'] = Dba::insertID();
				$dst = UploadMod::get_file_dst($file);
				file_put_contents($dst, $new);
				chmod($dst, 0777);
			} Dba::commit();

			//保存一个原始文件
			$dst_orig = $dst.'.orig';
			copy($origin_file, $dst_orig);
			chmod($dst_orig, 0777);
		}
		catch (Exception $e) {
			return false;
		}
		
		return UploadMod::fill_file_url($file);
	}



}


