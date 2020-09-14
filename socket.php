#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

if( Utilities::getOS() === Utilities::OS_MAC){
	$socket = new AutocompleteSocket(AutocompleteSocket::SOCKET_FILE_MAC);
}
?>