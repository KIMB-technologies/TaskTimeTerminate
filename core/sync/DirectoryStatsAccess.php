<?php
class DirectoryStatsAccess extends StatsAccess {

	private string $directory;
	private string $thisClientName;

	public function __construct(){
		$c = Config::getStorageReader('config');

		$dir = $c->getValue(['sync', 'directory', 'path']);
		$this->directory = ( substr($dir, -1) === '/' ? substr($dir, 0, -1) : $dir);
		
		$this->thisClientName = $c->getValue(['sync', 'directory', 'thisname']);
	}

	protected function listFilesUnfiltered( int $timeMin, int $timeMax ) : array {
		// No filtertering for $timeMax, $timeMin cause only minimal speedup
		$files = array();
		foreach(array_diff(scandir($this->directory), ['.','..']) as $dir ){
			if( $dir !== $this->thisClientName && is_dir($this->directory . '/' . $dir) ){
				$files = array_merge(
					$files,
					array_map( function ($f) use (&$dir) {
							return array(
								'timestamp' => strtotime(substr($f, 0, -5)),
	 							'file' => $f, 
	 							'device' => $dir
							);
						},
						array_filter(
							scandir( $this->directory . '/' . $dir ),
							function ($f) {
								return preg_match(parent::FILENAME_PREG, $f) === 1;
							}
						)
					)
				);
			}
		}
		return $files;
	}

	protected function getFileUnfiltered( string $file, string $device ) : array {
		return json_decode(file_get_contents( $this->directory . '/' . $device . '/' . $file ), true);
	}

	public function initialSync() : bool {
		if( !is_dir( $this->directory . '/' . $this->thisClientName ) ){
			if(!mkdir( $this->directory . '/' . $this->thisClientName , 0740, true)){
				return false;
			}
		}
		
		$ok = true;
		foreach( $this->filesToSyncInitially() as $file ){
			$ok &= copy(
				Config::getStorageDir() . '/' . $file,
				$this->directory . '/' . $this->thisClientName . '/' . $file
			);
		}
		return $ok;
	}

	public function setDayTasks(array $tasks, int $day) : void {
		$file = $this->directory . '/' . $this->thisClientName . '/' . date( 'Y-m-d', $day ) . '.json';
		if( !empty($tasks) ){
			file_put_contents(
				$file,
				json_encode( $tasks, JSON_PRETTY_PRINT )
			);
		}
		else if( is_file($file) ){
			unlink($file);
		}
	}
}
?>