<?php

class LocalStatsAccess extends StatsAccess {

	public function listDayFiles() : array {
		return array_map(	function ($v) {
			return array(
					'day' => $v,
					'client' => ''
				);
			},
			array_filter(scandir( Config::getStorageDir()), function ($f) {
				return preg_match(parent::FILENAME_PREG, $f) === 1;
			})
		);
	}

	public function getDayFile( string $client, string $day  ) : array {
		return Config::getStorageReader($day)->getArray();
	}
}

?>