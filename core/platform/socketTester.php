<?php

require_once(__DIR__ . '/../Utilities.php');

function getSocketPath(){
	return '/private/tmp/TaskTimeTerminateAutocomplete.sock';
	//return getenv('USERPROFILE') . '/AppData/Local/Temp/TaskTimeTerminateAutocomplete.sock';
}

function getCompletion($prefix){
	$completions = array();
	if(!empty($prefix) && function_exists('socket_create') ){
		$s = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		if( $s !== false && @socket_connect($s, getSocketPath()) ){
			$send = socket_write($s, $prefix . PHP_EOL, strlen($prefix . PHP_EOL));
			if( $send === strlen($prefix . PHP_EOL) ){
				$buffer = socket_read($s, 2048, PHP_NORMAL_READ);
				if( $buffer !== false ){
					$completions = explode(',', trim($buffer));
				}
			}
		}
		if(Utilities::isSocketType($s)){
			socket_close($s);
		}
	}
	return $completions;
}

while(true){
	$pref = readline('Type prefix (or exit): ');
	foreach(getCompletion($pref) as $p ){
		echo "\t $p" . PHP_EOL;
	}

	if($pref == "exit"){
		die('Bye :)');
	}
}
?>