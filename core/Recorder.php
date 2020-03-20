<?php
class Recorder {

	public Dialog $dialog;

	public function __construct() {
		$os = php_uname('s');
		if( stripos($os, 'darwin') !== false ){
			$this->dialog = new MacDialog();
		}
		else if( stripos($os, 'linux') !== false ){
			$this->dialog = new LinuxDialog();
		}
		else{
			die('Plattform not supported!!');
		}
	}

	public function record() {
		echo "Record" . PHP_EOL;
		$this->dialog->open();
	}
}
?>