<?php
class TimeParser {

	private const TIME_INPUT_PREG = '/^((\d+h)?(\d+m)?)|(\d+:\d+)$/';

	public static function checkTimeInput(string $t) : bool {
		return !empty($t) && preg_match( self::TIME_INPUT_PREG, $t) === 1;
	}

}
?>