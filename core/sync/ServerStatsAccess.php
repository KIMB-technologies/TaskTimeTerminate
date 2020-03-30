<?php

class ServerStatsAccess extends StatsAccess {

	private string $uri;
	private string $groupId;
	private string $token;
	private string $thisClientName;

	public function __construct(){
		$c = Config::getStorageReader('config');
		$this->uri = $c->getValue(['sync', 'server', 'uri']);
		$this->groupId = $c->getValue(['sync', 'server', 'group']);
		$this->token = $c->getValue(['sync', 'server', 'token']);
		$this->thisClientName = $c->getValue(['sync', 'server', 'thisname']);
	}

	public function listFiles() : array {
		/**
		 * ToDo
		 */
		return array();
	}

	public function getFile( string $file, string $device ) : array {
		/**
		 * ToDo
		 */
		return array();
	}

	public function initialSync() : bool {
		/**
		 * ToDo
		 */
		return false;
	}

	public function setDayTasks(array $tasks) : void {
		/**
		 * ToDo
		 */
	}

}

?>