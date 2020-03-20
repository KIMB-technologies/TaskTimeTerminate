<?php
class CLIParser {

	private array $args;

	public function __construct(int $argc, array $argv) {
		if( $argc > 0){
			$this->args = array_slice($argv, 1);
		}

		print_r($this->args);
	}
}
?>