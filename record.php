#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

Config::init();
$recorder = new Recorder();

if( !Config::getStorageReader('config')->isValue(['status']) ){
	Config::getStorageReader('config')->setValue(['status'], true);
}

while( true ){
	if( Config::getStorageReader('config')->getValue(['status']) ){
		$recorder->record();
	}
	sleep(Config::getSleepTime());
}
?>