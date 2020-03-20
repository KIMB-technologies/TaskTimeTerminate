#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

$parser = new CLIParser($argc, $argv);
$cli = new CLI($parser);
$cli->checkTask();

$a = Config::getStorageReader('test');
print_r($a->getArray());

?>