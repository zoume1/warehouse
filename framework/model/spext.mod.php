<?php

/*
	后台特定模板设置

	如某个模板需要设置额外颜色信息
*/

class SpExtMod {

	/*
		后台模板额外设置

		$k 保存的键名 默认 cfg_{$app}_{$tpl}_{$act}_{$sp_uid}
		$v 值 mix
	*/
	public static function set_sp_ext_cfg($v, $k = '') {
		if(!$k)  $k = 'cfg_' . $GLOBALS['_UCT']['APP'] . '_' . $GLOBALS['_UCT']['TPL'] . '_' . $GLOBALS['_UCT']['ACT'] . '_' 
					. AccountMod::get_current_service_provider('uid');

		return $GLOBALS['arraydb_sys'][$k] = json_encode($v);
	}

	public static function get_sp_ext_cfg($k = '') {
		if(!$k)  $k = 'cfg_' . $GLOBALS['_UCT']['APP'] . '_' . $GLOBALS['_UCT']['TPL'] . '_' . $GLOBALS['_UCT']['ACT'] . '_' 
					. AccountMod::get_current_service_provider('uid');

		return empty($GLOBALS['arraydb_sys'][$k]) ? false : json_decode($GLOBALS['arraydb_sys'][$k], true);
	}
	
}

