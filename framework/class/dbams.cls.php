<?php
/*

mssql 注意事项
	可直接指定数据库地址账号密码, 或者调用init
	define('DBMS_DSN', 'mssql:dbname=weixin_db;host=127.0.0.1;port=1343');
	define('DBMS_USER', 'admin');
	define('DBMS_PASSWD', '123');

	* mssql 字段不区分大小写，
	* 查询条件不能用 && || 而是用 and or
	* 引号只能用单引号' 不能用双引号 "
	* 没有limit replace 等语法
*/

class Dbams {
    private static $link = null;

	//sql执行出错时是否直接退出, swoole php会长时间运行，所以出错时试着重连一次
	private static $exit = false;

	//保存pdo->exec返回结果
	private static $last_affect_count = 0;
    private static $queue=array();
    //强制返回 不转码
    public static $iconv_forse = false;

	public static function init($dsn=DBMS_DSN,$user=DBMS_USER,$pwd=DBMS_PASSWD,$new = 0) {
	//lazy connect

        if (!self::$link || $new) {
            Dbams::connect($dsn, $user, $pwd);
        }

	}

	private static function connect($dsn=DBMS_DSN,$user=DBMS_USER,$pwd=DBMS_PASSWD) {
		try {
			self::$link = new PDO(
                $dsn,
                $user,
                $pwd,
                array(
                    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
                ));
		} catch (PDOException $e) {
			echo ('failed to connect db! ['.$e->getCode().'] '.$e->getMessage()); exit(1);
		}

		#for safety
		#Dbams::write('rollback');
	}
	
	public static function exitOnSqlError($exit = true) {
		self::$exit = $exit;
	}

	public static function read($sql) {
		if (self::$link === null) {
			self::connect();
		}
        $sql = self::array_iconv($sql,'gbk');
		#if(!empty($GLOBALS['_d'])) echo $sql;
		$ps = self::$link->prepare($sql);
		if (false === $ps->execute()) {
			if (self::$exit) {
				echo ('error in mssql query! '.var_export($ps->errorInfo(), true).' => '.$sql); exit(1);
			}
			else {
				log_error('[warn] error in mssql query! '.var_export($ps->errorInfo(), true).' => '.$sql);
				//mssql连接错误 CR_SERVER_GONE_ERROR 尝试重新连接
				$err = $ps->errorInfo();
				$errCode = $err[1];
				if (in_array($errCode, array(2006))) {
					log_error('mssql try again...', $sql);
                    $ps->closeCursor();
					return self::read($sql);
				}

				return false;
			}
		}
		return $ps;
	}

	public static function write($sql) {
		if (self::$link === null) {
			self::connect();
		}
        $sql = self::array_iconv($sql,'gbk');
		self::$last_affect_count = 0;
		self::$last_affect_count = self::$link->exec($sql);
		if (false === self::$last_affect_count) {
			if (self::$exit) {
				echo ('error in mssql query! '.self::error().' => '.$sql); exit(1);
			}
			else {
				log_error('[warn] error in mssql exec! '.var_export(self::$link->errorInfo(), true).' => '.$sql);
				//mssql连接错误 CR_SERVER_GONE_ERROR 尝试重新连接
				$err = self::$link->errorInfo();
				$errCode = $err[1];
				if (in_array($errCode, array(2006))) {
					log_error('mssql try again...', $sql);
					self::close();

					return self::write($sql);
				}

				return false;
			}
		}
		//log_verbose('mssql write ->> '.$sql);
		return self::$last_affect_count;
	}

	//如果有嵌套事务, 可以临时禁用事务
	private static $disable_transaction = false;
	public static function disableTransaction($disable = true) {
		self::$disable_transaction = $disable;
	}

	public static function beginTransaction() {
		if (self::$link === null) {
			self::connect();
		}

		#self::$exit = false;
        array_push(self::$queue,md5(time()));
        if (self::$disable_transaction) return true;
        if(self::$link->inTransaction()) return true;
		return self::$link->beginTransaction();
	}

	public static function rollBack() {
		#self::$exit = true;
		if (self::$disable_transaction) return true;
		return self::$link->rollBack();
	}

	public static function commit() {
		#self::$exit = true;
        array_shift(self::$queue);
		if (self::$disable_transaction) return true;
        if(count(self::$queue)>0) return true;
		return self::$link->commit();
	}

	public static function inTransaction() {
		if (self::$link === null) {
			return false;
		}
		return self::$link->inTransaction();
	}

	public static function newSavePoint($name) {
		Dbams::write('savepoint '.$name);
	}

	public static function rollBackToSavePoint($name) {
		Dbams::write('rollback to savepoint '.$name);
	}

	public static function nestedBeginTransaction() {
		if (Dbams::inTransaction()) {
			return false;
		}
		Dbams::beginTransaction();
		return true;
	}

	//设置事务隔离级别，mssql默认3
	public static function setIsolationLevel($level = 3) {
		switch($level) {
			case 1:
				$l = 'read uncommitted';
				break;
			case 3:
			default:
				$l = 'repeatable read';
				break;
		}

		Dbams::write('set session transaction isolation level '.$l);
	}


	public static function insertID() {
		return self::$link->lastInsertId();
	}

	//只支持write影响行数
	public static function affectedRows() {
		return self::$last_affect_count;
	}

	public static function fetchRow($ps) {
        $ret = $ps->fetch(PDO::FETCH_NUM);
        $ret = self::array_iconv($ret);
        return $ret;
	}

	public static function fetchAssoc($ps) {
	    $ret = $ps->fetch(PDO::FETCH_ASSOC);
        $ret = self::array_iconv($ret);
        return $ret;
	}

	/*
	* 返回查询结果总数以及部分结果
	* 支持只包含一个小写from语句的sql
    * $index 表的主键
    * 随着数据量增加 性能会下降
	* eg:$ret=  Dbams::readCountAndLimit('*','CardUseBound','0','9','ID<20 and dsn>5','PrjId desc,dsn ','ID');
	*/
	public static function readCountAndLimit($field='*',$table, $page = 0, $limit = -1,$where='',$order='',$index = 'id', $func_filter = null) {
        $where_str ='';
        empty($where) || $where_str = ' where '.$where;
        empty($order) || $order = ' order by '.$order;
        $sql = sprintf('select %s from %s %s %s',$field,$table,$where_str,$order);
        $sql_cnt = sprintf('select count(*) from %s %s ',$table,$where_str);
        $count = self::readOne($sql_cnt);
        if ($page * $limit >= $count) {
            return array('count' => $count, 'list' => array());
        }
		if ($limit >= 0) {
            if($page == 0){
                $sql_result = sprintf('select top %d %s from %s %s %s',$limit,$field,$table,$where_str,$order);
            }else
            {
                $sql_result =sprintf('select top %d %s from %s %s %s',$page*$limit,$index,$table,$where_str,$order);
                empty($where) || $where = ' and '.$where;
                $sql_result = sprintf('select top %d %s from %s where %s not in (%s) %s %s',$limit,$field,$table,$index,$sql_result,$where,$order);;
            }
		}
		else {
            $sql_result = $sql;
        }
//        var_dump(__FILE__.' line:'.__LINE__,$sql_result);exit;
		return array(
				'count'  => $count,
				'list' => self::readAllAssoc($sql_result, $func_filter), 	
				);
	}

	public static function readOne($sql) {
        $sql = preg_replace('/select(\s*top\s*1)?/i','select top 1 ',$sql);
		if (!($query = self::read($sql))) {
			return false;
		}
		if (!($ret = self::fetchRow($query))) {
			return false;
		}

		return $ret[0];
	}

	public static function readRow($sql) {
        $sql = preg_replace('/select(\s*top\s*1)?/i','select top 1 ',$sql);
        if (!($query = self::read($sql))) {
			return false;
		}
        if (!($ret = self::fetchRow($query))) {
            return false;
        }
        return $ret;
	}

	public static function readRowAssoc($sql, $func_filter = null) {
        $sql = preg_replace('/select(\s*top\s*1)?/i','select top 1 ',$sql);
        if (!($query = self::read($sql))) {
			return false;
		}

		$item = self::fetchAssoc($query);
		if (!$item || !$func_filter) {
			return $item;
		}

        $rr = call_user_func($func_filter, $item);
		if ($rr !== false) {
			return $rr;
		}

		return false;
	}

	public static function readAll($sql, $func_filter = null) {
		if (!($query = self::read($sql))) {
			return false;
		}

		$ret = array();
		if (!$func_filter) {
			while($item = self::fetchRow($query)) {
				$ret[] = $item;
			}
		}
		else {
			while($item = self::fetchRow($query)) {
				$rr = call_user_func($func_filter, $item);
				if ($rr !== false) {
					$ret[] = $rr;
				}
			}
		}
		return $ret;
	}

	public static function readAllAssoc($sql, $func_filter = null) {
		if (!($query = self::read($sql))) {
			return false;
		}

		$ret = array();
		if (!$func_filter) {
			while($item = self::fetchAssoc($query)) {
				$ret[] = $item;
			}
		}
		else {
			while($item = self::fetchAssoc($query)) {
				$rr = call_user_func($func_filter, $item);
				if ($rr !== false) {
					$ret[] = $rr;
				}
			}
		}
		return $ret;
	}

	public static function readAllOne($sql, $func_filter = null) {
		if (!($query = self::read($sql))) {
			return false;
		}

		$ret = array();
		if (!$func_filter) {
			while($item = self::fetchRow($query)) {
				$ret[] = $item[0];
			}
		}
		else {
			while($item = self::fetchRow($query)) {
				$rr = call_user_func($func_filter, $item[0]);
				if ($rr !== false) {
					$ret[] = $rr;
				}
			}
		}
		return $ret;
	}


	public static function errno() {
		return self::$link->errorCode();
	}

	public static function error() {
		return var_export(self::$link->errorInfo(), true);
	}

	/**
	 * 生成sql update语句中的set k1 = v1, k2 = v2... 部分
	 * @param $kv 字段数组
	 * @param $k 只生成指定的键值
	 */
	public static function makeSet($kv, $k = null) {
	$set = array();
	 if ($k) {
	 	array_walk($kv, function($value, $key) use (&$set, &$k){
	 		if (in_array($key, $k)){
				if(is_array($value)) $value = json_encode($value);
	 			$set[] = '['.$key.'] = ' . (is_string($value) ? ('\''.addslashes($value).'\'') : $value);
	 		}
	 	});
	 }	
	 else {
	 	array_walk($kv, function($value, $key) use (&$set){
			if(is_array($value)) $value = json_encode($value);
	 		$set[] = '['.$key.'] = ' . (is_string($value) ? ('\''.addslashes($value).'\'') : $value);
	 	});
	 }
	 
	 return implode(' , ', $set);
	}

	public static function makeUpdate($table, $kv, $k = null, $where = null) {
		$sql = 'update ['.$table.'] set ' . self::makeSet($kv, $k);	
		if ($where) {
			$sql .= ' where '.$where;
		}
		return $sql;
	}

	/*
	*  生成sql 语句中的in (...) 部分
	*/
	public static function makeIn($tag) {
		return self::makeValue($tag);
	}

	/**
	 * 生成sql insert语句中的values (p1, q1...) 部分
	 * @param $tag   字段数组
	 * @param $keys  按k的顺序生成，否则按kv默认生成 
	 */
	public static function makeValue($tag, $keys = null) {
		$groups = array();
		if ($keys) {
				$value = array();
				foreach($keys as $k) {
                    $k = ltrim($k, '[');
                    $k = rtrim($k, ']');
					if(is_array($tag[$k])) $tag[$k] = json_encode($tag[$k]);
		 			$value[] = (is_string($tag[$k]) ? ('\''.addslashes($tag[$k]).'\'') : $tag[$k]);
				}
				return '('.implode(' , ', $value).')';
		}
		else {
				$value = array();
				foreach($tag as $v) {
					if(is_array($v)) $v = json_encode($v);
		 			$value[] = (is_string($v) ? ('\''.addslashes($v).'\'') : $v);
				}
				return '('.implode(' , ', $value).')';
		}
	}

	/**
	 * 生成sql insert语句中的values (p1, q1...), (p2, q2...)... 部分
	 * @param $tags 字段二维数组
	 * @param $keys  按k的顺序生成，否则按kv默认生成
	 */
	public static function makeValueS($tags, $keys = null) {
		$groups = array();
		if ($keys) {
			foreach ($tags as $tag) {
				$value = array();
				foreach($keys as $k) {
					$k = ltrim($k, '[');
					$k = rtrim($k, ']');
					if(is_array($tag[$k])) $tag[$k] = json_encode($tag[$k]);
		 			$value[] = (is_string($tag[$k]) ? ('\''.addslashes($tag[$k]).'\'') : $tag[$k]);
				}
				$groups[] = '('.implode(' , ', $value).')';
		 	}
		}
		else {
			foreach ($tags as $tag) {
				$value = array();
				foreach($tag as $v) {
					if(is_array($v)) $v = json_encode($v);
		 			$value[] = (is_string($v) ? ('\''.addslashes($v).'\'') : $v);
				}
				$groups[] = '('.implode(' , ', $value).')';
		 	}
		}
		 	
		return implode(' , ', $groups);
	}

	/**
	 *生成一条sql语句 insert into table (...) values (...)
	*/
	public static function makeInsert($table, $values, $keys = null) {
		$sql = 'insert into ['.$table.']'; 
		if ($keys) {
			$sql .= ' (['.implode('], [', $keys).']) ';
		}
		$sql .= ' values '.Dbams::makeValue($values, $keys);
		return $sql;
	}

//	/**
//	 *生成一条sql语句 replace into table (...) values (...)
//	*/
//	public static function makeReplace($table, $values, $keys = null) {
//		$sql = 'replace  ['.$table.']';
//		if ($keys) {
//			$sql .= ' (['.implode('], [', $keys).']) ';
//		}
//		$sql .= ' values '.Dbams::makeValue($values, $keys);
//
//		return $sql;
//	}

	/**
	 *生成一条sql语句 insert into table (...) values (...), (...), (...) 
	*/
	public static function makeInsertS($table, $values, $keys = null) {
		if (!$keys) {
			$keys = array_keys($values[0]);
		}
		return 'insert into ['.$table.'] (['.implode('], [', $keys).'])  values '.Dbams::makeValueS($values, $keys);
	}

	public static function insert($table, $insert) {
		return self::write(self::makeInsert($table, $insert, array_keys($insert)));
	}

	public static function insertS($table, $inserts) {
		return self::write(self::makeInsertS($table, $inserts));
	}

// mssql 不支持 repalce into
//	public static function replace($table, $insert) {
//		return self::write(self::makeReplace($table, $insert, array_keys($insert)));
//	}

	public static function update($table, $update, $where) {
		return self::write(self::makeUpdate($table, $update, array_keys($update), $where));
	}

    /**
     * 对数据进行编码转换
     * @param array/string $data       数组
     * @param string $output    转换后的编码
     */
    public static function array_iconv($data,  $output = 'utf-8') {
//        if( self::$iconv_forse || is_win() )
        if( self::$iconv_forse )
        {
            return $data;
        }
        $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
        if (!is_array($data)) {
	    $encoded = mb_detect_encoding($data, $encode_arr);
                return mb_convert_encoding($data, $output, $encoded);
        }
        else {
            foreach ($data as $key=>$val) {
                $key =  self::array_iconv($key, $output);
                if(is_array($val)) {
                    $data[$key] = self::array_iconv($val, $output);
                } else {
		 	$encoded = mb_detect_encoding($val, $encode_arr);                    
                    $data[$key] = mb_convert_encoding($val, $output, $encoded);
                }
            }
            return $data;
        }
    }

}


