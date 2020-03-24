<?php

class InTerminalDialog extends Dialog {

	/* usage in a for platform specific class:
	 * => add in open()
	 *	$d = new InTerminalDialog();
	 *	$d->setCategories($this->categories);
	 *	$d->open();
	 *	$this->chCategory = $d->getChosenCategory();	
	 *	$this->chName = $d->getChosenName();
	 *	$this->chTime = $d->getChosenTime();
	 */
	
	public function open() : void {
		echo PHP_EOL . "===================================" . PHP_EOL;
		echo "Its time for a new task!" . PHP_EOL;

		echo "-----------------------------------" . PHP_EOL;
		echo "Choose Category:" . PHP_EOL;
		foreach( $this->categories as $id => $name ){
			echo "\t" . $id . " : " . $name . PHP_EOL;
		}
		do {
			$cat = readline("Category ID: ");
		} while( !is_numeric($cat) || !isset($this->categories[$cat] ) );
		$this->chCategory = $cat;

		echo "-----------------------------------" . PHP_EOL;
		echo "Give a name for the task:" . PHP_EOL;
		do {
			$name = readline("Name: ");
		} while( !InputParser::checkNameInput($name) );
		$this->chName = $name;

		echo "-----------------------------------" . PHP_EOL;
		echo "Give a time limit for the task:" . PHP_EOL;
		do {
			$time = readline("Time: ");
		} while( !InputParser::checkTimeInput($time) || strpos($time, '+') !== false );
		$this->chTime = $time;

		echo "===================================" . PHP_EOL;
	}

	public static function checkOSPackages() : void {
	}
}

?>