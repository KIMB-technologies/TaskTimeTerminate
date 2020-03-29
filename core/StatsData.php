<?php
class StatsData {

	const FORWARD_TO_NOW = -1;
	const DATE_PREG = '/^\d{4}-(0|1)\d-[0-3]\d$/';

	private StatsLoader $loader;
	private array $dataset;

	public function __construct(int $time = 0, int $forwardTo = self::FORWARD_TO_NOW) {
		$this->loader = new StatsLoader(
			$time,
			( $forwardTo  === self::FORWARD_TO_NOW ) ? time() : $forwardTo
		);
	}

	private function loadContents(bool $force = false, $localOnly = false) : void {
		$this->dataset = $this->loader->loadContents( $force, $localOnly );
	}

	public function filterData(array $names = array(), array $cats = array(), $localOnly = false){
		$this->loadContents(false, $localOnly);
		foreach( $this->dataset as $k => $d ){
			if(
				(!empty($cats) && !in_array($d['category'], $cats))
				||
				(!empty($names) && !in_array($d['name'], $names))
			){
				unset( $this->dataset[$k]);
			}
		}
	}
	
	public function getAllNames() : array {
		$this->loadContents();

		return array_unique(array_column($this->dataset, 'name'));
	}

	public function getAllDatasets() : array {
		return $this->dataset;
	}
	
	public function merge($merge, $mergeTo) : bool {
		if( $merge === $mergeTo){
			return true;
		}

		$ret = true;
		foreach( $this->filelist as $f ){
			$r = Config::getStorageReader($f);
			foreach( $r->getArray() as $k => $a ){
				if( $a['name'] === $merge ){
					$ret &= $r->setValue([$k, 'name'], $mergeTo);
				}
			}
			unset($r);
		}

		$this->loadContents(true); //force reload
		return $ret;
	}

	public static function getAllCategories() : array {
		$r = Config::getStorageReader('config');
		return $r->isValue(['categories']) ? $r->getValue(['categories']) : array();
	}
}
?>