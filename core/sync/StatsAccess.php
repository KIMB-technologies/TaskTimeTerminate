<?php

abstract class StatsAccess {

	const FILENAME_PREG = '/^\d{4}-(0|1)\d-[0-3]\d\.json$/';
	const CLIENT_NAME_PREG = '/^[A-Za-z0-9\-]+$/';

	abstract public function listDayFiles() : array;

	abstract public function getDayFile( string $day ) : array;

	abstract public function addTask( array $task ) : void;
}

?>