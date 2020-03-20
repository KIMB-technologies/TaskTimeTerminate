<?php
class CLIOutput {

	const BEGINEND = "============================================================";
	const MIDDLE =   "------------------------------------------------------------";
	const MOIN = "Welcome to TTT -- TaskTimeTerminate by KIMB-technologies ";

	const RED = "\e[0;31m";
	const BLACK = "\e[0;30m";
	const GREEN = "\e[0;32m";
	const YELLOW = "\e[0;33m";
	const BLUE = "\e[0;34m";
	const WHITE = "\e[0;37m";
	const RESET = "\e[0;0m";

	public static function colorString($s, $color) : string {
		return $color . $s . self::RESET;
	}

	public function __construct() {
		$this->hello();
	}

	public function hello(){
		$this->print(array(
			self::BEGINEND,
			self::MOIN,
			self::MIDDLE
		));
	}

	public function print( array $s, ?string $color = null, int $ind = 0 ) : void {
		foreach( $s as $data ){
			if( is_array( $data ) ){
				$this->print($data, $color, $ind+1);
			}
			else{
				$this->echo($data, $color, $ind);
			}
		}
	}

	public function __destruct(){
		$this->print([self::BEGINEND]);
	}

	private function echo( string $s, ?string $color = null, int $ind = 0) : void {
		echo str_repeat("\t", $ind) . ($color === null ? '' : $color ) . $s . ($color === null ? '' : self::RESET ) . PHP_EOL;
	}
}
?>