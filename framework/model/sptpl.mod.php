<?php
/*
	后台模板
*/

class SptplMod {
	//获取模板列表
	public static function get_tpls_list($option = array()) {
		$dir = UCT_PATH.'app'.DS.$GLOBALS['_UCT']['APP'].DS.'view'.DS;
		$ds = scandir($dir);
		$ds = array_diff($ds, array('.', '..'));
		$all = array();
		foreach($ds as $d) {
			if(!is_dir($dir.$d) || !file_exists($dir.$d.DS.'tplinfo.php')) {
				continue;
			}
			$p = include $dir.$d.DS.'tplinfo.php';
			$p['dir'] = $d;
			if(!empty($option['with_all']) || empty($p['hide'])) {
				$all[] = $p;
			}
		}
		if(!empty($option['type']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return $i['type'] == $option['type'];
			});
		}
		if(!empty($option['industry']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return $i['industry'] == $option['industry'];
			});
		}
		if(!empty($option['key']) && $all) {
			$all = array_filter($all, function($i) use($option) {
				return stripos($i['name'], $option['key']) !== false;
			});
		}

		$cnt = count($all);
		if($option['limit'] >= 0) {
			$all = array_slice($all, $option['page']*$option['limit'], $option['limit']);
		}
		/*
		array_walk($all, function(&$i){
			$i['has_installed'] = WeixinPlugMod::is_plugin_installed($i);
		});
		*/
//		var_dump(__file__.' line:'.__line__,$cnt,$all);exit;
		return array('count' => $cnt, 'list' => $all);
	}

}

