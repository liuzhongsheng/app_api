<?php
class RedisDb{
	private static $instance;
	private $confing;
	
	private $redis;
	// 防止直接被实例化
	private function __construct($config){
		$this->config = $config;
		try {
			$this->redis = new Redis(); 
			$this->redis->connect($this->config->host, $this->config->port);
		}catch (Exception $e){
            echo $e->getMessage().'<br/>';
        }
	}
	
	// 防止对象被克隆
	private function _clone(){}
	
	public static function getInstance($config){
		// 如果$instance没有被实例化则进行实例化
		if(!self::$instance instanceof self){
			self::$instance = new self($config);
		}
		return self::$instance;
	}
	
	/**
	 * @param string $dbIndex 要切换的数据库编号
	 * @auth 李黑帅
	 * @return
	 **/
	public function select($dbIndex){
		return $this->redis->select($dbIndex);
	}
	
	/**
	 * @param string $key 需要设置的key
	 * @param string $value 对应的值
	 * @auth 李黑帅
	 * @return
	 **/
	public function setKey($key, $value){
		return $this->redis->set($key, $value);
	}
	
	public function getKey($key){
		return $this->redis->get($key);
	}
	
	public function delKey($key, $value){
		
	}
    
    // 设置过期时间
    // key 要设置过期时间的key
    // 超时时间
    public function expire($key,$time){
        return $this->redis->expire($key,$time);
    }
}