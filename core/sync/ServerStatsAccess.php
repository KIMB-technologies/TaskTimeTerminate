<?php

class ServerStatsAccess extends StatsAccess {

	private string $uri;
	private string $groupId;
	private string $token;
	private string $thisClientName;
	private ServerAccessCache $cache;

	private bool $requestError = false;

	public function __construct(){
		$c = Config::getStorageReader('config');

		$this->uri = $c->getValue(['sync', 'server', 'uri']);
		$this->groupId = $c->getValue(['sync', 'server', 'group']);
		$this->token = $c->getValue(['sync', 'server', 'token']);
		$this->thisClientName = $c->getValue(['sync', 'server', 'thisname']);

		$this->cache = new ServerAccessCache( $this->uri, $this->groupId, $this->token, $this->thisClientName );
	}

	private function postToServer(string $endpoint, array $data = array() ) : array {
		$context = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-Type: application/x-www-form-urlencoded',
					'ignore_errors' => true,
					'content' => http_build_query(array(
						'group' => $this->groupId,
						'token' => $this->token,
						'client' => $this->thisClientName,
						'data' => json_encode($data)
					))
			));
		$append = substr($this->uri, -1) === '/' ? '' : '/';

		if( in_array($endpoint, ['add', 'list', 'get'])){
			$append .= 'api/' . $endpoint . '.php';
			$ret = file_get_contents( $this->uri . $append, false, stream_context_create($context));

			if( $ret !== false ){
				$json = json_decode( $ret, true);
				if( !is_null($json) && empty($json['error']) ){
					$this->requestError = false;
					return $json;
				}
				else{
					$msg = is_null($json) ? $ret : $json['error'];
					echo "ERROR: Returned message from server: '". $msg ."'" . PHP_EOL;
				}
			}
		}
		echo "ERROR: Request failed!" . PHP_EOL;
		$this->requestError = true;
		return array();
	}

	protected function listFilesUnfiltered(int $timeMin, int $timeMax) : array {
		$data = $this->cache->cachedFileList($timeMin, $timeMax);
		if( is_null($data) ){
			// Filtertering cause reduces server response size
			$data = $this->postToServer(
				'list',
				array(
					'timeMin' => $timeMin,
					'timeMax' => $timeMax
				));

			$this->cache->setFileList($timeMin, $timeMax, $data);
		}
		return $data;
	}

	protected function getFileUnfiltered( string $file, string $device ) : array {
		$data = $this->cache->cachedGetFile($file, $device);
		if( is_null($data) ){
			$data = $this->postToServer(
				'get',
				array(
					'file' => $file,
					'device' => $device
				));
			$this->cache->setGetFile($file, $device, $data);
		}
		return $data;
	}

	public function initialSync() : bool {
		$ok = true;
		foreach( $this->filesToSyncInitially() as $file ){
			$this->setDayTasks(json_decode(
					file_get_contents( Config::getStorageDir() . '/' . $file ),
					true
				),
				strtotime(substr($file, 0, -5))
			);
			$ok &= !$this->requestError;
			
		}
		return $ok;
	}

	public function setDayTasks(array $tasks, int $day) : void {
		$this->postToServer('add', array( 'day' => $day, 'tasks' => $tasks ) );
	}

}

?>