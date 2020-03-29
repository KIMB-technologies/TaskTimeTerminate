<?php
class DirectoryStatsAccess extends StatsAccess {

	private string $directory;
	private string $thisClientName;

	public function __construct( string $directory, string $thisClientName ){
		$this->directory = ( substr($directory, -1) === '/' ? substr($directory, 0, -1) : $directory);
		$this->thisClientName = $thisClientName;
	}

	public function listDayFiles() : array {
		$files = array();
		foreach(array_diff(scandir($this->directory), ['.','..']) as $dir ){
			if( $dir !== $this->thisClientName && is_dir($this->directory . '/' . $dir) ){
				$files = array_merge(
					$files,
					array_map( function ($f) use (&$dir) {
							return array(
								'day' => substr($f, 0.),
								'client' => $dir,
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

	public function getDayFile( string $client, string $day ) : array {
		return json_decode(file_get_contents( $this->directory . '/' . $client . '/' . $day . '.json' ), true);
	}
}
?>