<?php
class ExtensionEventHandler {

	private static array $files = array();
	private static array $loaded = array();
	private static array $eventExtList = array(
		'newRecordSaved'  => array(),
		'newDialogOpened'  => array(),
		'daemonAfterSleep'  => array(),
		'daemonBeforeSleep'  => array(),
		'dayFileSync'  => array(),
		'statsViewed'  => array(),
		'settingsCatsChanged'  => array()
	);

	// Load
	private static function ensureExtensionLoaded( string $name ){
		if( !isset( self::$loaded[$name] ) && is_readable( self::$files[$name] ) ){
			$ok = @include_once( self::$files[$name] );
			if( $ok !== false ){
				self::$loaded[$name] = true;
			}
		}
	}

	// External calls
	public static function loadExtension( string $name, array $events , string $filepath ) : void {
		self::$files[$name] = $filepath;
		foreach(self::$eventExtList as $event => &$names ){
			if( !empty( $events[$event] ) && !in_array($name, $names)  ){
				$names[$name] = $events[$event];
			}
		}
	}

	public static function cli( string $name, CLIParser $parser, CLIOutput $output, string $callback ) : void {
		self::ensureExtensionLoaded($name);
		if($parser->getTask() !== CLIParser::TASK_EXTENSION){
			// shortcut used, we will reconstruct full parser (tasks/ commands)!!
			$oldArgs = $parser->getPlainArgs();
			$newArgs = array_merge(array(
				$oldArgs[0],
				CLIParser::TASK_EXTENSION,
				$name
			), array_slice($oldArgs, 2));
			$p = new CLIParser(count($newArgs), $newArgs );
		}
		else{
			$p = $parser;
		}
		$callback($p, $output);
	}

	private static function event( string $event, ...$args ) : void {
		if( isset(self::$eventExtList[$event])){
			foreach( self::$eventExtList[$event] as $name => $callback ){
				self::ensureExtensionLoaded($name);
				$callback(...$args);
			}
		}
	}

	// Event calls
	public static function newRecordSaved( int $begin, int $end, string $name, string $category ) : void {
		self::event("newRecordSaved", $begin, $end, $name, $category);
	}

	public static function newDialogOpened( Dialog $dialog ) : void {
		self::event("newDialogOpened", $dialog);
	}

	public static function daemonAfterSleep() : void {
		self::event("daemonAfterSleep");
	}

	public static function daemonBeforeSleep() : void {
		self::event("daemonBeforeSleep");
	}

	public static function dayFileSync( array $array ) : void {
		self::event("dayFileSync", $array);
	}

	public static function statsViewed(array $data, CLIOutput $output ) : void {
		self::event("statsViewed", $data, $output);
	}

	public static function settingsCatsChanged( string $affectedCategory, bool $added ) : void {
		self::event("settingsCatsChanged", $affectedCategory, $added);
	}
}
?>