<?php
class Config {

	private const DEFAULT_CONF = '{"sleep":60,"savedir":"~/.tasktimeterminate"}';
	private const DEFAULT_SLEEP = 60;
	private const DEFAULT_SAVEDIR = '~/.tasktimeterminate';

	private static ?Config $instance = null;

	private JSONReader $json;
	private int $sleeptime;
	private string $savedir;

	public function __construct() {
		// create default config.json, if non exists
		if( !is_file( __DIR__ . '/../config.json' ) ){
			file_put_contents(__DIR__ . '/../config.json', self::DEFAULT_CONF);
		}
		// load config json
		$this->json = new JSONReader( 'config', false, __DIR__ . '/../');

		// check for storage dir
		if( !$this->json->isValue(['savedir'])){
			$this->json->setValue(['savedir'], self::DEFAULT_SAVEDIR);
		}
		$this->savedir = self::parseUnixPath($this->json->getValue(['savedir']));
		if( !is_dir($this->savedir) ){
			if( !mkdir($this->savedir, 0740 , true) ){
				die('Unable to create storage directory at "'. $this->savedir .'"');
			}
		}
		if( !is_writable($this->savedir) ){
			die('Storage directory at "'. $this->savedir .'" is not writeable');
		}

		// load sleeptime
		$this->sleeptime = $this->json->getValue(['sleep']);
		if( !is_numeric($this->sleeptime) ){
			$this->sleeptime = self::DEFAULT_SLEEP;
		}
	}

	private static function init(){
		if(self::$instance == null){
			self::$instance = new Config();
		}
	}

	public static function getSleepTime() : int {
		self::init();

		return self::$instance->sleeptime;
	}

	public static function getStorageReader(string $name) : JSONReader {
		self::init();

		return new JSONReader($name, false, self::$instance->savedir);
	}

	private static function parseUnixPath(string $path) : string {
		$path = ltrim($path);
		if( $path[0] === '~' ){ // home shortcut
			$home = posix_getpwuid(posix_getuid())['dir'];
			return $home . '/' . substr($path, 1);
		}
		else if( $path[0] !== '/' ){ // relative path (as relative to project root)
			return __DIR__ . '/../' . $path;
		}
		else { // absolute path
			return $path;
		}
	}
}
?>