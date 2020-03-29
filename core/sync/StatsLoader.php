<?php

class StatsLoader {

	private array $access = array();

	private array $filelist = array();
	private array $datasets = array();

	public function __construct(int $time, int  $forwardTo, bool $localOnly = false){
		$this->access['local'] = new LocalStatsAccess();
		if( !$localOnly ){
			$c = Config::getStorageReader('config');
			if( $c->isValue(['sync', 'directory']) ){
				$this->access['directory'] = new DirectoryStatsAccess(
					$c->getValue(['sync', 'directory', 'path']),
					$c->getValue(['sync', 'directory', 'thisname'])
				);
			}
			if( $c->isValue(['sync', 'server']) ){
				$this->access['server'] = new ServerStatsAccess(
					$c->getValue(['sync', 'server', 'uri']),
					$c->getValue(['sync', 'server', 'group']),
					$c->getValue(['sync', 'server', 'token']),
					$c->getValue(['sync', 'server', 'thisname'])
				);
			}
		}

		$this->selectUntil($time, $forwardTo);
	}

	private function selectUntil(int $time, int  $forwardTo) : void {
		foreach( $this->access as $type => $acc ){
			foreach( $acc->listDayFiles() as $f ){
				$timestamp = strtotime($f['day']);
				if( $timestamp !== false ){
					if( $timestamp >= $time && $timestamp <= $forwardTo){
						$this->filelist[] = array(
							'day' => $f['day'],
							'client' => $f['client'],
							'type' => $type
						);
					}
				}
			}
		}
	}

	public function getFilelist() : array {
		return $this->filelist;
	}

	public function getContents(bool $force = false) : array {
		if( $this->datasets === array() || $force ){
			$this->loadContents();
		}
		return $this->datasets;
	}

	
	private function loadContents(bool $force = false) : void {
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