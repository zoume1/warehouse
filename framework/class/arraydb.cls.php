<?php
/*
数组式的kv存储类， 采用数据库保存数据

一张数据表结构如下
create table `arraydb_sys` (
    `ukey` varchar(32) not null comment '键',
    `data` text not null  comment '值',
    `expire_time` int unsigned default '0' comment '到期时间, 0不过期',

    unique key `ukey`(`ukey`)
)engine=innodb default charset=utf8;


*/

class ArrayDb implements ArrayAccess{
	protected $table = '';
	
	/*
		增加一个内存cache， 注意这个cache没有判断过期时间
	*/
	protected $cache = array();

	/*
		构造函数， $table 指定数据表
	*/
	public function __construct($table = 'arraydb_sys') {
		$this->table = $table;
	}

	// isset($obj[$key])
	public function offsetExists($key) {
		if(isset($this->cache[$key])) {
			return true;
		}		

		$sql = 'select * from '.$this->table.' where ukey = "'.addslashes($key).'"';
		if(!($obj = Dba::readRowAssoc($sql))) {
			return false;
		}
		
		if ($obj['expire_time'] == 0 || (time() < $obj['expire_time'])) {
			$this->cache[$key] = $obj['data'];
			return true;
		}
		return false;
	}

	/* 
		($obj[$key])
		这里没有刷新过期时间	
	*/
	public function offsetGet($key) {
		if(isset($this->cache[$key])) {
			return $this->cache[$key];
		}		

		$sql = 'select * from '.$this->table.' where ukey = "'.addslashes($key).'"';
		if(!($obj = Dba::readRowAssoc($sql))) {
			return null;
		}
		
		if($obj['expire_time'] != 0 && (time() > $obj['expire_time'])) {
			$this->offsetUnset($key);
			return false;
		}

		return $this->cache[$key] = $obj['data'];	
	}

	/* 	
		$obj[$key] = $value
		$value 可以是字符串或数组
		array('expire' => 0, 'value' => 'xxxxxx')

		如果指定了expire 则刷新过期时间	
	*/
	public function offsetSet ($key, $value) {
		if (!is_array($value)){
			if($old = $this->offsetGet($key)) {
				if($old == $value) {
					return;
				}
				$update = array(
					'data' => $value,
				);
				Dba::update($this->table, $update, 'ukey = "'.addslashes($key).'"');
				$this->cache[$key] = $value;
				return;
			}
		}

		$expire = 0;
		if(is_array($value)) {
			$expire = $value['expire'];
			$value = $value['value'];
		}
		if($expire > 0 && $expire <= 2592000) { //86400 * 30
			$expire += time();
		}

		$replace = array(
			'ukey' => $key,
			'data' => $value,
			'expire_time' => $expire,
		);
		Dba::replace($this->table, $replace);
		$this->cache[$key] = $value;	
	}

	// unset($obj[$key])
	public function offsetUnset($key) {
		unset($this->cache[$key]);

		$sql = 'delete from '.$this->table.' where ukey = "'.addslashes($key).'"';
		Dba::write($sql);
	}
}

