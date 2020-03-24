<?php

class LinuxDialog extends Dialog {
	
	public function open() : void {
		$cmd = array(
			'yad',
			'--title="TaskTimeTerminate"',
			'--text-align=center',
			'--text="It is time for a new task!"',
			'--sticky',
			'--on-top',
			'--center',
			'--form',
			'--item-separator=,',
			'--separator=" "',
			'--field="Category:CB" "'. implode(',', $this->categories) .'"',
			'--field="Task:TEXT"',
			'--field="Time:TEXT"',
			'--field="Time can be a duration like 2h, 2h10m, 25m or time like 12:00.:LBL"',
			'--field="The time is seen as a limit, if reached this dialog will show up again.:LBL"',
			'--button="Start"',
			'--button="Pause"',
			'2> /dev/null'
		);

		$handle = popen(implode(' ', $cmd), 'r');
		$stdout = fgets($handle);
		pclose($handle);

		if( !empty($stdout) ){
			$stdout = explode(' ', trim($stdout));

			if( count($stdout) !== 3 ){
				$this->open();
				return;
			}

			// additional time?
			$timelist = array_values(array_filter($stdout, function ($v){
				return InputParser::checkTimeInput($v);
			}));
			if( count($timelist) === 1 && strpos($timelist[0], '+' ) !== false ){
				$this->chTime = $timelist[0];
			}
			else{
				$this->chCategory = in_array($stdout[0], $this->categories) ? array_search($stdout[0], $this->categories) : null; // category id
				$this->chName = InputParser::checkNameInput($stdout[1]) ? $stdout[1] : null;
				$this->chTime = InputParser::checkTimeInput($stdout[2]) ? $stdout[2] : null;
			}
			
			if( is_null($this->chCategory) || is_null( $this->chTime ) || is_null($this->chName)){
				$this->open();
				return;
			}
		}
		else {
			$this->shortBreak = true;
		}
	}

	public static function checkOSPackages() : void {
		exec('yad --version &> /dev/null', $null, $ret);
		if( $ret !== 1 ){
			die('"yad" is not installed!'. PHP_EOL . "\t" .' sudo apt-get install yad');
		}  
	}
}

?>