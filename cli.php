#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/load.php');

Config::init();
$parser = new CLIParser($argc, $argv);
$cli = new CLI($parser);
$cli->checkTask();
?>