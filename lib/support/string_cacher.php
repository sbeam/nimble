<?php
	/**
	* @package Support
	* Cacheing for single requests
	* @todo add apc cache calls if apc is enabled
	*/
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
		/**
		* Stores a value in the cache
		* @param string $key Cache key 
		* @param mixed $value Value you wish to store
		* @return mixed
		*/
		public static function set($key, $value) {
			$klass = self::getInstance();
			$klass->cache[crc32($key)] = $value;
			return $value;
		}
		/**
		* Check to see if a key is set
		* @param string $key Cache key
		* @return boolean
		*/
		public static function isCached($key) {
			$klass = self::getInstance();
			return isset($klass->cache[crc32($key)]);
		}
		/**
		* Unsets the value for a giving key
		* @param string Cache key
		*/
		public static function cache_unset($key) {
			$klass = self::getInstance();
			unset($klass->cache[crc32($key)]);
		}
		/** 
		* Fetches a value from the cache givin the key
		* @param string $key Cache key
		* @return mixed
		*/
		public static function fetch($key) {
			$klass = self::getInstance();
			return $klass->cache[crc32($key)];
		}
		/**
		* Clears the current cache
		* @warning this will wipe all data stored in the cache
		* @return boolean
		*/
		public static function clear() {
			$klass = self::getInstance();
			$klass->cache = array();
			return true;
		}
  
	}
?>
