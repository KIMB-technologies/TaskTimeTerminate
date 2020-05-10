<?php
namespace TTTMiLog;

class MiLog {

	private static bool $isMiLogOutput = false;
	private static array $data;

	public static function cli( \CLIParser $parser, \CLIOutput $output ) : void {
		self::$isMiLogOutput = true;
		\ob_start();

		$o = new \CLIOutput();
		$s = new \Stats($parser, $o);
		$o->__destruct();
		unset($o);

		\ob_end_clean();
		self::$isMiLogOutput = false;

		$output->print(
			array('MiLog',
				array(
					'Please give the same commands to filter data as used in the statistics view.',
			)
		));

		if( !empty(self::$data)){
			$tasks = array();
			$i = 0;
			$lastval = null;
			$lastend = 0;
			foreach( self::$data as $d ){
				if( $lastval === $d['category'] . '++' . $d['name']
					&& $lastend + \Config::getSleepTime() > $d['begin'] ){
					$tasks[$i-1]['duration'] += $d['duration'];
				}
				else{
					$tasks[$i++] = array(
						'day' => date('d', $d['begin']),
						'duration' => $d['duration'],
						'start' => '[' . date('H', $d['begin']) . ', ' . date('i', $d['begin']) . ']'
					);
				}
				$lastend = $d['end'];
				$lastval = $d['category'] . '++' . $d['name'];
			}

			$arrays = array();
			foreach($tasks as $task ){
				$arrays[] = 'array( '. $task['day'] .', '. $task['start'] .', '.  ((int) ( $task['duration'] / 60) / 60 ) .' )';
			}
			echo PHP_EOL . \implode( ",\r\n", \array_reverse( $arrays ) ) . PHP_EOL . PHP_EOL;
		}
	}

	public static function statsViewed(array $data, \CLIOutput $output) : void {
		if( self::$isMiLogOutput ){
			self::$data = $data;
		}
	}
}
?>