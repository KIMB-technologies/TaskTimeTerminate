#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

$recorder = new Recorder();

while( true ){
	if( Config::getRecordStatus() ) {
		$recorder->record();
	}
	ReaderManager::clearAll(); // write JSON to disk -- force
	sleep(Config::getSleepTime());
}
?>