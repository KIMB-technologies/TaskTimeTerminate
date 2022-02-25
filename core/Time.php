<?php
class Time {

	private const DEFAULT_WORK_DAY_HOURS = 8; // default work day length
	private const DEFAULT_FORMAT = 'dhm|s';
	private const ALLOWED_PARTS = array(
			'y' => 365*24*60*60, // year
			't' => 30*24*60*60, // month (one of twelve)
			'w' => 7*24*60*60, // week
			'd' => 24*60*60, // day
			'a' => 60*60, // work-day 
			'h' => 60*60, // hour
			'm' => 60, // minute
			's' => 1 // second
		);
	private const DELIMITER = '|';
	private const PAD_VALUE = 4;

	private string $timeformat = "";
	private array $timeParts = self::ALLOWED_PARTS;

	private static ?Time $time = null;
	
	public function __construct() {
		// write to config
		$r = Config::getStorageReader('config');
		if( !$r->isValue(['timeformat']) ){
			$r->setValue(['timeformat'], self::DEFAULT_FORMAT );
		}
		$this->timeformat = $r->getValue(['timeformat']);
		if( !$r->isValue(['work_day_hours']) ){
			$r->setValue(['work_day_hours'], self::DEFAULT_WORK_DAY_HOURS );
		}
		$this->timeParts['a'] *= is_numeric($r->getValue(['work_day_hours'])) ? $r->getValue(['work_day_hours']) : self::DEFAULT_WORK_DAY_HOURS;

		// check syntax
		$ok = true;
		foreach(str_split($this->timeformat) as $char){
			if( !in_array($char, array_keys($this->timeParts), true) && $char !== self::DELIMITER ){
				$ok = false;
			}
		}
		$ok = $ok &&
			$this->timeformat[0] !== self::DELIMITER && 
			substr($this->timeformat, -1) !== self::DELIMITER &&
			str_replace('||', '', $this->timeformat) === $this->timeformat;

		if(!$ok){
			$this->timeformat = self::DEFAULT_FORMAT;
			CLIOutput::error( CLIOutput::ERROR_WARN, "Invalid timeformat in config.json" );
		}

		// calculate pad 
		$this->padResult = self::PAD_VALUE * max(array_map( 'strlen', explode(self::DELIMITER, $this->timeformat)));
	}

	private function formatTime(int $time, string $format) : array {
		$d = array();
		$notEmpty = false;
		foreach( str_split($format) as $k => $f ){
			$c = intval($time / $this->timeParts[$f]);
			if( $c > 0 || $notEmpty){
				$d[$f] = $c; 
				$time = intval($time % $this->timeParts[$f]);
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

	public static function isSummertime(int $now = null) : bool {
		return date('I', is_null($now) ? time() : $now) === '1';
	}

	public static function getTimeChangeDifference(int $now = null) : int {
		$now = is_null($now) ? time() : $now;
		$todayNight = self::isSummertime(strtotime("today 00:00", $now));
		$todayMid = self::isSummertime(strtotime("today 12:00", $now));
		if( $todayMid === $todayNight || intval(date('G', $now)) < 3 ){
			return 0;
		}
		else {
			if( !$todayNight && $todayMid ){
				return -1;
			}
			if( $todayNight && !$todayMid ){
				return +1;
			}
			else{
				return 0;
			}
		}
	}
}
?>