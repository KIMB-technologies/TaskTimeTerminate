<?php

class ServerAccessCache {

	private const CACHE_DURATION = 600; // 10 minutes

	private JSONReader $cache;
	
	public function __construct(string $uri, string $group, string $token, string $name){
		$id = sha1($uri . $group . $token . $name);

		$this->cache = Config::getStorageReader('cache');

		if( !$this->cache->isValue(['id'], $id) ){
			// reset if new id/ server/ ...
			$this->cache->setArray(array(
				'id' => $id,
				'list' => array(),
				'file' => array()
			));
		}
	}

	/**
	 * Get a cached file list for given range
	 * Returns array with data or null, if uncached
	 */
	public function cachedFileList(int $timeMin, int $timeMax) : ?array{
		if(
			!$this->cache->isValue(['list','data']) || // empty
			$this->cache->isValue(['list','data'], array()) || // empty
			$this->cache->getValue(['list','time']) + self::CACHE_DURATION < time() || // outdated
			$this->cache->getValue(['list','min']) - self::CACHE_DURATION > $timeMin || // wrong range (too small)
			$this->cache->getValue(['list','max']) + self::CACHE_DURATION < $timeMax // wrong range (too small)
		){
			return null;
		}

		return $this->cache->getValue(['list','data']);
	}

	/**
	 * Save a filelist to cache
	 */
	public function setFileList(int $timeMin, int $timeMax, array $value) : void {
		$this->cache->setValue(['list','data'], $value);
		$this->cache->setValue(['list','time'], time());
		$this->cache->setValue(['list','min'], $timeMin);
		$this->cache->setValue(['list','max'], $timeMax);
	}

	/**
	 * Get a cached file given device and filename
	 * Returns array with data or null, if uncached
	 */
	public function cachedGetFile( string $file, string $device ) : ?array {
		if(
			!$this->cache->isValue(['file',$device]) || // empty
			!$this->cache->isValue(['file',$device,$file]) || // empty
			$this->cache->isValue(['file',$device,$file,'data'], array()) || // empty
			$this->cache->getValue(['file',$device,$file,'time']) + self::CACHE_DURATION < time() // outdated
		){
			return null;
		}

		return $this->cache->getValue(['file',$device,$file,'data']);
	}

	/**
	 * Get a cached file given device and filename
	 * Returns array with data or null, if uncached
	 */
	public function setGetFile( string $file, string $device, array $value ) : void {
		if(!$this->cache->isValue(['file',$device])){
			$this->cache->setValue(['file',$device], array());
		}
		$this->cache->setValue(['file',$device,$file], array(
			'data' => $value,
			'time' => time()
		));
	}
}

?>