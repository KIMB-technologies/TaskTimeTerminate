#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

if( function_exists('socket_create') ){
	switch (Utilities::getOS()){
		case Utilities::OS_MAC:
			$socket = new AutocompleteSocket(AutocompleteSocket::SOCKET_FILE_MAC);
			break;
		case Utilities::OS_WIN:
			$socket = new AutocompleteSocket(AutocompleteSocket::getWinSocketFile());
			break;
		default:
			echo "ERROR: Unable to create Socket on this OS!" . PHP_EOL;
			break;
	}
}
else{
	echo "ERROR: Socket Library not loaded in this installation!" . PHP_EOL;
}
?>