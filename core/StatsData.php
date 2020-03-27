<?php
class StatsData {

	const FORWARD_TO_NOW = -1;

	private int $until = 0;
	private int $forward = -1;
	private array $filelist = array();
	private array $dataset = array();

	public function __construct(int $time = 0, int $forwardTo = self::FORWARD_TO_NOW) {
		$this->forward = ( $forwardTo  === self::FORWARD_TO_NOW ) ? time() : $forwardTo;
		$this->until = $time;
		$this->selectUntil();
	}

	private function selectUntil() : void {
		$datafiles = array_filter(scandir( Config::getStorageDir()), function ($f) {
			return preg_match('/^\d{4}-(0|1)\d-[0-3]\d\.json$/', $f) === 1;
		});
		foreach( $datafiles as $f ){
			$timestamp = strtotime(substr($f, 0, -5));
			if( $timestamp !== false ){
				if( $timestamp >= $this->until && $timestamp <= $this->forward){
					$this->filelist[] = substr($f, 0, -5);
				}
			}
		}
	}

	private function loadContents(bool $force = false) : void {
		if( empty($this->dataset) || $force ){
			foreach( $this->filelist as $f ){
				$r = Config::getStorageReader($f);
				$array = $r->getArray();
				foreach( $array as $key => $a ){
					if($a['end'] < $this->until ){
						unset($array[$key]);
					}
					else {
						$array[$key]['duration'] = $a['end'] - $a['begin'];
					}
				}
				if( !empty($array )){
					$this->dataset = array_merge($this->dataset, $array);
				}
			}
		}
	}

	public function filterData(array $names = array(), array $cats = array()){
		$this->loadContents();
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

	public static function getAllCategories() : array {
		$r = Config::getStorageReader('config');
		return $r->isValue(['categories']) ? $r->getValue(['categories']) : array();
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
}
?>