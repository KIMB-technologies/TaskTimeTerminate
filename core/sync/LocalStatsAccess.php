<?php

class LocalStatsAccess extends StatsAccess {

	protected function listFilesUnfiltered( int $timeMin, int $timeMax ) : array {
		// No filtertering for $timeMax, $timeMin cause only minimal speedup
		return array_map( function ($f) {
			return array(
					'timestamp' => strtotime(substr($f, 0, -5)),
	 				'file' => $f, 
	 				'device' => ''
				);
			},
			array_filter(scandir( Config::getStorageDir()), function ($f) {
				return preg_match(parent::FILENAME_PREG, $f) === 1;
			})
		);
	}

	protected function getFileUnfiltered( string $file, string $device ) : array {
		return Config::getStorageReader(substr($file, 0, -5))->getArray();
	}

	public function initialSync() : bool {
		return true; // never needed (since local data is the "real" data)
	}

	public function setDayTasks(array $tasks, int $day) : void {
		throw new Exception("StatsAccess::setDayTasks() will not work for LocalStatsAccess!");
	}
}

?>