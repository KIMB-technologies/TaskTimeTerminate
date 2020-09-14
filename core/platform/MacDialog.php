<?php

class MacDialog extends Dialog {
	
	const MAC_DIALOG = __DIR__ . '/macos/TaskTimeTerminate.app/Contents/MacOS/TaskTimeTerminate';
	
	public function open() : void {
		$handle = popen($this->createCommandLineArgs(self::MAC_DIALOG), 'r');
		$stdout = fgets($handle);
		pclose($handle);

		$this->handleStdoutJson(trim($stdout));
	}

	public static function checkOSPackages() : void {
	}
}

?>