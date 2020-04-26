<?php
class ExtensionLoader {

	const EXT_DIR = __DIR__ . '/../extensions/';
	const EXT_NAME_PREG = '/^[A-Za-z0-9\_\-]{1,30}$/';

	private $list = array();
	private $shortnames = array();

	public function __construct() {
		$this->loadList();
	}

	private function loadList(){
		// load ext folder
		$extFolders = array_filter(
			array_diff(
				scandir(self::EXT_DIR),
				['..','.']
			),
			fn ($e) => preg_match(self::EXT_NAME_PREG, $e) === 1
		);
		// load disabled exts
		if( is_file(self::EXT_DIR . '/disabled.txt')){
			$disabled = array_filter(
				array_map(
					'trim',
					file(self::EXT_DIR . '/disabled.txt')
				),
				fn ($e) => preg_match(self::EXT_NAME_PREG, $e) === 1
			);
		}
		else{
			$disabled = array();
		}

		// load each ext
		foreach( $extFolders as $ext ){
			if( !in_array( $ext, $disabled ) &&
				is_file( self::EXT_DIR . $ext . '/index.json' ) && is_file( self::EXT_DIR . $ext . '/index.php' ) ){
				$json = json_decode( file_get_contents( self::EXT_DIR . $ext . '/index.json' ), true);
				if( !empty($json) ){
					$this->list[$ext] = array(
						'name' => $json['name'],
						'version' => $json['version'],
						'cli' => empty($json['cli']) ? false : $json['cli']
					);
					ExtensionEventHandler::loadExtension( $ext, $json['events'], self::EXT_DIR . $ext . '/index.php' );
					if( !empty($json['shortname'])){
						$this->shortnames[$json['shortname']] = $ext;
					}
				}
			}
		}
	}

	public function callCLI( string $ext, CLIParser $parser, CLIOutput $output) : bool {
		if( !empty( $this->list[$ext] ) &&  $this->list[$ext]['cli'] !== false ){
			ExtensionEventHandler::cli( $ext, $parser, $output, $this->list[$ext]['cli'] );
			return true;
		}
		else{
			return false;
		}
	}

	public function callShortname( string $shortname, CLIParser $parser, CLIOutput $output) : bool {
		if( !empty( $this->shortnames[$shortname] ) ){
			return $this->callCLI( $this->shortnames[$shortname], $parser, $output );
		}
		else{
			return false;
		}
	}

	public function getVersions( CLIOutput $o ){
		$e = array();
		foreach( $this->list as $l){
			$e[] = $l['name'];
			$e[] = array( 'Version '. $l['version'] );
		}
		$o->print(array(
			'Loaded Extensions and Versions',
			$e
		));
	}
}
?>