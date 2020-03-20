<?php

class LinuxDialog extends Dialog {
	
	public function open() : void {
		
		$this->chCategory = 0; /*key of $this->categories */
		$this->chName = "Test";
		$this->chTime = "1h";
	}

	public static function checkOSPackages() : void {
		exec('yad --version &> /dev/null', $null, $ret);
		if( $ret !== 1 ){
			die('"yad" is not installed!'. PHP_EOL . "\t" .' sudo apt-get install yad');
		}  
	}
}

?>