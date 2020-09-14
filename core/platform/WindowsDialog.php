<?php

class WindowsDialog extends Dialog {

	const PHP_GTK_URL = "http://gtk.php.net/distributions/PHP55-GTK2.zip";
	const PHP_GTK_SHA1 = "7a9436f5e768ee20364e7cbc1210b798742a97dc";

	const PHP_GTK_TEST = __DIR__ . '/php-gtk/PHP55-GTK2/php.exe -v';
	const PHP_GTK_DIALOG = __DIR__ . '/php-gtk/PHP55-GTK2/php.exe "'.__DIR__.'/windows/dialog.php"';
	
	public function open() : void {
		exec($this->createCommandLineArgs(self::PHP_GTK_DIALOG), $stdout);

		if( !empty($stdout) ){
			$this->handleStdoutJson($stdout[0]);
		}
		else { // error
			$this->open();
			return;
		}
	}

	public static function checkOSPackages() : void {
		if( !is_dir(__DIR__ . '/php-gtk/') ){
			if( mkdir(__DIR__ . '/php-gtk/', 0740) &&
				file_put_contents(__DIR__ . '/d.zip', file_get_contents(self::PHP_GTK_URL)) &&
				sha1_file(__DIR__ . '/d.zip') === self::PHP_GTK_SHA1
			){
				$zip = new ZipArchive();
				if ( $zip->open(__DIR__ . '/d.zip') === true) {
					$zip->extractTo(__DIR__ . '/php-gtk/');
					$zip->close();

					unlink(__DIR__ . '/d.zip');

					exec(self::PHP_GTK_TEST, $output);
					if( empty($output) || strpos($output[0], 'PHP 5.5.10') === false){
						die( PHP_EOL . 'Seems like PHP-GTK has errors :( !!' . PHP_EOL . PHP_EOL);
					}
				}
				else{
					unlink(__DIR__ . '/d.zip');
					die( PHP_EOL . 'Error unzipping PHP-GTK!!' . PHP_EOL . PHP_EOL);
				}
			}
			else{
				die( PHP_EOL . 'Error downloading PHP-GTK!!' . PHP_EOL . PHP_EOL);
			}
		}
	}
}

?>