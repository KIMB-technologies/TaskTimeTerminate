<?php

class MacDialog extends Dialog {
	
	const MAC_DIALOG = __DIR__ . '/macos/TaskTimeTerminate.app/Contents/MacOS/TaskTimeTerminate';
	
	public function open() : void {
		$cmd = array(
			self::MAC_DIALOG,
			'-cats',
			'"'. implode(',', $this->categories) .'"',
			'-lastcat',
			'"'.$this->categories[$this->chCategory ?? 0] .'"'
		);
		if( !empty($this->chName)){
			array_push($cmd,
				'-lasttask',
				'"'.$this->chName.'"'
			);
		}

		$handle = popen(implode(' ', $cmd), 'r');
		$stdout = fgets($handle);
		pclose($handle);

		if( !empty($stdout) ){
			$stdout = json_decode(trim($stdout), true);

			if( $stdout['pause'] ){
				$this->shortBreak = true;
			}
			else{
				if( strpos($stdout['time'], '+' ) === false ){ // not additional time for last task/ else values are set
					$this->chCategory = in_array($stdout['cat'], $this->categories) ? array_search($stdout['cat'], $this->categories) : null; // category id
					$this->chName = InputParser::checkNameInput($stdout['name']) ? $stdout['name'] : null;
				}
				$this->chTime = InputParser::checkTimeInput($stdout['time']) ? $stdout['time'] : null;

				if( is_null($this->chCategory) || is_null( $this->chTime ) || is_null($this->chName)){
					$this->open();
					return;
				}
			}
		}
		else { // error
			$this->open();
			return;
		}
	}

	public static function checkOSPackages() : void {
	}
}

?>