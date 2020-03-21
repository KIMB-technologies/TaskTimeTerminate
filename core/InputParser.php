<?php
class InputParser {

	private const TIME_INPUT_PREG = '/^((\d+h)?(\d+m)?)|(\d+:\d+)$/';

	private const CATEGORY_INPUT_PREG = '/^[A-Za-z\-]+$/';

	private const NAME_INPUT_PREG = '/^[A-Za-z0-9\_\-]+$/';

	public static function checkTimeInput(string $t) : bool {
		return !empty($t) && preg_match( self::TIME_INPUT_PREG, $t) === 1;
	}

	public static function checkCategoryInput(string $c) : bool {
		return !empty($c) && preg_match( self::CATEGORY_INPUT_PREG, $c) === 1;
	}

	public static function checkNameInput(string $n) : bool {
		return !empty($n) && preg_match( self::NAME_INPUT_PREG, $n) === 1;
	}

}
?>