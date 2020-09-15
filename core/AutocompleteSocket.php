<?php
class AutocompleteSocket {

	const SOCKET_FILE_MAC = '/private/tmp/TaskTimeTerminateAutocomplete.sock';
	const ACTIVATE_SOCKET = false;
	
	private string $socketpath;
	private $socket;
	private array $answerCache = array();

	public function __construct(string $socketpath) {
		$this->socketpath = $socketpath;

		$this->cacheAnswers();
		$this->openSocket();
	}

	private function cacheAnswers() : void  {
		$data = new StatsData();
		$names = array_values($data->getLocalNames());
		unset($data);

		$this->answerCache = array();
		foreach($names as $name){
			$this->answerCache[str_replace(['-', '_'], '', strtolower($name))] = $name;
		}
	}

	private function openSocket(){
		if( file_exists($this->socketpath) ){
			$this->closeOtherSocket();
		}
		// create and bind
		$this->socket = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		if( $this->socket !== false && socket_bind( $this->socket, $this->socketpath ) ){
			if( socket_listen( $this->socket, 5 ) ){
				while( true ){
					// wait for connection and handle it
					$this->connectionHandler( socket_accept(  $this->socket ) );
				}
			}
		}
		else {
			echo "ERROR: Unable to bind socket to '" . $this->socketpath . "'!" . PHP_EOL;
		}
			
	}

	public function __destruct(){
		if( is_resource($this->socket)){
			socket_close($this->socket);
		}
		if( file_exists($this->socketpath)){
			unlink($this->socketpath);
		}
	}

	private function closeOtherSocket(){
		$s = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		if( $s !== false && @socket_connect($s, $this->socketpath) ){
			$msg = ">__SELFKILL__<" . PHP_EOL;

			socket_write($s, $msg, strlen($msg));
		}
		if(is_resource($s)){
			socket_close($s);
		}

		sleep(2); // give time to other socket to kill itself
		if( file_exists($this->socketpath)){
			unlink($this->socketpath);
		}
	}

	private function connectionHandler( $connection ){
		$buffer = "";
		do{
			if( !is_resource($connection) ){
				return;
			}
			$buffer = @socket_read($connection, 256, PHP_NORMAL_READ);

			if( !empty($buffer) ){
				$buffer = trim($buffer);
				if(InputParser::checkNameInput($buffer)){
					$answer = implode(',', $this->getCompletes($buffer)) . PHP_EOL;
					socket_write( $connection, $answer );
				}
				else{
					if( $buffer === ">__SELFKILL__<" ){
						$this->__destruct();
						die();
					}
					else {
						socket_write( $connection, ( empty($buffer) ? "" : "ERROR: Invalid prefix!" ) . PHP_EOL );
					}
				}
			}
		} while(!empty($buffer));
		socket_close( $connection );
	}

	private function getCompletes( string $prefix ) : array {
		$prefix = str_replace(['-', '_'], '', strtolower($prefix));
		$prefLen = strlen($prefix);

		$answers = array();
		$count = 0;
		foreach($this->answerCache as $search => $cand ){
			if(substr($search, 0, $prefLen) == $prefix ){
				$answers[] = $cand;

				$count++;
				if($count > 10){
					break;
				}
			}
		}
		return $answers;
	}

	public static function createSocketThread(){
		if( self::ACTIVATE_SOCKET ){
			if( Utilities::getOS() === Utilities::OS_WIN ){
				$PHP_BIN = exec("where php.exe");
				if( !is_executable($PHP_BIN)){
					$PHP_BIN = "php.exe";
				}
				if( file_exists(self::getWinSocketFile()) ){
					unlink(self::getWinSocketFile());
				}
				echo $PHP_BIN . ' "'.realpath(__DIR__) . '/../socket.php" > NUL';
				echo system($PHP_BIN . ' "'.realpath(__DIR__) . '/../socket.php" > NUL &', $ret );
				var_dump($ret);
			}
			else if (Utilities::getOS() === Utilities::OS_MAC ){
				$cmd = array(
					'"'. PHP_BINDIR. '/php"',
					'"'.realpath(__DIR__ . '/../socket.php' ) .'"',
					'> /dev/null &'
				);

				exec(implode(' ', $cmd));
			}
		} 
		else {
			echo "INFO: Automatic Socket starting disabled!" . PHP_EOL;
		}
	}

	public static function getWinSocketFile() : string {
		if( Utilities::getOS() === Utilities::OS_WIN ){
			return getenv('USERPROFILE') . '/AppData/Local/Temp/TaskTimeTerminateAutocomplete.sock';
		}
		else{
			echo "ERROR: Used windows socket path function (AutocompleteSocket::getWinSocketFile) on non-windows OS!" . PHP_EOL;
			return "";
		}
	}
}
?>