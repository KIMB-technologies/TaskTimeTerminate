<?php
class Time {

	private const DEFAULT_FORMAT = 'dhm|s';
	private const ALLOWED_PARTS = array(
			'd' => 24*60*60,
			'h' => 60*60,
			'm' => 60, 
			's' => 1
		);
	private const DELIMITER = '|';
	private const PAD_VALUE = 4;

	private static ?Time $time = null;
	private string $timeformat = "";
	
	public function __construct() {
		// write to config
		$r = Config::getStorageReader('config');
		if( !$r->isValue(['timeformat']) ){
			$r->setValue(['timeformat'], self::DEFAULT_FORMAT );
		}
		$this->timeformat = $r->getValue(['timeformat']);

		// check syntax
		$ok = true;
		foreach(str_split($this->timeformat) as $char){
			if( !in_array($char, array_keys(self::ALLOWED_PARTS), true) && $char !== self::DELIMITER ){
				$ok = false;
			}
		}
		$ok = $ok &&
			$this->timeformat[0] !== self::DELIMITER && 
			substr($this->timeformat, -1) !== self::DELIMITER &&
			str_replace('||', '', $this->timeformat) === $this->timeformat;

		if(!$ok){
			$this->timeformat =  self::DEFAULT_FORMAT;
			echo "ERROR: Invalid timeformat in config.json" . PHP_EOL;
		}

		// calculate pad 
		$this->padResult = self::PAD_VALUE * max(array_map( 'strlen', explode(self::DELIMITER, $this->timeformat)));
	}

	private function formatTime(int $time, string $format) : array {
		$d = array();
		$notEmpty = false;
		foreach( str_split($format) as $k => $f ){
			$c = intval($time / self::ALLOWED_PARTS[$f]);
			if( $c > 0 || $notEmpty){
				$d[$f] = $c; 
				$time = intval($time % self::ALLOWED_PARTS[$f]);
				$notEmpty = true;
			}
		}
		return $d;
	}

	private function formatAsString(array $duration){
		$s = "";
		foreach( $duration as $name => $value){
			$s .= str_pad( " " . $value . $name, self::PAD_VALUE, " ", STR_PAD_LEFT);
		}
		return str_pad( $s, $this->padResult, " ", STR_PAD_LEFT);
	}

	private function getDurationString(int $t) : string {
		foreach(explode(self::DELIMITER, $this->timeformat) as $format){
			$v = $this->formatTime($t, $format);
			//print_r($v);
			if( array_sum($v) > 0 ){
				return $this->formatAsString($v);
			}
		}
		return str_pad( "0", $this->padResult, " ", STR_PAD_LEFT);
	}

	public static function secToTime(int $t) : string {
		if(self::$time === null){
			self::$time = new Time();
		}
		return self::$time->getDurationString($t);
	}
}
?>