<?php

  class StringCacher {
  
	private $cache = array();
	static private $instance = NULL;
    /**
     * Get the global StringCacher object instance.
     * @return StringCacher
     */
    public static function getInstance()
    {
        if(self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	public static function set($key, $value) {
		$klass = self::getInstance();
		$klass->cache[md5($key)] = $value;
		return $value;
	}
	
	public static function isCached($key) {
		$klass = self::getInstance();
		return isset($klass->cache[md5($key)]);
	}
	
	public static function cache_unset($key) {
		$klass = self::getInstance();
		unset($klass->cache[md5($key)]);
	}
	
	public static function fetch($key) {
		$klass = self::getInstance();
		return $klass->cache[md5($key)];
	}
	
	public static function clear() {
		$klass = self::getInstance();
		return $klass->cache = array();
	}
  
  }
?>