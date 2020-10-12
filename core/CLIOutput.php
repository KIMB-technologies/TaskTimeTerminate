<?php
class CLIOutput {

	const BEGINEND = "=";
	const MIDDLE =   "-";
	const MOIN = "Welcome to TTT -- TaskTimeTerminate by KIMB-technologies ";

	const PAD_SPACING = 1;

	const RED = "\e[0;31m";
	const BLACK = "\e[0;30m";
	const GREEN = "\e[0;32m";
	const YELLOW = "\e[0;33m";
	const BLUE = "\e[0;34m";
	const WHITE = "\e[0;37m";
	const RESET = "\e[0;0m";

	const ERROR_WARN = 1;
	const ERROR_INFO = 2;
	const ERROR_FATAL = 3;

	private static int $readlineCount = 0;
	private static array $readlineUsed = array();

	public static function colorString(string $s, string $color) : string {
		return $color . $s . self::RESET;
	}

	public static function error(int $type, string $msg) : void {
		$p = "";
		switch ($type){
			case self::ERROR_WARN:
				$p = self::colorString("WARN", self::YELLOW);
				break;
			case self::ERROR_INFO:
				$p = self::colorString("INFO", self::BLUE);
				break;
			case self::ERROR_FATAL:
				$p = self::colorString("ERROR", self::RED);
				break;
		}
		echo $p . ': "' . $msg . '"' . PHP_EOL;
	}

	public function __construct() {
		$this->hello();
	}

	public function hello(){
		$cols = Utilities::getTerminalColumns();
		$this->print(array(
			str_repeat(self::BEGINEND, $cols),
			self::MOIN,
			str_repeat(self::MIDDLE, $cols)
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

	public function table(array $data) : void {
		if( empty($data)){
			return;
		}
		$colsize = array();
		foreach( $data as $row ){
			foreach( $row as $cid => $col ){
				if( !isset($colsize[$cid])){
					$colsize[$cid] = strlen($cid);
				}
				$colsize[$cid] = max( $colsize[$cid], strlen($col) );
			}
		}

		echo PHP_EOL . str_repeat('-', array_sum($colsize) + count($colsize) * (2*self::PAD_SPACING+1) + 1) . PHP_EOL;
		$firstrow = true;
		$lastfirstcell = '';
		foreach( $data as $row ){
			if($firstrow){
				echo '|' . str_repeat(' ', self::PAD_SPACING);
				foreach( $row as $cid => $col ){
					echo self::BLUE . str_pad($cid, $colsize[$cid] + self::PAD_SPACING ) . self::RESET . '|' . str_repeat(' ', self::PAD_SPACING);
				}
				echo PHP_EOL;
				echo str_repeat('-', array_sum($colsize) + count($colsize) * (2*self::PAD_SPACING+1) + 1) . PHP_EOL;
				$firstrow = false;
			}
			echo '|' . str_repeat(' ', self::PAD_SPACING);
			$firstcell = true;
			foreach( $row as $cid => $col ){
				if($firstcell){
					if( $lastfirstcell == $col  ){
						$col = '';
					}
					else {
						$lastfirstcell = $col;
					}
					$firstcell = false;
				}
				echo str_pad($col, $colsize[$cid] + self::PAD_SPACING ) . '|' . str_repeat(' ', self::PAD_SPACING);
			}
			echo PHP_EOL;
		}
		echo str_repeat('-', array_sum($colsize) + count($colsize) * (2*self::PAD_SPACING+1) + 1) . PHP_EOL . PHP_EOL;
	}

	public function __destruct(){
		$this->print(array(
			str_repeat(self::BEGINEND, Utilities::getTerminalColumns())
		));
	}

	private function echo( string $s, ?string $color = null, int $ind = 0) : void {
		echo str_repeat("\t", $ind) . ($color === null ? '' : $color ) . $s . ($color === null ? '' : self::RESET ) . PHP_EOL;
	}

	public function readline(string $question, ?string $color = null, int $ind = 0) : string {
		if(Utilities::getOS() === Utilities::OS_TELEGRAM){
			self::$readlineCount++;
			if(self::$readlineCount > 50){
				die("Readline Count Error!");
			}
			
			$r = Config::getStorageReader('telegram');
			if($r->isValue(['readline', $question])){
				$val = $r->getValue(['readline', $question]);
				if(is_array($val)){
					if(!isset( self::$readlineUsed[$question] ) ){
						self::$readlineUsed[$question] = 0;
					}
					if(isset($val[self::$readlineUsed[$question]])){
						$val = self::$readlineUsed[$question];
					}
					self::$readlineUsed[$question]++;
				}
				return !is_string($val) ? "" : $val;
			}
			else{
				
				file_put_contents(Config::getStorageDir() . '/notFoundReadlineKeys.log', json_encode($question) . PHP_EOL, FILE_APPEND); 
				return "";
			}
		}
		else{
			echo str_repeat("\t", $ind) . ($color === null ? '' : $color );
			$r = readline($question . ' ');
			echo self::RESET;
			return $r;
		}
	}
}
?>