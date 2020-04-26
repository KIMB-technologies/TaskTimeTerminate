#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

new ExtensionLoader(); // load extensions

Recorder::runOnlyOnce(); // kills other recorder processes
$recorder = new Recorder();

while( true ){
	ExtensionEventHandler::daemonAfterSleep();
	if( Config::getRecordStatus() ) {
		$recorder->record();
	}
	ReaderManager::clearAll(); // write JSON to disk -- force
	ExtensionEventHandler::daemonBeforeSleep();
	sleep(Config::getSleepTime());
}
?>