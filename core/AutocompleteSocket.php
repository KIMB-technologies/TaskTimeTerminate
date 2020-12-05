<?php
class AutocompleteSocket {

	const SOCKET_FILE_MAC = '/private/tmp/TaskTimeTerminateAutocomplete.sock';
	const ACTIVATE_SOCKET = true;
	const CACHE_TIME = 3600;
	
	private string $socketpath;
	private $socket;
	private array $answerCache = array();
	private int $lastCached = 0;

	public function __construct(string $socketpath) {
		$this->socketpath = $socketpath;

		$this->cacheAnswers();
		$this->openSocket();
	}

	private function cacheAnswers() : void  {
		if( $this->lastCached + self::CACHE_TIME < time() ){
			$data = new StatsData();
			$names = array_values($data->getLocalNames());
			unset($data);

			$this->answerCache = array();
			foreach($names as $name){
				$this->answerCache[str_replace(['-', '_'], '', strtolower($name))] = $name;
			}

			$this->lastCached = time();
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
			CLIOutput::error( CLIOutput::ERROR_FATAL, "ERROR: Unable to bind socket to '" . $this->socketpath . "'!");
		}
			
	}

	public function __destruct(){
		if( Utilities::isSocketType($this->socket)){
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
		if(Utilities::isSocketType($s)){
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
			if( !Utilities::isSocketType($connection) ){
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
		$this->cacheAnswers();

		$prefix = str_replace(['-', '_'], '', strtolower($prefix));
		$prefLen = strlen($prefix);

		$answers = array();
		$sims = array();

		$count = 0;
		foreach($this->answerCache as $search => $cand ){
			$percent = 0;
			if( $prefLen > 2 ){
				similar_text( $prefix, $search, $percent );
				if( $percent > 30 ){
					$sims[$cand] = $percent;
				}
			}
			if( substr($search, 0, $prefLen) == $prefix || $percent > 70 ){
				$answers[] = $cand;

				$count++;
				if($count > 10){
					break;
				}
			}
		}
		if( $count < 10){
			arsort($sims, SORT_NUMERIC);
			$answers = array_unique(array_merge($answers, array_slice(array_keys($sims), 0, 10 - $count)));
		}
		return $answers;
	}

	public static function createSocketThread(){
		if( self::ACTIVATE_SOCKET ){
			if( Utilities::getOS() === Utilities::OS_WIN ){
				
				// locate php.exe
				$PHP_BIN = exec("where php.exe");
				if( !is_executable($PHP_BIN)){
					$PHP_BIN = "php.exe";
				}
				// make sure to free socket
				if( file_exists(self::getWinSocketFile()) ){
					unlink(self::getWinSocketFile());
				}
				
				//calc command
				$cmd = $PHP_BIN . ' "'.realpath(__DIR__) . '/../socket.php" > NUL';

				//open async background thread
				$descriptorspec = array(
					0 => array("pipe", "r"), // STDIN
					1 => array("pipe", "w"), // STDOUT
					2 => array("pipe", "w") // STDERR
				);
				$pipes = array();
				$sock = proc_open($cmd, $descriptorspec, $pipes);
			
				if (is_resource($sock)) {
					foreach($pipes as $pipe){
						stream_set_blocking($pipe, 0);
					}
				}
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
			CLIOutput::error( CLIOutput::ERROR_INFO, "Automatic Socket starting disabled!");
		}
	}

	public static function getWinSocketFile() : string {
		if( Utilities::getOS() === Utilities::OS_WIN ){
			return getenv('USERPROFILE') . '/AppData/Local/Temp/TaskTimeTerminateAutocomplete.sock';
		}
		else{
			CLIOutput::error( CLIOutput::ERROR_FATAL, "Used windows socket path function (AutocompleteSocket::getWinSocketFile) on non-windows OS!");
			return "";
		}
	}
}
?>