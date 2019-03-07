<?php
/*
	微信第三方平台自动授权 每10分钟更新一次的 component_verify_ticket 
	更新到所有域名下

	2015-10-21这个job不再使用, 改为直接同步component_access_token 

*/

include_once UCT_PATH . 'vendor/weixin_encrypt/wxBizMsgCrypt.php';
class BroadcastTicketJob {
	public function perform($ticket) {
		$url = '?_u=component.callback';
		$xml = '<xml><AppId>'.COMPONENT_APPID.
				'</AppId><CreateTime>'.time().
				'</CreateTime><InfoType>component_verify_ticket</InfoType><ComponentVerifyTicket>'.
				$ticket.'</ComponentVerifyTicket></xml>';
		$pc = new Prpcrypt(COMPONENT_KEY);
		$enc_xml = $pc->encrypt($xml, COMPONENT_APPID);
		if($enc_xml[0] != 0) {
			echo 'fatal error! encrypt msg failed!';
			return false;
		}
		$enc_xml = '<xml><AppId>'.COMPONENT_APPID.'</AppId><Encrypt>'.$enc_xml[1].'</Encrypt></xml>';
		
		uct_use_app('domain');
		$ds = DomainMod::get_all_uct_top_domains();	
		echo '['.date('Y-m-d H:i:s').'] sync comopnent ticket ['.$ticket.']'; 
		foreach($ds as $d => $v) {
			if($d == 'uctphp.com') {
				continue;
			}
			
			$ret = $this->call('http://weixin.'.$d.$url, $enc_xml);
			echo '-- to ['.$d.'] => ['.$ret .'] --';
		}
		
		echo '. done !';
	}

	public function call($url, $data) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($c);
		curl_close($c);

		return $ret;
	}
}

