<?php

	class ArticleSpiderMod {
		private function __construct()
		{


		}

		public static function fetch($url)
		{
			//        var_dump($url);
			include_once UCT_PATH . 'vendor/snoopy/snoopy.php';
			$snoopy = new Snoopy();
			$ret    = $snoopy->fetch($url);
			//        var_dump($snoopy->results);
			//        $snoopy->_striptext($snoopy->results);
			return $snoopy;


		}

		public static function get_sogou_list($content)
		{
			$ret = preg_match_all('/<li[^>]*>.*?<\/li>/ius', $content, $ret) ? $ret[0] : '';
			//        var_dump($ret);exit;
			if(!$ret) return array();
			$list  = array();
			$count = count($ret);
			for ($i = 0; $i < $count; $i++)
			{

				if (preg_match('/<h3>.*?<\/h3>/u', $ret[$i], $h3))
				{
					$list[$i]['art_title'] = preg_match('/">.*?</u', $h3[0], $rets) ? (strtr($rets[0], array('">' => '', '<'   => ''))) : '';

					$list[$i]['art_href']  = preg_match('/(?!href=")http(s)?:\/\/mp.weixin.qq.com[^"]*(?=")/u', $h3[0], $rets) ? $rets[0] : '';
				}


				$list[$i]['art_digest'] = preg_match('/-all">[^<]*?(?=<)/u', $ret[$i], $rets) ? strtr($rets[0], array('-all">' => '')) : '';


				$list[$i]['art_img_url'] = preg_match('/http(s)?:\/\/img.*jpeg/u', $ret[$i], $rets) ? $rets[0] : '';


				$rets = preg_match_all('/http(s)?:\/\/img0[0-9]\.sogoucdn[^"]*(?=")/u', $ret[$i], $rets) ? $rets[0] : '';

				$list[$i]['gzh_img_url']     = (count($rets) == 3) ? $rets[0] : '';
				$list[$i]['gzh_erweima_url'] = (count($rets) == 3) ? $rets[2] : '';

				$list[$i]['gzh_title'] = preg_match('/title="[^"]*"/u', $ret[$i], $rets) ? strtr($rets[0], array('title="' => '',
				                                                                                                 '"'       => '')) : '';
				$list[$i]['gzh_url']   = preg_match('/http(s)?:\/\/weixin.sogou.com\/gzh\?openid=[^"]*(?=")/u', $ret[$i], $rets) ? $rets[0] : '';


				$list[$i]['read_count'] = preg_match('/&nbsp;[0-9+]+&nbsp;/u', $ret[$i], $rets) ? strtr($rets[0], array('&nbsp;' => '')) : '';

//				$list[$i]['get_time'] = preg_match('/&nbsp;&nbsp;&nbsp;.*?(?=<\/div>)/u', $ret[$i], $rets) ? strtr($rets[0], array('&nbsp;' => '','</div>' => '')) : '';
//				<span class="s2" t="1508301473"></span>
				$list[$i]['get_time'] = preg_match('/t=".*?(?=<\/span>)/u', $ret[$i], $rets) ? strtr($rets[0], array('t="' => '','">' => '')) : '';

				if(!empty($list[$i]['get_time'])){
					$list[$i]['get_time'] = date('Y-m-d H:i',$list[$i]['get_time']);
				}else{
					$list[$i]['get_time'] = date('Y-m-d H:i',$_SERVER['REQUEST_TIME']);
				}

			}

			return $list;
		}

		/*
			取文章页面内容
		*/
		public static function get_article_content($content)
		{
			$contents                       = array();
			$contents['title']              = preg_match('/activity-name">.*?(?=<\/h2>)/ius', $content, $ret) ? trim(strtr($ret[0], array('activity-name">' => '','&nbsp;'=>''))) : '';
//			$contents['time']              = preg_match('/media_meta_text">.*?(?=<\/em>)/ius', $content, $ret) ? trim(strtr($ret[0], array('media_meta_text">' => '','&nbsp;'=>''))) : '';
			$contents['time']    = preg_match('/ct = ".*(?=";)/u', $content, $rets) ? strtr($rets[0], array('ct = ' => '','"' => '')) : '';
			$contents['url']                = preg_match('/msg_cdn_url = "[^"]*?(?=")/ius', $content, $ret) ? strtr($ret[0], array('msg_cdn_url = "' => '')) : '';
			$contents['digest']             = preg_match('/msg_desc = "[^"]*?(?=")/ius', $content, $ret) ? strtr($ret[0], array('msg_desc = "' => '',
			                                                                                                                    '$nbsp;'       => '')) : '';
			$contents['content']            = preg_match('/js_content">.*?(?=<\/div>)/ius', $content, $ret) ? trim(strtr($ret[0], array('js_content">' => '',
			                                                                                                                            'data-src'     => 'src',
			                                                                                                                            'src="/'=>'src="http://mp.weixin.qq.com/'))) : '';
			$contents['content_source_url'] = preg_match('/msg_link = "[^"]*?(?=")/ius', $content, $ret) ? trim(strtr($ret[0], array('msg_link = "' => '',
			                                                                                                                         '$nbsp'        => '',
			                                                                                                                         'amp;'=>''))) : '';

			return $contents;
		}


		/*
			根据关键字搜索文章
		*/
		public static function get_sougou_by_key($ret_fetch)
		{
			$content = $ret_fetch->results;
			$ret     = preg_match_all('/<li id[^>]*>.*?<\/li>/ius', $content, $ret) ? $ret[0] : '';
//			var_dump(__file__.' line:'.__line__,$ret);exit;
			if(empty($ret)) return false;
			$list    = array();
			$count   = count($ret);
			//$count-2,后面包含组js匹配一致
			for ($i = 0; $i < $count-2; $i++)
			{
				$list[$i]['art_title']  = preg_match('/div>.*<em>.*(?=<\/div>)/u', $ret[$i], $rets) ? (strtr($rets[0], array('div>'          => '',
					'<'             => '',
					'!--red_beg-->' => '',
					'!--red_end-->' => '',
					'em>'           => '',
					'/'             => ''))) : '';
				$list[$i]['art_href']   = preg_match('/(?!href=")http(s)?:\/\/mp.weixin.qq.com[^"]*(?=")/u', $ret[$i], $rets) ? (strtr($rets[0], array('amp;' => '', '/websearch/' => 'http://weixin.sogou.com/websearch/'))) : '';
				$list[$i]['art_digest'] = preg_match('/-all">[^<]*?(?=<)/u', $ret[$i], $rets) ? strtr($rets[0], array('-all">' => '')) : '';
//				var_dump($list[$i]['art_href']);

				$headers[] = strtr($ret_fetch->headers[8], array('Set-' => ''));

//				$list[$i]['art_href'] = self::get_article_by_key($list[$i]['art_href'], $headers);


				$list[$i]['art_img_url'] = preg_match('/http(s)?:\/\/img.*jpeg/u', $ret[$i], $rets) ? strtr($rets[0], array('amp;' => '')) : '';
				$list[$i]['gzh_title']   = preg_match('/title="[^"]*"/u', $ret[$i], $rets) ? strtr($rets[0], array('title="' => '',
				                                                                                                   '"'       => '')) : '';
//				$list[$i]['get_time']    = preg_match('/t="[\w]+/u', $ret[$i], $rets) ? strtr($rets[0], array('t="' => '')) : '';
//				data-lastmodified="1506560881"
				$list[$i]['get_time']    = preg_match('/dified=".*(?=">)/u', $ret[$i], $rets) ? strtr($rets[0], array('dified="' => '','"' => '')) : '';
				if(empty($list[$i]['get_time'])||$list[$i]['get_time']==1){
					$list[$i]['get_time'] = $_SERVER['REQUEST_TIME'];
				}
				$list[$i]['get_time']    = isset($list[$i]['get_time']) ? date('Y-m-d H:i', $list[$i]['get_time']) : '';

			}

			return $list;
		}

		public static function get_article_by_key($url, $header)
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_HTTPHEADER, $header); //设置头信息的地方
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($c, CURLOPT_HEADER, 1);
			$ret           = curl_exec($c);
			$headerSize    = curl_getinfo($c, CURLINFO_HEADER_SIZE);
			$header        = substr($ret, 0, $headerSize);
			$body          = substr($ret, $headerSize);
			$ret           = null;
			$ret['header'] = $header;
			$ret['body']   = $body;
			curl_close($c);

			return preg_match('/(?!href=")http(s)?:\/\/mp.weixin.qq.com[^"]*(?=")/u', $ret['body'], $rets) ? strtr($rets[0], array('amp;' => '')) : '';


		}


		public static function get_last_script($content)
		{
			preg_match_all('/<script type="text\/javascript">[^>]+>/', $content, $ret);
			$count = count($ret[0]);
			$ret   = $ret[0][($count - 1)];
			$ret   = strtr($ret, array('var' => '', "\s" => ''));

			return $ret;
		}


	}

