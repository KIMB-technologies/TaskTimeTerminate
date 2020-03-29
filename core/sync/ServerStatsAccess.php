<?php

class ServerStatsAccess extends StatsAccess {

	private string $uri;
	private string $groupId;
	private string $token;
	private string $thisClientName;

	public function __construct( string $uri, string $groupId, string $token, string $thisClientName ){
		$this->uri = $uri;
		$this->groupId = $groupId;
		$this->token = $token;
		$this->thisClientName = $thisClientName;
	}

	public function listDayFiles() : array {
		/**
		 * ToDo
		 */
		return array(array(
			'day' => '2020-03-29',
			'client' => 'xxx'
		));
	}

	public function getDayFile( string $client, string $day ) : array {
		/**
		 * ToDo
		 */
		return array();
	}

}

?>