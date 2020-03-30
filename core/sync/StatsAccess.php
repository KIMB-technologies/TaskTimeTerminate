<?php
abstract class StatsAccess {

	const FILENAME_PREG = '/^\d{4}-(0|1)\d-[0-3]\d\.json$/';

	/**
	 * return array(array(
	 * 	'timestamp' => '', // day of file at 00:00
	 * 	'file' => '', // filename to pass to getFile()
	 * 	'device' => '' // device to pass to getFile()
	 * ), ...)
	 * 
	 * Must not return stats of the client itself!
	 */
	abstract public function listFiles() : array;

	/**
	 * return array like JSON in 2020-02-12.json
	 */
	abstract public function getFile( string $file, string $device ) : array;

	abstract public function initialSync() : bool;

	abstract public function setDayTasks(array $tasks) : void;

	protected function filesToSyncInitially() : array {
		return array_values(array_filter(scandir( Config::getStorageDir() ), function ($f) {
			return preg_match(self::FILENAME_PREG, $f) === 1
				&& date('Y-m-d') !== substr($f, 0, -5);
		}));
	}
}
?>