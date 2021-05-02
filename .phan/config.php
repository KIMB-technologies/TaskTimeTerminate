<?php
/*
	Run check via Docker-Installes Phan:

	# function defined in shell
	phan() {
		docker run -v $PWD:/mnt/src --rm -u "1000:1000" phanphp/phan:latest $@; return $?;
	} 

	# start in document root of project
	$ phan -o report.txt 
*/
return [
	'target_php_version' => '8.0',
	'directory_list' => [
		'core',
		'extensions',
	],
	'backward_compatibility_checks' => true,
	'exclude_file_list' => [
		'core/platform/windows/dialog.php',
		'core/platform/windows/socket.php'
	],
	'autoload_internal_extension_signatures' => [
		'zip' => '.phan/zip.phan_php',
		'sockets' => '.phan/sockets.phan_php'
	],
	'plugins' => [
		'AlwaysReturnPlugin',
		'DollarDollarPlugin',
		'DuplicateArrayKeyPlugin',
		'DuplicateExpressionPlugin',
		'PregRegexCheckerPlugin',
		'PrintfCheckerPlugin',
		'SleepCheckerPlugin',
		'UnreachableCodePlugin',
		'UseReturnValuePlugin',
		'EmptyStatementListPlugin',
		'LoopVariableReusePlugin',
	]
];