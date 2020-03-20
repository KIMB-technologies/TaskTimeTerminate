<?php
class Recorder {

	public Dialog $dialog;

	public function __construct() {
		$os = php_uname('s');
		if( stripos($os, 'darwin') !== false ){
			MacDialog::checkOSPackages();
			$this->dialog = new MacDialog();
		}
		else if( stripos($os, 'linux') !== false ){
			MacDialog::checkOSPackages();
			$this->dialog = new LinuxDialog();
		}
		else{
			die('Plattform not supported!!');
		}
	}

	public function record() {
		
		$this->dialog->setCategories(array(1 => 'a', 2 => 'b'));
		$this->dialog->open();
	}
}
?>