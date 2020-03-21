#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

$recorder = new Recorder();

while( true ){
	if( Config::getStorageReader('config')->getValue(['status']) ){
		$recorder->record();
	}
	sleep(Config::getSleepTime());
}
?>