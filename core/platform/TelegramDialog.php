<?php

class TelegramDialog extends Dialog {
	
	public function open() : void {
		$r = Config::getStorageReader('telegram');

		if($r->isValue(['dialog'])){
			$time = $r->getValue(['dialog', 'time']);
			$cat = $r->getValue(['dialog', 'category']);
			$task = $r->getValue(['dialog', 'task']);

			$this->chCategory = in_array($cat, $this->categories) ? array_search($cat, $this->categories) : null; // category id
			$this->chName = InputParser::checkNameInput($task) ? $task : null;
			$this->chTime = InputParser::checkTimeInput($time) ? $time : null;
			
			if( is_null($this->chCategory) || is_null( $this->chTime ) || is_null($this->chName)){
				$this->shortBreak = true;
				echo "Invalid values given!" . PHP_EOL;
			}
		}
		else{
			$this->shortBreak = true;
			echo "No values for task given!!" . PHP_EOL;
		}
	}

	public static function checkOSPackages() : void {
	}
}

?>