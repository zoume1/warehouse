<?php
function log_error($str) {
	error_log($str);
}

function sql_log($sql){
    $log_dir = UCT_PATH.(defined('LOG_DIR')? LOG_DIR : 'log/');
    if (!file_exists($log_dir))
    {
        @mkdir($log_dir, 0777);
        @chmod($log_dir, 0777);
    }
    $logfilename =  $log_dir. 'mysql_query_' . date('Y_m_d') . '.log';
    //$debug = debug_backtrace( );  //获取函数调用
    //$debug = array_slice($debug,3,-1,true);
    //$trace = var_export($debug,true);
    $e = new Exception;
    $trace=$e->getTraceAsString();
    $str = $sql ."\n".$trace ."\n\n";

    if (PHP_VERSION >= '5.0')
    {
        file_put_contents($logfilename, $str, FILE_APPEND);
    }
    else
    {
        $fp = @fopen($logfilename, 'ab+');
        if ($fp)
        {
            fwrite($fp, $str);
            fclose($fp);
        }
    }
}

class Dba {
	private static $link = null;

	//sql执行出错时是否直接退出, swoole php会长时间运行，所以出错时试着重连一次
	private static $exit = false;

	//保存pdo->exec返回结果
	private static $last_affect_count = 0;

    private static $queue=array();

	public static function init() {
	//lazy connect
	  /*
		if (!self::$link) {
			Dba::connect();
		}	
	  */
	}
	
	public static function hasConnected() {
		return self::$link !== null;
	}
	
	public static function close() {
		self::$link = null;
	}

	private static function connect() {
		try {
			self::$link = new PDO(
					DB_DSN,
					DB_USER,
					DB_PASSWD,
					array(
						PDO::ATTR_PERSISTENT => false,
						#PDO::ATTR_PERSISTENT => true,
						#PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::MYSQL_ATTR_INIT_COMMAND => 'set names \'utf8\'',
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
					));
		} catch (PDOException $e) {
			echo ('failed to connect db! ['.$e->getCode().'] '.$e->getMessage()); exit(1);
		}

		#for safety
		#Dba::write('rollback');
	}
	
	public static function exitOnSqlError($exit = true) {
		self::$exit = $exit;
	}

	public static function read($sql) {
		if (self::$link === null) {
			self::connect();
		}

        /*_start by ccf 2018-06-07*/
        if (defined('SQL_DEBUG')&&SQL_DEBUG)
        {
            sql_log($sql);
        }
        /*_end by ccf 2018-06-07*/

		$ps = self::$link->prepare($sql);
		if (false === $ps->execute()) {
			if (self::$exit) {
				echo ('error in mysql query! '.var_export($ps->errorInfo(), true).' => '.$sql); exit(1);
			}
			else {
				log_error('[warn] error in mysql query! '.var_export($ps->errorInfo(), true).' => '.$sql);
				//mysql连接错误 CR_SERVER_GONE_ERROR 尝试重新连接
				$err = $ps->errorInfo();
				$errCode = $err[1];
				if (in_array($errCode, array(2006))) {
					log_error('mysql try again...', $sql);
					self::close();
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

        /*_start by ccf 2018-06-07*/
        if (defined('SQL_DEBUG')&&SQL_DEBUG)
        {
            sql_log($sql);
        }
        /*_end by ccf 2018-06-07*/

		self::$last_affect_count = 0;
		self::$last_affect_count = self::$link->exec($sql);
		if (false === self::$last_affect_count) {
			if (self::$exit) {
				echo ('error in mysql query! '.self::error().' => '.$sql); exit(1);
			}
			else {
				log_error('[warn] error in mysql exec! '.var_export(self::$link->errorInfo(), true).' => '.$sql);
				//mysql连接错误 CR_SERVER_GONE_ERROR 尝试重新连接
				$err = self::$link->errorInfo();
				$errCode = $err[1];
				if (in_array($errCode, array(2006))) {
					log_error('mysql try again...', $sql);
					self::close();
					
					return self::write($sql);	
				}

				/*
					??? why 2018-07-03
					ER_DUP_ENTRY 1062: Duplicate entry '%s' for key %d   
				*/
				if (in_array($errCode, array(1062))) {
					self::$exit = true;
					log_error('mysql just try again...', $sql);
					return self::write($sql);	
				}
				

				return false;
			}
		}
		//log_verbose('mysql write ->> '.$sql);
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
		Dba::write('savepoint '.$name);
	}

	public static function rollBackToSavePoint($name) {
		Dba::write('rollback to savepoint '.$name);
	}

	public static function nestedBeginTransaction() {
		if (Dba::inTransaction()) {
			return false;
		}
		Dba::beginTransaction();
		return true;
	}

	//设置事务隔离级别，mysql默认3
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

		Dba::write('set session transaction isolation level '.$l);
	}


	public static function insertID() {
		return self::$link->lastInsertId();
	}
	 
	//只支持write影响行数
	public static function affectedRows() {
		return self::$last_affect_count;
	}
	
	public static function fetchRow($ps) {
		return $ps->fetch(PDO::FETCH_NUM);
	}

	public static function fetchAssoc($ps) {
		return $ps->fetch(PDO::FETCH_ASSOC);
	}

	/*
	* 返回查询结果总数以及部分结果
	* 支持只包含一个小写from语句的sql
	*/
	public static function readCountAndLimit($sql, $page = 0, $limit = -1, $func_filter = null) {
		$arr = explode(' from ', $sql, 2);
		if (count($arr) != 2) {
			echo ('this sql do not support count_and_limit ! => '.$sql); exit(1);
		}
		$arr[1] = current(explode('order by', $arr[1]));
		$sql_cnt = 'select count(*) from '.$arr[1];
		$count = self::readOne($sql_cnt); 
		if ($page * $limit >= $count) {
			return array('count' => $count, 'list' => array());
		}
		if ($limit >= 0) {
			$sql_result = $sql.' limit ' . (($page) ? ($page * $limit).', '.$limit : $limit);
		}
		else {
			$sql_result = $sql;
		}
		return array(
				'count'  => $count,
				'list' => self::readAllAssoc($sql_result, $func_filter), 	
				);
	}

	public static function readOne($sql) {
		if (!($query = self::read($sql))) {
			return false;
		}
		if (!($ret = self::fetchRow($query))) {
			return false;
		}

		return $ret[0];
	}

	public static function readRow($sql) {
		if (!($query = self::read($sql))) {
			return false;
		}

		return self::fetchRow($query);
	}

	public static function readRowAssoc($sql, $func_filter = null) {
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
	 			$set[] = '`'.$key.'` = ' . (is_string($value) ? ('\''.addslashes($value).'\'') : $value);
	 		}
	 	});
	 }	
	 else {
	 	array_walk($kv, function($value, $key) use (&$set){
			if(is_array($value)) $value = json_encode($value);
	 		$set[] = '`'.$key.'` = ' . (is_string($value) ? ('\''.addslashes($value).'\'') : $value);
	 	});
	 }
	 
	 return implode(' , ', $set);
	}

	public static function makeUpdate($table, $kv, $k = null, $where = null) {
		$sql = 'update `'.$table.'` set ' . self::makeSet($kv, $k);	
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
					$k = trim($k, '`'); 
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
					$k = trim($k, '`'); 
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
		$sql = 'insert into `'.$table.'`'; 
		if ($keys) {
			$sql .= ' (`'.implode('`, `', $keys).'`) ';
		}
		$sql .= ' values '.Dba::makeValue($values, $keys);
		return $sql;
	}

	/**
	 *生成一条sql语句 replace into table (...) values (...)
	*/
	public static function makeReplace($table, $values, $keys = null) {
		$sql = 'replace into `'.$table.'`'; 
		if ($keys) {
			$sql .= ' (`'.implode('`, `', $keys).'`) ';
		}
		$sql .= ' values '.Dba::makeValue($values, $keys);

		return $sql;
	}

	/**
	 *生成一条sql语句 insert into table (...) values (...), (...), (...) 
	*/
	public static function makeInsertS($table, $values, $keys = null) {
		if (!$keys) {
			$keys = array_keys($values[0]);
		}
		return 'insert into `'.$table.'` (`'.implode('`, `', $keys).'`)  values '.Dba::makeValueS($values, $keys);
	}

	public static function insert($table, $insert) {
		return self::write(self::makeInsert($table, $insert, array_keys($insert)));
	}

	public static function insertS($table, $inserts) {
		return self::write(self::makeInsertS($table, $inserts));
	}

	public static function replace($table, $insert) {
		return self::write(self::makeReplace($table, $insert, array_keys($insert)));
	}

	public static function update($table, $update, $where) {
		return self::write(self::makeUpdate($table, $update, array_keys($update), $where));
	}

}

class AutoIsolation {
	function __construct() {
		Dba::setIsolationLevel(1);
	}

	function __destruct() {
		Dba::setIsolationLevel(3);
	}
}

class AutoTransaction{
	function __construct() {
		Dba::beginTransaction();
	}

	function __destruct() {
		Dba::commit();
	}
}

